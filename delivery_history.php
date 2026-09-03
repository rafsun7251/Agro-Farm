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
   GET DELIVERY MAN PROFILE
========================================================= */

$query = "
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

$stmt = mysqli_prepare($conn, $query);

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
   DELIVERY MAN NOT FOUND
========================================================= */

if ($deliveryManId === null) {

    die("
        <div style='
            font-family:Arial;
            padding:50px;
            text-align:center;
        '>

            <h2>Delivery Man Profile Not Found</h2>

            <p>
                Your account is not connected to a delivery
                man profile.
            </p>

        </div>
    ");
}


/* =========================================================
   FETCH COMPLETED DELIVERIES
========================================================= */

$deliveries = [];

$query = "
    SELECT

        d.delivery_id,
        d.order_id,
        d.delivery_status,
        d.assigned_at,
        d.delivered_at,

        o.order_date,
        o.total_amount,
        o.delivery_address,

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

    AND d.delivery_status = 'delivered'

    ORDER BY
        d.delivered_at DESC,
        d.delivery_id DESC
";

$stmt = mysqli_prepare($conn, $query);

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


/* =========================================================
   STATISTICS
========================================================= */

$totalDelivered = count($deliveries);

$totalEarnings = 0;

foreach ($deliveries as $delivery) {

    $totalEarnings +=
        (float) (
            $delivery['total_amount'] ?? 0
        );
}


/* =========================================================
   PROFILE INITIAL
========================================================= */

$nameParts = preg_split(
    '/\s+/',
    trim($deliveryManName)
);

$profileInitial =
    strtoupper(
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
        Nobanno | Delivery History
    </title>

    <link
        rel="stylesheet"
        href="../../css/DeliveryManDashboard/delivery_history.css"
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
            class="nav-link"
        >
            Pending Deliveries
        </a>


        <a
            href="delivery_history.php"
            class="nav-link active"
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
     MAIN
====================================================== -->

<main class="main-container">


    <!-- PAGE HEADER -->

    <section class="page-header">

        <div>

            <p class="eyebrow">
                DELIVERY RECORDS
            </p>

            <h1>
                Delivery History
            </h1>

            <p class="page-description">

                View all orders you have successfully
                delivered.

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
         STATISTICS
    ================================================== -->

    <section class="stats-grid">


        <!-- TOTAL DELIVERIES -->

        <div class="stat-card">

            <div class="stat-icon">
                ✓
            </div>


            <div>

                <span>
                    Total Delivered
                </span>

                <strong>
                    <?php
                    echo number_format(
                        $totalDelivered
                    );
                    ?>
                </strong>

            </div>

        </div>



        <!-- TOTAL ORDER VALUE -->

        <div class="stat-card">

            <div class="stat-icon money">
                ৳
            </div>


            <div>

                <span>
                    Total Order Value
                </span>

                <strong>

                    ৳<?php

                    echo number_format(
                        $totalEarnings,
                        2
                    );

                    ?>

                </strong>

            </div>

        </div>


    </section>



    <!-- =================================================
         HISTORY
    ================================================== -->

    <?php if (empty($deliveries)): ?>


        <!-- EMPTY -->

        <section class="empty-state">

            <div class="empty-icon">
                ✓
            </div>


            <h2>
                No Delivery History
            </h2>


            <p>

                You have not completed any deliveries yet.

            </p>


            <a
                href="pending_deliveries.php"
                class="primary-button"
            >
                View Pending Deliveries
            </a>

        </section>


    <?php else: ?>


        <section class="history-card">


            <!-- CARD HEADER -->

            <div class="history-header">

                <div>

                    <h2>
                        Completed Deliveries
                    </h2>

                    <p>
                        Your recently completed orders
                    </p>

                </div>


                <span class="count-badge">

                    <?php
                    echo $totalDelivered;
                    ?>

                    Deliveries

                </span>

            </div>



            <!-- TABLE -->

            <div class="table-container">

                <table>


                    <thead>

                        <tr>

                            <th>
                                ORDER
                            </th>

                            <th>
                                CUSTOMER
                            </th>

                            <th>
                                DELIVERY ADDRESS
                            </th>

                            <th>
                                AMOUNT
                            </th>

                            <th>
                                DELIVERED ON
                            </th>

                            <th>
                                STATUS
                            </th>

                        </tr>

                    </thead>


                    <tbody>


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

                            if (
                                $customerName === ''
                            ) {
                                $customerName =
                                    "Customer";
                            }


                            $customerInitial =
                                strtoupper(
                                    substr(
                                        $customerName,
                                        0,
                                        1
                                    )
                                );

                            ?>


                            <tr>


                                <!-- ORDER -->

                                <td>

                                    <strong class="order-id">

                                        #<?php
                                        echo htmlspecialchars(
                                            $delivery[
                                                'order_id'
                                            ]
                                        );
                                        ?>

                                    </strong>

                                </td>



                                <!-- CUSTOMER -->

                                <td>

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
                                                    ] ?? ''
                                                );
                                                ?>

                                            </span>

                                        </div>

                                    </div>

                                </td>



                                <!-- ADDRESS -->

                                <td>

                                    <div class="address">

                                        <span>
                                            📍
                                        </span>

                                        <p>

                                            <?php
                                            echo htmlspecialchars(
                                                $delivery[
                                                    'delivery_address'
                                                ] ?? 'N/A'
                                            );
                                            ?>

                                        </p>

                                    </div>

                                </td>



                                <!-- AMOUNT -->

                                <td>

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

                                </td>



                                <!-- DATE -->

                                <td>

                                    <div class="date-info">

                                        <?php

                                        if (
                                            !empty(
                                                $delivery[
                                                    'delivered_at'
                                                ]
                                            )
                                        ) {

                                            echo date(
                                                'd M Y',
                                                strtotime(
                                                    $delivery[
                                                        'delivered_at'
                                                    ]
                                                )
                                            );

                                            echo '<span>';

                                            echo date(
                                                'h:i A',
                                                strtotime(
                                                    $delivery[
                                                        'delivered_at'
                                                    ]
                                                )
                                            );

                                            echo '</span>';

                                        } else {

                                            echo 'N/A';

                                        }

                                        ?>

                                    </div>

                                </td>



                                <!-- STATUS -->

                                <td>

                                    <span class="status">

                                        <span class="status-dot">
                                        </span>

                                        Delivered

                                    </span>

                                </td>


                            </tr>


                        <?php endforeach; ?>


                    </tbody>

                </table>

            </div>

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