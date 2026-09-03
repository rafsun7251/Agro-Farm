<?php

session_start();

require_once __DIR__ . '/../admin_auth.php';
require_once __DIR__ . '/../db.php';


// ======================================================
// DASHBOARD STATISTICS
// ======================================================

$totalUsers = 0;
$totalFarmers = 0;
$totalDeliveryMen = 0;
$totalCustomers = 0;
$totalCrops = 0;
$totalOrders = 0;
$totalRevenue = 0;


// Total Users
$query = "SELECT COUNT(*) AS total FROM users";
$result = mysqli_query($conn, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalUsers = (int)$row['total'];
}


// Farmers
$query = "SELECT COUNT(*) AS total FROM users WHERE role = 'farmer'";
$result = mysqli_query($conn, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalFarmers = (int)$row['total'];
}


// Delivery Men
$query = "SELECT COUNT(*) AS total FROM users WHERE role = 'delivery_man'";
$result = mysqli_query($conn, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalDeliveryMen = (int)$row['total'];
}


// Customers
$query = "SELECT COUNT(*) AS total FROM users WHERE role = 'customer'";
$result = mysqli_query($conn, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalCustomers = (int)$row['total'];
}


// Crops
$query = "SELECT COUNT(*) AS total FROM crops";
$result = mysqli_query($conn, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalCrops = (int)$row['total'];
}


// Orders
$query = "SELECT COUNT(*) AS total FROM orders";
$result = mysqli_query($conn, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalOrders = (int)$row['total'];
}


// Revenue
// This will work if payments table contains an amount column.
$query = "SELECT COALESCE(SUM(amount), 0) AS total FROM payments";
$result = mysqli_query($conn, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalRevenue = (float)$row['total'];
}


// ======================================================
// ADMIN INFORMATION
// ======================================================

$adminName = "Admin";

if (isset($_SESSION['first_name'])) {
    $adminName = $_SESSION['first_name'];
}

if (
    isset($_SESSION['first_name']) &&
    isset($_SESSION['last_name'])
) {
    $adminName =
        $_SESSION['first_name'] . " " .
        $_SESSION['last_name'];
}

$adminInitial = strtoupper(substr($adminName, 0, 1));

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Nobanno | Admin Dashboard</title>

    <link
        rel="stylesheet"
        href="../css/AdminDashboard.css"
    >

</head>


<body>


<div class="dashboard">


    <!-- ==================================================
         SIDEBAR
    =================================================== -->

    <aside class="sidebar">

        <div class="sidebar-brand">

            <div class="brand-logo">
                🌾
            </div>

            <div class="brand-info">

                <h2>নবান্ন</h2>

                <p>Admin Panel</p>

            </div>

        </div>


        <nav class="sidebar-menu">


            <!-- Dashboard -->

            <a
                href="AdminDashboard.php"
                class="menu-item active"
            >

                <span class="menu-icon">▣</span>

                <span>Dashboard</span>

            </a>


            <!-- User Management -->

            <a
                href="UserManagement.php"
                class="menu-item"
            >

                <span class="menu-icon">♟</span>

                <span>User Management</span>

            </a>


            <!-- Crop Management -->

            <a
                href="CropManagement.php"
                class="menu-item"
            >

                <span class="menu-icon">🌱</span>

                <span>Crop Management</span>

            </a>


            <!-- Orders -->

            <a
                href="OrderManagement.php"
                class="menu-item"
            >

                <span class="menu-icon">🛒</span>

                <span>Order Management</span>

            </a>


            <!-- Deliveries -->

            <a
                href="DeliveryManagement.php"
                class="menu-item"
            >

                <span class="menu-icon">🚚</span>

                <span>Delivery Management</span>

            </a>


            <!-- Payments -->

            <a
                href="PaymentManagement.php"
                class="menu-item"
            >

                <span class="menu-icon">▣</span>

                <span>Payment Management</span>

            </a>


            <!-- Settings -->

            <a
                href="AdminSettings.php"
                class="menu-item"
            >

                <span class="menu-icon">⚙</span>

                <span>Settings</span>

            </a>


        </nav>


        <!-- Logout -->

        <div class="sidebar-bottom">

            <a
                href="../logout.php"
                class="logout-link"
            >

                <span class="menu-icon">↪</span>

                <span>Logout</span>

            </a>

        </div>

    </aside>



    <!-- ==================================================
         MAIN CONTENT
    =================================================== -->

    <main class="main-content">


        <!-- TOP BAR -->

        <header class="topbar">

            <div class="page-heading">

                <h1>Admin Dashboard</h1>

                <p>
                    Manage and monitor your Nobanno platform
                </p>

            </div>


            <div class="admin-profile">

                <div class="profile-avatar">

                    <?php echo htmlspecialchars($adminInitial); ?>

                </div>

                <div class="profile-info">

                    <strong>
                        <?php echo htmlspecialchars($adminName); ?>
                    </strong>

                    <span>Administrator</span>

                </div>

            </div>

        </header>



        <!-- ==================================================
             WELCOME CARD
        =================================================== -->

        <section class="welcome-card">

            <div>

                <h2>
                    Welcome back,
                    <?php echo htmlspecialchars($adminName); ?>!
                </h2>

                <p>
                    Here's what's happening with your
                    Nobanno platform today.
                </p>

            </div>

        </section>



        <!-- ==================================================
             STATISTICS
        =================================================== -->

        <section class="stats-grid">


            <!-- Total Users -->

            <div class="stat-card">

                <div class="stat-icon users-icon">
                    👥
                </div>

                <div class="stat-content">

                    <span>Total Users</span>

                    <strong>
                        <?php echo number_format($totalUsers); ?>
                    </strong>

                    <small>
                        Registered users
                    </small>

                </div>

            </div>


            <!-- Farmers -->

            <div class="stat-card">

                <div class="stat-icon farmers-icon">
                    🌾
                </div>

                <div class="stat-content">

                    <span>Farmers</span>

                    <strong>
                        <?php echo number_format($totalFarmers); ?>
                    </strong>

                    <small>
                        Registered farmers
                    </small>

                </div>

            </div>


            <!-- Delivery Men -->

            <div class="stat-card">

                <div class="stat-icon delivery-icon">
                    🚚
                </div>

                <div class="stat-content">

                    <span>Delivery Men</span>

                    <strong>
                        <?php echo number_format($totalDeliveryMen); ?>
                    </strong>

                    <small>
                        Registered delivery men
                    </small>

                </div>

            </div>


            <!-- Customers -->

            <div class="stat-card">

                <div class="stat-icon customer-icon">
                    🛒
                </div>

                <div class="stat-content">

                    <span>Customers</span>

                    <strong>
                        <?php echo number_format($totalCustomers); ?>
                    </strong>

                    <small>
                        Registered customers
                    </small>

                </div>

            </div>


            <!-- Crops -->

            <div class="stat-card">

                <div class="stat-icon crops-icon">
                    🌱
                </div>

                <div class="stat-content">

                    <span>Total Crops</span>

                    <strong>
                        <?php echo number_format($totalCrops); ?>
                    </strong>

                    <small>
                        Available crops
                    </small>

                </div>

            </div>


            <!-- Orders -->

            <div class="stat-card">

                <div class="stat-icon orders-icon">
                    📦
                </div>

                <div class="stat-content">

                    <span>Total Orders</span>

                    <strong>
                        <?php echo number_format($totalOrders); ?>
                    </strong>

                    <small>
                        Total orders
                    </small>

                </div>

            </div>


            <!-- Revenue -->

            <div class="stat-card">

                <div class="stat-icon revenue-icon">
                    ৳
                </div>

                <div class="stat-content">

                    <span>Total Revenue</span>

                    <strong>
                        ৳<?php
                        echo number_format(
                            $totalRevenue,
                            2
                        );
                        ?>
                    </strong>

                    <small>
                        Total payment amount
                    </small>

                </div>

            </div>


        </section>



        <!-- ==================================================
             LOWER SECTION
        =================================================== -->

        <section class="dashboard-grid">


            <!-- QUICK ACTIONS -->

            <div class="dashboard-card">

                <div class="card-header">

                    <div>

                        <h3>Quick Actions</h3>

                        <p>
                            Frequently used admin functions
                        </p>

                    </div>

                </div>


                <div class="quick-actions">


                    <a
                        href="UserManagement.php"
                        class="action-button"
                    >

                        <span class="action-icon">
                            👥
                        </span>

                        <span>
                            Manage Users
                        </span>

                    </a>


                    <a
                        href="CropManagement.php"
                        class="action-button"
                    >

                        <span class="action-icon">
                            🌱
                        </span>

                        <span>
                            Manage Crops
                        </span>

                    </a>


                    <a
                        href="OrderManagement.php"
                        class="action-button"
                    >

                        <span class="action-icon">
                            📦
                        </span>

                        <span>
                            Manage Orders
                        </span>

                    </a>


                    <a
                        href="DeliveryManagement.php"
                        class="action-button"
                    >

                        <span class="action-icon">
                            🚚
                        </span>

                        <span>
                            Manage Deliveries
                        </span>

                    </a>


                </div>

            </div>



            <!-- SYSTEM OVERVIEW -->

            <div class="dashboard-card">

                <div class="card-header">

                    <div>

                        <h3>System Overview</h3>

                        <p>
                            Current platform information
                        </p>

                    </div>

                </div>


                <div class="overview-list">


                    <div class="overview-row">

                        <span>Users</span>

                        <strong>
                            <?php echo number_format($totalUsers); ?>
                        </strong>

                    </div>


                    <div class="overview-row">

                        <span>Farmers</span>

                        <strong>
                            <?php echo number_format($totalFarmers); ?>
                        </strong>

                    </div>


                    <div class="overview-row">

                        <span>Customers</span>

                        <strong>
                            <?php echo number_format($totalCustomers); ?>
                        </strong>

                    </div>


                    <div class="overview-row">

                        <span>Crops</span>

                        <strong>
                            <?php echo number_format($totalCrops); ?>
                        </strong>

                    </div>


                    <div class="overview-row">

                        <span>Orders</span>

                        <strong>
                            <?php echo number_format($totalOrders); ?>
                        </strong>

                    </div>


                </div>

            </div>


        </section>


        <!-- ==================================================
             FOOTER
        =================================================== -->

        <footer class="dashboard-footer">

            <p>
                © <?php echo date("Y"); ?>
                Nobanno Agro-Farm. Admin Panel.
            </p>

        </footer>

<script src="../controller/admin/AdminDashboard.js"></script>
    </main>

</div>


</body>

</html>