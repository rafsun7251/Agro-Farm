<?php

/* =========================================================
   DELIVERY MAN DASHBOARD
   NOBANNO AGRO FARM
========================================================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* =========================================================
   AUTHENTICATION
========================================================= */

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$currentRole = strtolower(trim($_SESSION['role'] ?? ''));

if (
    $currentRole !== 'delivery_man' &&
    $currentRole !== 'delivery'
) {
    session_unset();
    session_destroy();

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
$deliveryManPhone = "";
$vehicleType = "";
$vehicleNumber = "";
$deliveryManStatus = "available";


/* =========================================================
   FETCH DELIVERY MAN INFORMATION
========================================================= */

$deliveryManQuery = "
    SELECT
        dm.delivery_man_id,
        dm.vehicle_type,
        dm.vehicle_number,
        dm.status,
        u.first_name,
        u.last_name,
        u.phone
    FROM delivery_men dm

    INNER JOIN users u
        ON dm.user_id = u.user_id

    WHERE dm.user_id = ?

    LIMIT 1
";

$stmt = mysqli_prepare($conn, $deliveryManQuery);

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

        $deliveryManPhone =
            $deliveryMan['phone'] ?? '';

        $vehicleType =
            $deliveryMan['vehicle_type'] ?? '';

        $vehicleNumber =
            $deliveryMan['vehicle_number'] ?? '';

        $deliveryManStatus =
            strtolower(
                trim(
                    $deliveryMan['status'] ?? 'available'
                )
            );
    }

    mysqli_stmt_close($stmt);
}


/* =========================================================
   STATISTICS
========================================================= */

$pendingDeliveries = 0;
$completedDeliveries = 0;
$totalDeliveries = 0;


/* =========================================================
   DELIVERY STATISTICS
========================================================= */

if ($deliveryManId !== null) {


    /* -----------------------------------------------------
       PENDING
    ----------------------------------------------------- */

    $query = "
        SELECT COUNT(*) AS total

        FROM deliveries

        WHERE delivery_man_id = ?

        AND delivery_status != 'delivered'
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

            $row =
                mysqli_fetch_assoc($result);

            $pendingDeliveries =
                (int) ($row['total'] ?? 0);
        }

        mysqli_stmt_close($stmt);
    }


    /* -----------------------------------------------------
       COMPLETED
    ----------------------------------------------------- */

    $query = "
        SELECT COUNT(*) AS total

        FROM deliveries

        WHERE delivery_man_id = ?

        AND delivery_status = 'delivered'
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

            $row =
                mysqli_fetch_assoc($result);

            $completedDeliveries =
                (int) ($row['total'] ?? 0);
        }

        mysqli_stmt_close($stmt);
    }


    /* -----------------------------------------------------
       TOTAL
    ----------------------------------------------------- */

    $query = "
        SELECT COUNT(*) AS total

        FROM deliveries

        WHERE delivery_man_id = ?
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

            $row =
                mysqli_fetch_assoc($result);

            $totalDeliveries =
                (int) ($row['total'] ?? 0);
        }

        mysqli_stmt_close($stmt);
    }
}


/* =========================================================
   EARNINGS
========================================================= */

/*
   Current delivery fee:
   ৳100 per completed delivery

   We can later connect this to your
   database/payment system if needed.
*/

$deliveryFee = 100;

$totalEarnings =
    $completedDeliveries * $deliveryFee;


/* =========================================================
   RECENT DELIVERIES
========================================================= */

$deliveries = [];

if ($deliveryManId !== null) {

    $deliveryQuery = "

        SELECT

            d.delivery_id,
            d.order_id,
            d.delivery_status,
            d.assigned_at,
            d.delivered_at,

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

        ORDER BY d.delivery_id DESC

        LIMIT 8
    ";

    $stmt =
        mysqli_prepare(
            $conn,
            $deliveryQuery
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
   STATUS FUNCTIONS
========================================================= */

function statusLabel($status)
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


function statusClass($status)
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

$nameParts =
    preg_split(
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


/* =========================================================
   STATUS DISPLAY
========================================================= */

$statusText =
    ucfirst(
        str_replace(
            '_',
            ' ',
            $deliveryManStatus
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
        Nobanno | Delivery Man Dashboard
    </title>

    <link
        rel="stylesheet"
        href="../../css/DeliveryManDashboard/delivery_dashboard.css"
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
            class="nav-link active"
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
     MAIN
====================================================== -->

<main class="main-container">


    <!-- =================================================
         WELCOME
    ================================================== -->

    <section class="welcome-section">


        <div>

            <p class="eyebrow">
                DELIVERY DASHBOARD
            </p>


            <h1>

                Welcome,
                <?php
                echo htmlspecialchars(
                    $deliveryManName
                );
                ?>

            </h1>


            <p class="welcome-text">

                Manage your assigned deliveries
                and track your delivery progress.

            </p>

        </div>


        <!-- STATUS -->

        <div class="availability-box">


            <span
                class="availability-dot
                <?php
                echo
                    $deliveryManStatus === 'available'
                    ? 'available'
                    : '';
                ?>"
            ></span>


            <div>

                <strong>

                    <?php
                    echo htmlspecialchars(
                        $statusText
                    );
                    ?>

                </strong>


                <span>
                    Delivery Status
                </span>

            </div>

        </div>

    </section>



    <!-- =================================================
         STATISTICS
    ================================================== -->

    <section class="statistics-grid">


        <!-- PENDING -->

        <div class="stat-card">

            <div class="stat-icon pending-icon">
                📦
            </div>


            <div class="stat-content">

                <span>
                    Pending Deliveries
                </span>


                <strong>
                    <?php
                    echo $pendingDeliveries;
                    ?>
                </strong>


                <small>
                    Awaiting delivery
                </small>

            </div>

        </div>



        <!-- COMPLETED -->

        <div class="stat-card">

            <div class="stat-icon completed-icon">
                ✓
            </div>


            <div class="stat-content">

                <span>
                    Completed Deliveries
                </span>


                <strong>
                    <?php
                    echo $completedDeliveries;
                    ?>
                </strong>


                <small>
                    Successfully delivered
                </small>

            </div>

        </div>



        <!-- TOTAL -->

        <div class="stat-card">

            <div class="stat-icon total-icon">
                🚚
            </div>


            <div class="stat-content">

                <span>
                    Total Deliveries
                </span>


                <strong>
                    <?php
                    echo $totalDeliveries;
                    ?>
                </strong>


                <small>
                    All assigned deliveries
                </small>

            </div>

        </div>



        <!-- EARNINGS -->

        <div class="stat-card">

            <div class="stat-icon earning-icon">
                ৳
            </div>


            <div class="stat-content">

                <span>
                    Total Earnings
                </span>


                <strong>

                    ৳<?php

                    echo number_format(
                        $totalEarnings,
                        0
                    );

                    ?>

                </strong>


                <small>
                    Based on completed deliveries
                </small>

            </div>

        </div>

    </section>



    <!-- =================================================
         QUICK ACTIONS
    ================================================== -->

    <section class="quick-actions-section">


        <div class="section-heading">

            <div>

                <p class="section-eyebrow">
                    QUICK ACTIONS
                </p>


                <h2>
                    Manage Deliveries
                </h2>

            </div>

        </div>


        <div class="quick-actions">


            <!-- PENDING -->

            <a
                href="pending_deliveries.php"
                class="quick-action-card"
            >

                <div class="quick-action-icon">
                    📦
                </div>


                <div>

                    <strong>
                        Pending Deliveries
                    </strong>

                    <span>
                        View deliveries waiting to be completed
                    </span>

                </div>


                <span class="action-arrow">
                    →
                </span>

            </a>



            <!-- HISTORY -->

            <a
                href="delivery_history.php"
                class="quick-action-card"
            >

                <div class="quick-action-icon history-icon">
                    🕘
                </div>


                <div>

                    <strong>
                        Delivery History
                    </strong>

                    <span>
                        View your completed delivery records
                    </span>

                </div>


                <span class="action-arrow">
                    →
                </span>

            </a>

        </div>

    </section>



    <!-- =================================================
         RECENT DELIVERIES
    ================================================== -->

    <section class="recent-section">


        <div class="section-heading">


            <div>

                <p class="section-eyebrow">
                    DELIVERY ACTIVITY
                </p>


                <h2>
                    Recent Deliveries
                </h2>

            </div>


            <a
                href="pending_deliveries.php"
                class="view-all"
            >
                View All →
            </a>

        </div>



        <?php if (empty($deliveries)): ?>


            <!-- EMPTY -->

            <div class="empty-state">

                <div class="empty-icon">
                    📦
                </div>


                <h3>
                    No deliveries assigned
                </h3>


                <p>
                    You currently have no delivery records.
                    New assignments will appear here.
                </p>

            </div>


        <?php else: ?>


            <!-- TABLE -->

            <div class="table-card">

                <div class="table-wrapper">

                    <table>


                        <thead>

                            <tr>

                                <th>
                                    Order
                                </th>

                                <th>
                                    Customer
                                </th>

                                <th>
                                    Address
                                </th>

                                <th>
                                    Amount
                                </th>

                                <th>
                                    Assigned
                                </th>

                                <th>
                                    Status
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php foreach (
                            $deliveries
                            as $delivery
                        ): ?>


                            <?php

                            $customerName =
                                trim(
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
                                    'Customer';
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

                                    <span
                                        class="order-number"
                                    >

                                        #
                                        <?php
                                        echo htmlspecialchars(
                                            $delivery[
                                                'order_id'
                                            ]
                                        );
                                        ?>

                                    </span>

                                </td>



                                <!-- CUSTOMER -->

                                <td>

                                    <div
                                        class="customer-cell"
                                    >


                                        <div
                                            class="customer-avatar"
                                        >

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


                                            <small>

                                                <?php
                                                echo htmlspecialchars(
                                                    $delivery[
                                                        'customer_phone'
                                                    ] ?? ''
                                                );
                                                ?>

                                            </small>

                                        </div>

                                    </div>

                                </td>



                                <!-- ADDRESS -->

                                <td>

                                    <div
                                        class="address-cell"
                                    >

                                        <span
                                            class="address-icon"
                                        >
                                            📍
                                        </span>


                                        <span>

                                            <?php
                                            echo htmlspecialchars(
                                                $delivery[
                                                    'delivery_address'
                                                ] ?? 'N/A'
                                            );
                                            ?>

                                        </span>

                                    </div>

                                </td>



                                <!-- AMOUNT -->

                                <td>

                                    <strong
                                        class="amount"
                                    >

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



                                <!-- ASSIGNED -->

                                <td>

                                    <?php

                                    if (
                                        !empty(
                                            $delivery[
                                                'assigned_at'
                                            ]
                                        )
                                    ) {

                                        echo date(
                                            'd M Y',
                                            strtotime(
                                                $delivery[
                                                    'assigned_at'
                                                ]
                                            )
                                        );

                                    } else {

                                        echo 'N/A';

                                    }

                                    ?>

                                </td>



                                <!-- STATUS -->

                                <td>

                                    <span
                                        class="status
                                        <?php
                                        echo statusClass(
                                            $delivery[
                                                'delivery_status'
                                            ]
                                        );
                                        ?>"
                                    >

                                        <span
                                            class="status-dot"
                                        ></span>


                                        <?php

                                        echo htmlspecialchars(
                                            statusLabel(
                                                $delivery[
                                                    'delivery_status'
                                                ]
                                            )
                                        );

                                        ?>

                                    </span>

                                </td>

                            </tr>


                        <?php endforeach; ?>


                        </tbody>

                    </table>

                </div>

            </div>


        <?php endif; ?>


    </section>

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