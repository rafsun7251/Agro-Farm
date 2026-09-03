<?php

session_start();

require_once __DIR__ . '/../../db.php';


/* =========================================================
   LOGIN CHECK
========================================================= */

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}


$userId = (int) $_SESSION['user_id'];


/* =========================================================
   ROLE CHECK
========================================================= */

$userRole = strtolower(trim($_SESSION['role'] ?? ''));

if ($userRole !== 'customer') {
    header("Location: ../login.php");
    exit();
}


/* =========================================================
   GET CUSTOMER ID
========================================================= */

$customerId = 0;

$customerSql = "
    SELECT customer_id
    FROM customers
    WHERE user_id = ?
    LIMIT 1
";

$customerStmt = mysqli_prepare($conn, $customerSql);

if ($customerStmt) {

    mysqli_stmt_bind_param(
        $customerStmt,
        "i",
        $userId
    );

    mysqli_stmt_execute($customerStmt);

    $customerResult = mysqli_stmt_get_result($customerStmt);

    if ($customerResult && mysqli_num_rows($customerResult) > 0) {

        $customerData = mysqli_fetch_assoc($customerResult);

        $customerId = (int) $customerData['customer_id'];
    }

    mysqli_stmt_close($customerStmt);
}


/* =========================================================
   VARIABLES
========================================================= */

$groupedOrders = [];

$totalOrders = 0;
$activeOrders = 0;
$completedOrders = 0;
$totalSpent = 0;


/* =========================================================
   GET ORDERS
========================================================= */

if ($customerId > 0) {

    $orderSql = "
        SELECT
            o.order_id,
            o.order_date,
            o.status,
            o.total_amount,

            oi.order_item_id,
            oi.crop_id,
            oi.farmer_id,
            oi.quantity,
            oi.price_per_kg,
            oi.subtotal,

            c.crop_name

        FROM orders o

        INNER JOIN order_items oi
            ON o.order_id = oi.order_id

        INNER JOIN crops c
            ON oi.crop_id = c.crop_id

        WHERE o.customer_id = ?

        ORDER BY
            o.order_date DESC,
            o.order_id DESC
    ";

    $orderStmt = mysqli_prepare($conn, $orderSql);

    if ($orderStmt) {

        mysqli_stmt_bind_param(
            $orderStmt,
            "i",
            $customerId
        );

        mysqli_stmt_execute($orderStmt);

        $orderResult = mysqli_stmt_get_result($orderStmt);

        if ($orderResult) {

            while ($row = mysqli_fetch_assoc($orderResult)) {

                $orderId = (int) $row['order_id'];

                if (!isset($groupedOrders[$orderId])) {

                    $groupedOrders[$orderId] = [
                        'order_id' => $row['order_id'],
                        'order_date' => $row['order_date'],
                        'status' => $row['status'],
                        'total_amount' => $row['total_amount'],
                        'items' => []
                    ];
                }

                $groupedOrders[$orderId]['items'][] = $row;
            }
        }

        mysqli_stmt_close($orderStmt);
    }
}


/* =========================================================
   STATISTICS
========================================================= */

$totalOrders = count($groupedOrders);


foreach ($groupedOrders as $order) {

    $status = strtolower(
        trim(
            $order['status'] ?? ''
        )
    );


    if ($status === 'pending') {

        $activeOrders++;

    } elseif ($status === 'processing') {

        $activeOrders++;

    } elseif ($status === 'shipped') {

        $activeOrders++;

    } elseif ($status === 'delivered') {

        $completedOrders++;

    } elseif ($status === 'completed') {

        $completedOrders++;
    }


    if (
        $status === 'delivered' ||
        $status === 'completed'
    ) {

        $totalSpent += (float) $order['total_amount'];
    }
}


/* =========================================================
   CUSTOMER NAME
========================================================= */

$customerName = $_SESSION['name']
    ?? $_SESSION['username']
    ?? 'Customer';

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Nobanno - My Orders</title>


    <!-- SEPARATE CSS FILE -->

    <link
        rel="stylesheet"
        href="../../css/CustomerDashboard/orders.css"
    >

</head>


<body>


<div class="orders-page">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <header class="orders-header">


        <div class="orders-header-left">


            <a
                href="customer_dashboard.php"
                class="brand"
            >

                <span class="brand-icon">
                    🌾
                </span>

                <span>
                    নবান্ন
                </span>

            </a>


            <div class="page-title">

                <span>
                    CUSTOMER DASHBOARD
                </span>

                <h1>
                    My Orders
                </h1>

            </div>


        </div>



        <div class="orders-header-right">


            <a
                href="customer_dashboard.php"
                class="header-link"
            >
                Dashboard
            </a>


            <a
                href="../crops.php"
                class="header-link"
            >
                Crops
            </a>


            <a
                href="cart.php"
                class="header-link"
            >
                Cart
            </a>


            <a
                href="orders.php"
                class="header-link active"
            >
                Orders
            </a>


            <a
                href="../../logout.php"
                class="logout-btn"
            >
                Logout
            </a>


        </div>


    </header>



    <!-- =====================================================
         MAIN
    ====================================================== -->

    <main class="orders-container">


        <!-- =================================================
             INTRO
        ================================================== -->

        <section class="orders-intro">


            <div>

                <p class="small-label">
                    ORDER MANAGEMENT
                </p>


                <h2>
                    My Orders
                </h2>


                <p>
                    Track your purchases and view
                    your previous orders.
                </p>

            </div>


            <a
                href="../crops.php"
                class="shop-btn"
            >
                Browse Fresh Crops
            </a>


        </section>



        <!-- =================================================
             STATISTICS
        ================================================== -->

        <section class="order-statistics">


            <div class="stat-card">

                <div class="stat-icon">
                    📦
                </div>

                <div>

                    <span>
                        Total Orders
                    </span>

                    <strong>
                        <?php echo $totalOrders; ?>
                    </strong>

                </div>

            </div>



            <div class="stat-card">

                <div class="stat-icon">
                    🚚
                </div>

                <div>

                    <span>
                        Active Orders
                    </span>

                    <strong>
                        <?php echo $activeOrders; ?>
                    </strong>

                </div>

            </div>



            <div class="stat-card">

                <div class="stat-icon">
                    ✓
                </div>

                <div>

                    <span>
                        Completed
                    </span>

                    <strong>
                        <?php echo $completedOrders; ?>
                    </strong>

                </div>

            </div>



            <div class="stat-card">

                <div class="stat-icon">
                    ৳
                </div>

                <div>

                    <span>
                        Total Spent
                    </span>

                    <strong>
                        ৳<?php echo number_format($totalSpent, 2); ?>
                    </strong>

                </div>

            </div>


        </section>



        <!-- =================================================
             ORDER HISTORY
        ================================================== -->

        <section class="orders-section">


            <div class="section-heading">


                <div>

                    <h2>
                        Order History
                    </h2>

                    <p>
                        View all your previous purchases.
                    </p>

                </div>


                <span class="order-count">

                    <?php echo $totalOrders; ?>

                    <?php
                    echo ($totalOrders == 1)
                        ? ' Order'
                        : ' Orders';
                    ?>

                </span>


            </div>



            <?php if (empty($groupedOrders)): ?>


                <!-- =================================================
                     EMPTY ORDERS
                ================================================== -->

                <div class="empty-orders">


                    <div class="empty-icon">
                        📦
                    </div>


                    <h3>
                        No Orders Yet
                    </h3>


                    <p>
                        You haven't placed any orders yet.
                        Browse our fresh crops and place
                        your first order.
                    </p>


                    <a
                        href="../crops.php"
                        class="shop-btn"
                    >
                        Start Shopping
                    </a>


                </div>


            <?php else: ?>


                <!-- =================================================
                     ORDER LIST
                ================================================== -->

                <div class="orders-list">


                    <?php foreach ($groupedOrders as $order): ?>


                        <?php

                        $status = strtolower(
                            trim(
                                $order['status'] ?? ''
                            )
                        );


                        if ($status === '') {
                            $status = 'pending';
                        }


                        $statusClass = preg_replace(
                            '/[^a-z0-9]+/',
                            '-',
                            $status
                        );


                        $orderTotal = (float) $order['total_amount'];

                        ?>


                        <article class="order-card">


                            <!-- =================================================
                                 ORDER HEADER
                            ================================================== -->

                            <div class="order-card-header">


                                <div class="order-number">

                                    <span>
                                        Order Number
                                    </span>

                                    <strong>
                                        #<?php
                                        echo $order['order_id'];
                                        ?>
                                    </strong>

                                </div>



                                <div class="order-date">

                                    <span>
                                        Order Date
                                    </span>

                                    <strong>

                                        <?php

                                        if (!empty($order['order_date'])) {

                                            echo date(
                                                'd M Y',
                                                strtotime(
                                                    $order['order_date']
                                                )
                                            );

                                        } else {

                                            echo 'N/A';

                                        }

                                        ?>

                                    </strong>

                                </div>



                                <span
                                    class="order-status status-<?php
                                    echo $statusClass;
                                    ?>"
                                >

                                    <?php
                                    echo ucfirst($status);
                                    ?>

                                </span>


                            </div>



                            <!-- =================================================
                                 ORDER ITEMS
                            ================================================== -->

                            <div class="order-items">


                                <?php foreach ($order['items'] as $item): ?>


                                    <div class="order-item">


                                        <div class="item-icon">
                                            🌱
                                        </div>



                                        <div class="item-information">


                                            <strong>

                                                <?php

                                                echo htmlspecialchars(
                                                    $item['crop_name']
                                                    ?? 'Crop'
                                                );

                                                ?>

                                            </strong>



                                            <span>

                                                Quantity:

                                                <?php

                                                echo htmlspecialchars(
                                                    $item['quantity']
                                                );

                                                ?>

                                                kg

                                                <span class="separator">
                                                    •
                                                </span>

                                                Price:

                                                ৳<?php

                                                echo number_format(
                                                    $item['price_per_kg'],
                                                    2
                                                );

                                                ?>

                                                /kg

                                            </span>


                                        </div>



                                        <div class="item-total">

                                            ৳<?php

                                            echo number_format(
                                                $item['subtotal'],
                                                2
                                            );

                                            ?>

                                        </div>


                                    </div>


                                <?php endforeach; ?>


                            </div>



                            <!-- =================================================
                                 ORDER FOOTER
                            ================================================== -->

                            <div class="order-card-footer">


                                <div class="order-summary-text">


                                    <span>
                                        Order Total
                                    </span>


                                    <strong>

                                        ৳<?php

                                        echo number_format(
                                            $orderTotal,
                                            2
                                        );

                                        ?>

                                    </strong>


                                </div>



                                <div class="order-actions">


                                    <?php if ($status === 'pending'): ?>


                                        <span class="action-info">
                                            Order received
                                        </span>


                                    <?php elseif ($status === 'processing'): ?>


                                        <span class="action-info">
                                            Being prepared
                                        </span>


                                    <?php elseif ($status === 'shipped'): ?>


                                        <span class="action-info">
                                            On the way
                                        </span>


                                    <?php elseif ($status === 'delivered'): ?>


                                        <span class="action-success">
                                            ✓ Delivered
                                        </span>


                                    <?php elseif ($status === 'completed'): ?>


                                        <span class="action-success">
                                            ✓ Completed
                                        </span>


                                    <?php elseif ($status === 'cancelled'): ?>


                                        <span class="action-cancelled">
                                            ✕ Cancelled
                                        </span>


                                    <?php else: ?>


                                        <span class="action-info">

                                            <?php
                                            echo ucfirst($status);
                                            ?>

                                        </span>


                                    <?php endif; ?>


                                </div>


                            </div>


                        </article>


                    <?php endforeach; ?>


                </div>


            <?php endif; ?>


        </section>


    </main>



    <!-- =====================================================
         FOOTER
    ====================================================== -->

    <footer class="orders-footer">


        <strong>
            🌾 নবান্ন
        </strong>


        <span>
            Fresh from farmers, delivered to you.
        </span>


    </footer>


</div>


</body>

</html>