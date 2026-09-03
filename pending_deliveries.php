<?php

/* =========================================================
   DELIVERY MAN AUTHENTICATION
========================================================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$currentRole = strtolower(trim($_SESSION['role'] ?? ''));

if (
    $currentRole !== 'delivery_man' &&
    $currentRole !== 'delivery'
) {
    header("Location: ../login.php");
    exit();
}


/* =========================================================
   DATABASE
========================================================= */

require_once __DIR__ . '/../../db.php';


/* =========================================================
   LOGGED-IN USER
========================================================= */

$userId = (int) $_SESSION['user_id'];

$deliveryManId = null;
$deliveryManName = "Delivery Man";


/* =========================================================
   GET DELIVERY MAN
========================================================= */

$deliveryManQuery = "
    SELECT
        dm.delivery_man_id,
        u.first_name,
        u.last_name

    FROM delivery_men dm

    INNER JOIN users u
        ON dm.user_id = u.user_id

    WHERE dm.user_id = ?

    LIMIT 1
";

$stmt = mysqli_prepare(
    $conn,
    $deliveryManQuery
);

if ($stmt) {

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $userId
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (
        $result &&
        mysqli_num_rows($result) > 0
    ) {

        $deliveryMan = mysqli_fetch_assoc($result);

        $deliveryManId =
            (int) $deliveryMan['delivery_man_id'];

        $deliveryManName = trim(
            ($deliveryMan['first_name'] ?? '') .
            ' ' .
            ($deliveryMan['last_name'] ?? '')
        );

        if ($deliveryManName === '') {
            $deliveryManName = "Delivery Man";
        }
    }

    mysqli_stmt_close($stmt);
}


/* =========================================================
   FETCH PENDING DELIVERIES
========================================================= */

$deliveries = [];

if ($deliveryManId !== null) {

    $query = "
        SELECT

            d.delivery_id,
            d.order_id,
            d.delivery_status,
            d.assigned_at,

            o.total_amount,
            o.delivery_address,
            o.order_date,

            customer_user.first_name
                AS customer_first_name,

            customer_user.last_name
                AS customer_last_name,

            customer_user.phone
                AS customer_phone

        FROM deliveries d

        INNER JOIN orders o
            ON d.order_id = o.order_id

        INNER JOIN customers c
            ON o.customer_id = c.customer_id

        INNER JOIN users customer_user
            ON c.user_id = customer_user.user_id

        WHERE d.delivery_man_id = ?

        AND d.delivery_status != 'delivered'

        ORDER BY
            d.assigned_at ASC,
            d.delivery_id ASC
    ";

    $stmt = mysqli_prepare(
        $conn,
        $query
    );

    if ($stmt) {

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $deliveryManId
        );

        mysqli_stmt_execute($stmt);

        $result =
            mysqli_stmt_get_result($stmt);

        if ($result) {

            while (
                $row =
                mysqli_fetch_assoc($result)
            ) {

                $deliveries[] = $row;
            }
        }

        mysqli_stmt_close($stmt);
    }
}


/* =========================================================
   HELPER FUNCTIONS
========================================================= */

function getStatusLabel($status)
{
    switch ($status) {

        case 'assigned':
            return 'Assigned';

        case 'picked_up':
            return 'Picked Up';

        case 'out_for_delivery':
            return 'Out for Delivery';

        case 'delivered':
            return 'Delivered';

        default:
            return ucfirst(
                str_replace(
                    '_',
                    ' ',
                    $status
                )
            );
    }
}


function getStatusClass($status)
{
    switch ($status) {

        case 'assigned':
            return 'status-assigned';

        case 'picked_up':
            return 'status-picked';

        case 'out_for_delivery':
            return 'status-out';

        case 'delivered':
            return 'status-delivered';

        default:
            return 'status-assigned';
    }
}


/* =========================================================
   PROFILE INITIAL
========================================================= */

$nameParts = preg_split(
    '/\s+/',
    trim($deliveryManName)
);

$profileInitial = strtoupper(
    substr(
        $nameParts[0] ?? 'D',
        0,
        1
    )
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Nobanno | Pending Deliveries
    </title>

    <link
        rel="stylesheet"
        href="../../css/DeliveryManDashboard/pending_deliveries.css"
    >

</head>


<body>


<!-- =====================================================
     HEADER
====================================================== -->

<header class="top-header">


    <!-- BRAND -->

    <div class="brand">

        <div class="brand-icon">
            🌾
        </div>

        <div>

            <h2>
                নবান্ন
            </h2>

            <span>
                Delivery Panel
            </span>

        </div>

    </div>



    <!-- NAVIGATION -->

    <nav class="top-navigation">

        <a
            href="delivery_dashboard.php"
            class="nav-link"
        >
            Dashboard
        </a>


        <a
            href="pending_deliveries.php"
            class="nav-link active"
        >
            Pending Deliveries
        </a>


        <a
            href="delivery_history.php"
            class="nav-link"
        >
            Delivery History
        </a>

    </nav>



    <!-- PROFILE -->

    <div class="profile-area">

        <div class="profile-avatar">

            <?php
            echo htmlspecialchars(
                $profileInitial
            );
            ?>

        </div>


        <div class="profile-text">

            <strong>

                <?php
                echo htmlspecialchars(
                    $deliveryManName
                );
                ?>

            </strong>


            <span>
                Delivery Man
            </span>

        </div>


        <a
            href="../../logout.php"
            class="logout-button"
        >
            Logout
        </a>

    </div>

</header>



<!-- =====================================================
     MAIN CONTENT
====================================================== -->

<main class="main-container">


    <!-- PAGE HEADER -->

    <section class="page-header">


        <div>

            <p class="eyebrow">
                DELIVERY MANAGEMENT
            </p>


            <h1>
                Pending Deliveries
            </h1>


            <p class="page-description">

                View and manage all orders currently
                assigned to you.

            </p>

        </div>


        <a
            href="delivery_dashboard.php"
            class="back-button"
        >
            ← Dashboard
        </a>

    </section>



    <!-- =================================================
         SUMMARY
    ================================================== -->

    <section class="summary-card">


        <div class="summary-icon">
            📦
        </div>


        <div>

            <span>
                Current Pending Deliveries
            </span>


            <strong>
                <?php
                echo count($deliveries);
                ?>
            </strong>

        </div>

    </section>



    <!-- =================================================
         DELIVERY LIST
    ================================================== -->

    <?php if (empty($deliveries)): ?>


        <!-- EMPTY STATE -->

        <section class="empty-state">

            <div class="empty-icon">
                ✓
            </div>


            <h2>
                No Pending Deliveries
            </h2>


            <p>

                You don't have any pending deliveries
                at the moment.

            </p>


            <a
                href="delivery_dashboard.php"
                class="primary-button"
            >
                Back to Dashboard
            </a>

        </section>


    <?php else: ?>


        <section class="delivery-list">


            <?php foreach (
                $deliveries
                as $delivery
            ): ?>


                <?php

                $customerName = trim(
                    ($delivery[
                        'customer_first_name'
                    ] ?? '') .
                    ' ' .
                    ($delivery[
                        'customer_last_name'
                    ] ?? '')
                );


                if ($customerName === '') {
                    $customerName = "Customer";
                }


                $customerInitial =
                    strtoupper(
                        substr(
                            $customerName,
                            0,
                            1
                        )
                    );


                $status =
                    $delivery[
                        'delivery_status'
                    ] ?? 'assigned';


                ?>


                <!-- DELIVERY CARD -->

                <article class="delivery-card">


                    <!-- TOP -->

                    <div class="delivery-top">


                        <div class="order-info">

                            <span class="order-label">
                                ORDER
                            </span>


                            <strong class="order-id">

                                #

                                <?php
                                echo htmlspecialchars(
                                    $delivery[
                                        'order_id'
                                    ]
                                );
                                ?>

                            </strong>

                        </div>


                        <span
                            class="status
                            <?php
                            echo getStatusClass(
                                $status
                            );
                            ?>"
                        >

                            <span
                                class="status-dot"
                            ></span>

                            <?php

                            echo htmlspecialchars(
                                getStatusLabel(
                                    $status
                                )
                            );

                            ?>

                        </span>

                    </div>



                    <!-- CONTENT -->

                    <div class="delivery-content">


                        <!-- CUSTOMER -->

                        <div class="detail-block">


                            <span class="detail-label">
                                CUSTOMER
                            </span>


                            <div class="customer-info">


                                <div class="customer-avatar">

                                    <?php
                                    echo htmlspecialchars(
                                        $customerInitial
                                    );
                                    ?>

                                </div>


                                <div>

                                    <strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $customerName
                                        );
                                        ?>

                                    </strong>


                                    <span>

                                        <?php
                                        echo htmlspecialchars(
                                            $delivery[
                                                'customer_phone'
                                            ] ?? 'No phone'
                                        );
                                        ?>

                                    </span>

                                </div>

                            </div>

                        </div>



                        <!-- ADDRESS -->

                        <div class="detail-block">


                            <span class="detail-label">
                                DELIVERY ADDRESS
                            </span>


                            <div class="address-info">

                                <span class="location-icon">
                                    📍
                                </span>


                                <span>

                                    <?php
                                    echo htmlspecialchars(
                                        $delivery[
                                            'delivery_address'
                                        ] ?? 'Address unavailable'
                                    );
                                    ?>

                                </span>

                            </div>

                        </div>



                        <!-- AMOUNT -->

                        <div class="detail-block amount-block">


                            <span class="detail-label">
                                ORDER AMOUNT
                            </span>


                            <strong class="amount">

                                ৳<?php

                                echo number_format(
                                    (float) (
                                        $delivery[
                                            'total_amount'
                                        ] ?? 0
                                    ),
                                    2
                                );

                                ?>

                            </strong>

                        </div>


                    </div>



                    <!-- FOOTER -->

                    <div class="delivery-footer">


                        <div class="assigned-info">

                            <span>
                                Assigned:
                            </span>


                            <strong>

                                <?php

                                if (
                                    !empty(
                                        $delivery[
                                            'assigned_at'
                                        ]
                                    )
                                ) {

                                    echo date(
                                        'd M Y, h:i A',
                                        strtotime(
                                            $delivery[
                                                'assigned_at'
                                            ]
                                        )
                                    );

                                } else {

                                    echo "Not available";

                                }

                                ?>

                            </strong>

                        </div>



                        <!-- ACTION -->

                        <a
                            href="update_delivery.php?id=<?php
                            echo urlencode(
                                $delivery[
                                    'delivery_id'
                                ]
                            );
                            ?>"
                            class="update-button"
                        >

                            Manage Delivery

                            <span>
                                →
                            </span>

                        </a>

                    </div>


                </article>


            <?php endforeach; ?>


        </section>


    <?php endif; ?>


</main>



<!-- =====================================================
     FOOTER
====================================================== -->

<footer class="footer">

    <p>

        © <?php
        echo date('Y');
        ?>

        নবান্ন — Fresh From Farmers

    </p>


    <p>
        Delivery Management System
    </p>

</footer>


</body>

</html>