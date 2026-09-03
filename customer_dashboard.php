<?php

session_start();

require_once __DIR__ . '/../../db.php';


/* 
   CUSTOMER AUTHENTICATION
 */

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role'])
) {
    header("Location: ../login.php");
    exit();
}


$role = strtolower(
    trim($_SESSION['role'])
);


if ($role !== 'customer') {
    header("Location: ../login.php");
    exit();
}


$userId = (int) $_SESSION['user_id'];


/* 
   CUSTOMER INFORMATION
= */

$firstName = $_SESSION['first_name'] ?? 'Customer';
$lastName  = $_SESSION['last_name'] ?? '';

$fullName = trim(
    $firstName . ' ' . $lastName
);


/* 
   MAKE SURE CUSTOMER PROFILE EXISTS
 */

$customerId = 0;


/*
 * Find existing customer profile.
 */

$customerQuery = "
    SELECT customer_id
    FROM customers
    WHERE user_id = ?
    LIMIT 1
";

$customerStmt = mysqli_prepare(
    $conn,
    $customerQuery
);


if ($customerStmt) {

    mysqli_stmt_bind_param(
        $customerStmt,
        "i",
        $userId
    );

    mysqli_stmt_execute(
        $customerStmt
    );

    $customerResult =
        mysqli_stmt_get_result(
            $customerStmt
        );


    if (
        $customerResult &&
        mysqli_num_rows($customerResult) > 0
    ) {

        $customerRow =
            mysqli_fetch_assoc(
                $customerResult
            );

        $customerId =
            (int) $customerRow['customer_id'];
    }


    mysqli_stmt_close(
        $customerStmt
    );
}


/*
 * If the user is a customer but doesn't have
 * a customers table record yet, create one.
 */

if ($customerId <= 0) {

    $createCustomerQuery = "
        INSERT INTO customers
        (
            user_id,
            address
        )
        VALUES
        (?, NULL)
    ";

    $createCustomerStmt =
        mysqli_prepare(
            $conn,
            $createCustomerQuery
        );


    if ($createCustomerStmt) {

        mysqli_stmt_bind_param(
            $createCustomerStmt,
            "i",
            $userId
        );

        mysqli_stmt_execute(
            $createCustomerStmt
        );

        $customerId =
            (int) mysqli_insert_id(
                $conn
            );

        mysqli_stmt_close(
            $createCustomerStmt
        );
    }
}


/*
 * If customer profile still cannot be created,
 * stop safely.
 */

if ($customerId <= 0) {

    die(
        "Unable to create customer profile. " .
        "Please contact the administrator."
    );
}


/* 
   STATISTICS
 */


/* 
   AVAILABLE CROPS
 */

$availableCrops = 0;

$query = "
    SELECT COUNT(*) AS total
    FROM crops
    WHERE status = 'available'
    AND quantity > 0
";

$result = mysqli_query(
    $conn,
    $query
);

if ($result) {

    $row =
        mysqli_fetch_assoc(
            $result
        );

    $availableCrops =
        (int) $row['total'];
}


/* ---------------------------------------------------------
   CART ITEMS
--------------------------------------------------------- */

$cartItems = 0;

$query = "
    SELECT COALESCE(
        SUM(quantity),
        0
    ) AS total
    FROM cart
    WHERE customer_id = ?
";

$stmt = mysqli_prepare(
    $conn,
    $query
);

if ($stmt) {

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $customerId
    );

    mysqli_stmt_execute(
        $stmt
    );

    $result =
        mysqli_stmt_get_result(
            $stmt
        );

    if ($result) {

        $row =
            mysqli_fetch_assoc(
                $result
            );

        $cartItems =
            (float) $row['total'];
    }

    mysqli_stmt_close(
        $stmt
    );
}


/* 
   TOTAL ORDERS
 */

$totalOrders = 0;

$query = "
    SELECT COUNT(*) AS total
    FROM orders
    WHERE customer_id = ?
";

$stmt = mysqli_prepare(
    $conn,
    $query
);

if ($stmt) {

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $customerId
    );

    mysqli_stmt_execute(
        $stmt
    );

    $result =
        mysqli_stmt_get_result(
            $stmt
        );

    if ($result) {

        $row =
            mysqli_fetch_assoc(
                $result
            );

        $totalOrders =
            (int) $row['total'];
    }

    mysqli_stmt_close(
        $stmt
    );
}


/* 
   ACTIVE ORDERS
 */

$activeOrders = 0;

$query = "
    SELECT COUNT(*) AS total
    FROM orders
    WHERE customer_id = ?
    AND status IN
    (
        'pending',
        'processing',
        'out_for_delivery'
    )
";

$stmt = mysqli_prepare(
    $conn,
    $query
);

if ($stmt) {

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $customerId
    );

    mysqli_stmt_execute(
        $stmt
    );

    $result =
        mysqli_stmt_get_result(
            $stmt
        );

    if ($result) {

        $row =
            mysqli_fetch_assoc(
                $result
            );

        $activeOrders =
            (int) $row['total'];
    }

    mysqli_stmt_close(
        $stmt
    );
}


/* 
   RECENT / FEATURED CROPS
 */

$crops = [];


/*
 Query
 */

$query = "
    SELECT
        crop_id,
        crop_name,
        category,
        description,
        price_per_kg,
        quantity,
        unit,
        status,
        created_at
    FROM crops
    WHERE status = 'available'
    AND quantity > 0
    ORDER BY created_at DESC
    LIMIT 8
";

$result = mysqli_query(
    $conn,
    $query
);


if ($result) {

    while (
        $row =
        mysqli_fetch_assoc($result)
    ) {

        $crops[] = $row;
    }
}


/* 
   RECENT ORDERS
 */

$recentOrders = [];


$query = "
    SELECT
        order_id,
        order_date,
        total_amount,
        delivery_charge,
        status
    FROM orders
    WHERE customer_id = ?
    ORDER BY order_date DESC
    LIMIT 5
";

$stmt = mysqli_prepare(
    $conn,
    $query
);


if ($stmt) {

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $customerId
    );

    mysqli_stmt_execute(
        $stmt
    );

    $result =
        mysqli_stmt_get_result(
            $stmt
        );


    if ($result) {

        while (
            $row =
            mysqli_fetch_assoc($result)
        ) {

            $recentOrders[] = $row;
        }
    }


    mysqli_stmt_close(
        $stmt
    );
}


/* 
   CATEGORY LABEL
 */

function categoryLabel($category)
{
    return ucfirst(
        str_replace(
            '_',
            ' ',
            $category
        )
    );
}


/* 
   STATUS LABEL
 */

function statusLabel($status)
{
    return ucfirst(
        str_replace(
            '_',
            ' ',
            $status
        )
    );
}

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
        Customer Dashboard | Nobanno
    </title>


    <link
        rel="stylesheet"
        href="../../css/CustomerDashboard/customer_dashboard.css"
    >

</head>


<body>


<!-- 
     NAVBAR
 -->

<header class="navbar">

    <a
        href="customer_dashboard.php"
        class="brand"
    >

        <span class="brand-icon">
            🌾
        </span>

        <span class="brand-name">
            নবান্ন
        </span>

    </a>


    <nav class="nav-menu">

        <a
            href="customer_dashboard.php"
            class="nav-link active"
        >
            Dashboard
        </a>


       <a href="../crops.php">Crops</a>


        <a
            href="cart.php"
            class="nav-link"
        >

            Cart

            <?php if ($cartItems > 0): ?>

                <span class="cart-badge">
                    <?php
                    echo number_format(
                        $cartItems,
                        0
                    );
                    ?>
                </span>

            <?php endif; ?>

        </a>


        <a
            href="orders.php"
            class="nav-link"
        >
            Orders
        </a>


        <a
            href="profile.php"
            class="nav-link"
        >
            Profile
        </a>

    </nav>


    <div class="nav-right">

        <div class="user-mini">

            <div class="user-avatar">
                <?php
                echo strtoupper(
                    substr(
                        $firstName,
                        0,
                        1
                    )
                );
                ?>
            </div>

            <div class="user-info">

                <span>
                    Welcome
                </span>

                <strong>
                    <?php
                    echo htmlspecialchars(
                        $firstName
                    );
                    ?>
                </strong>

            </div>

        </div>


        <a
            href="../../logout.php"
            class="logout-btn"
        >
            Logout
        </a>

    </div>

</header>



<!-- 
     HERO / WELCOME
 -->

<section class="hero-section">

    <div class="hero-content">

        <span class="hero-label">
            CUSTOMER DASHBOARD
        </span>


        <h1>
            Welcome back,
            <?php
            echo htmlspecialchars(
                $firstName
            );
            ?>!
        </h1>


        <p>
            Discover fresh agricultural products
            directly from local farmers.
        </p>


        <div class="hero-actions">

            <a
                href="crops.php"
                class="primary-btn"
            >
                Browse Fresh Crops
                <span>→</span>
            </a>


            <a
                href="orders.php"
                class="secondary-btn"
            >
                View My Orders
            </a>

        </div>

    </div>


    <div class="hero-decoration">

        <div class="hero-circle circle-one"></div>

        <div class="hero-circle circle-two"></div>

        <div class="hero-leaf">
            🌿
        </div>

    </div>

</section>



<!-- 
     STATS
 -->

<section class="stats-section">

    <div class="stats-grid">


        <!-- Available Crops -->

        <div class="stat-card">

            <div class="stat-icon green">
                🌾
            </div>

            <div class="stat-content">

                <span>
                    Available Crops
                </span>

                <strong>
                    <?php
                    echo $availableCrops;
                    ?>
                </strong>

            </div>

        </div>



        <!-- Cart -->

        <div class="stat-card">

            <div class="stat-icon orange">
                🛒
            </div>

            <div class="stat-content">

                <span>
                    Cart Items
                </span>

                <strong>
                    <?php
                    echo number_format(
                        $cartItems,
                        0
                    );
                    ?>
                </strong>

            </div>

        </div>



        <!-- Orders -->

        <div class="stat-card">

            <div class="stat-icon blue">
                📦
            </div>

            <div class="stat-content">

                <span>
                    Total Orders
                </span>

                <strong>
                    <?php
                    echo $totalOrders;
                    ?>
                </strong>

            </div>

        </div>



        <!-- Active Orders -->

        <div class="stat-card">

            <div class="stat-icon purple">
                🚚
            </div>

            <div class="stat-content">

                <span>
                    Active Orders
                </span>

                <strong>
                    <?php
                    echo $activeOrders;
                    ?>
                </strong>

            </div>

        </div>


    </div>

</section>



<!-- 
     FEATURED CROPS
 -->

<section class="products-section">


    <div class="section-header">

        <div>

            <span class="section-label">
                FRESH FROM FARMERS
            </span>

            <h2>
                Fresh Products
            </h2>

            <p>
                Browse the latest agricultural
                products available now.
            </p>

        </div>


        <a
            href="crops.php"
            class="view-all"
        >
            View All Crops →
        </a>

    </div>



    <div class="product-grid">


        <?php if (empty($crops)): ?>


            <div class="empty-products">

                <div class="empty-icon">
                    🌱
                </div>

                <h3>
                    No crops available yet
                </h3>

                <p>
                    Farmers haven't added any available
                    products yet. Please check again later.
                </p>

            </div>


        <?php else: ?>


            <?php foreach ($crops as $crop): ?>


                <article class="product-card">


                    <div class="product-top">

                        <span class="category-badge">

                            <?php
                            echo htmlspecialchars(
                                categoryLabel(
                                    $crop['category']
                                )
                            );
                            ?>

                        </span>


                        <span class="available-badge">
                            Available
                        </span>

                    </div>



                    <div class="product-image">

                        <?php

                        $category =
                            strtolower(
                                $crop['category']
                            );

                        if ($category === 'fruits') {

                            echo "🍎";

                        } elseif (
                            $category === 'vegetables'
                        ) {

                            echo "🥬";

                        } elseif (
                            $category === 'grains'
                        ) {

                            echo "🌾";

                        } else {

                            echo "🌱";
                        }

                        ?>

                    </div>



                    <div class="product-content">

                        <h3>

                            <?php
                            echo htmlspecialchars(
                                $crop['crop_name']
                            );
                            ?>

                        </h3>


                        <p class="product-description">

                            <?php

                            $description =
                                trim(
                                    $crop['description'] ?? ''
                                );

                            if (
                                $description === ''
                            ) {

                                $description =
                                    'Fresh agricultural product from a local farmer.';
                            }


                            if (
                                strlen($description) > 95
                            ) {

                                $description =
                                    substr(
                                        $description,
                                        0,
                                        95
                                    ) . '...';
                            }


                            echo htmlspecialchars(
                                $description
                            );

                            ?>

                        </p>


                        <div class="product-meta">

                            <span>

                                Stock:

                                <strong>

                                    <?php
                                    echo number_format(
                                        (float)
                                        $crop['quantity'],
                                        1
                                    );
                                    ?>

                                    <?php
                                    echo htmlspecialchars(
                                        $crop['unit']
                                            ?: 'kg'
                                    );
                                    ?>

                                </strong>

                            </span>

                        </div>


                        <div class="product-footer">

                            <div>

                                <span class="price">

                                    ৳<?php
                                   echo number_format(
    (float)($crop['price_per_kg'] ?? 0),
    2
);
                                    ?>

                                </span>

                                <span class="price-unit">
                                    / kg
                                </span>

                            </div>


                            <a
                                href="crops.php"
                                class="product-btn"
                            >
                                View
                            </a>

                        </div>

                    </div>


                </article>


            <?php endforeach; ?>


        <?php endif; ?>


    </div>

</section>



<!-- =========================================================
     RECENT ORDERS
========================================================= -->

<section class="orders-section">


    <div class="section-header">

        <div>

            <span class="section-label">
                ORDER ACTIVITY
            </span>

            <h2>
                Recent Orders
            </h2>

        </div>


        <a
            href="orders.php"
            class="view-all"
        >
            View All Orders →
        </a>

    </div>



    <?php if (empty($recentOrders)): ?>


        <div class="no-orders">

            <div class="no-orders-icon">
                📦
            </div>

            <h3>
                No orders yet
            </h3>

            <p>
                Your recent orders will appear here
                after you purchase products.
            </p>


            <a
                href="crops.php"
                class="primary-small-btn"
            >
                Start Shopping
            </a>

        </div>


    <?php else: ?>


        <div class="orders-table-wrapper">

            <table class="orders-table">

                <thead>

                    <tr>

                        <th>
                            Order
                        </th>

                        <th>
                            Date
                        </th>

                        <th>
                            Amount
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>


                    <?php foreach (
                        $recentOrders
                        as $order
                    ): ?>


                        <tr>


                            <td>

                                <strong>
                                    #<?php
                                    echo (int)
                                        $order['order_id'];
                                    ?>
                                </strong>

                            </td>


                            <td>

                                <?php
                                echo date(
                                    'd M Y',
                                    strtotime(
                                        $order['order_date']
                                    )
                                );
                                ?>

                            </td>


                            <td>

                                <strong>

                                    ৳<?php
                                    echo number_format(
                                        (float)
                                        $order['total_amount'],
                                        2
                                    );
                                    ?>

                                </strong>

                            </td>


                            <td>

                                <span
                                    class="status <?php
                                    echo htmlspecialchars(
                                        $order['status']
                                    );
                                    ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        statusLabel(
                                            $order['status']
                                        )
                                    );
                                    ?>

                                </span>

                            </td>


                            <td>

                                <a
                                    href="orders.php"
                                    class="order-view-btn"
                                >
                                    View
                                </a>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                </tbody>

            </table>

        </div>


    <?php endif; ?>


</section>



<!-- =========================================================
     QUICK ACTIONS
========================================================= -->

<section class="quick-section">


    <div class="section-header">

        <div>

            <span class="section-label">
                QUICK ACCESS
            </span>

            <h2>
                What would you like to do?
            </h2>

        </div>

    </div>


    <div class="quick-grid">


        <a
            href="crops.php"
            class="quick-card"
        >

            <div class="quick-icon">
                🌾
            </div>

            <div>

                <h3>
                    Browse Crops
                </h3>

                <p>
                    Find fresh products from farmers.
                </p>

            </div>

            <span class="quick-arrow">
                →
            </span>

        </a>



        <a
            href="cart.php"
            class="quick-card"
        >

            <div class="quick-icon">
                🛒
            </div>

            <div>

                <h3>
                    Shopping Cart
                </h3>

                <p>
                    Review products before checkout.
                </p>

            </div>

            <span class="quick-arrow">
                →
            </span>

        </a>



        <a
            href="orders.php"
            class="quick-card"
        >

            <div class="quick-icon">
                📦
            </div>

            <div>

                <h3>
                    My Orders
                </h3>

                <p>
                    Track your previous purchases.
                </p>

            </div>

            <span class="quick-arrow">
                →
            </span>

        </a>


    </div>

</section>



<!--FOOTER-->

<footer class="footer">

    <div class="footer-content">

        <div>

            <strong>
                🌾 নবান্ন
            </strong>

            <p>
                Fresh From Farmers
            </p>

        </div>


        <p>
            © <?php echo date('Y'); ?>
            Agro-Farm. All rights reserved.
        </p>

    </div>

</footer>


</body>

</html>