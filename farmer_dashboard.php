<?php

require_once "farmer_data.php";

$farmer_name = $_SESSION["name"];

$total_products = count($_SESSION["farmer_products"]);
$pending_orders = getPendingOrders();
$total_sales = getTotalSales();
$total_stock = getTotalStock();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Nobanno - Farmer Dashboard</title>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background: #f5f7f2;
    color: #333;
}

/* Navbar */

.navbar {
    height: 70px;
    background: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 60px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.logo {
    font-size: 24px;
    font-weight: bold;
    color: #315d3b;
}

.nav-links {
    display: flex;
    gap: 28px;
}

.nav-links a {
    text-decoration: none;
    color: #555;
    font-size: 15px;
    padding: 25px 0;
}

.nav-links a:hover,
.nav-links a.active {
    color: #315d3b;
    font-weight: bold;
    border-bottom: 2px solid #315d3b;
}

.logout-btn {
    text-decoration: none;
    background: #315d3b;
    color: white;
    padding: 10px 20px;
    border-radius: 6px;
}

.logout-btn:hover {
    background: #264b2f;
}

/* Dashboard */

.dashboard {
    width: 90%;
    max-width: 1200px;
    margin: 40px auto;
}

.welcome {
    margin-bottom: 30px;
}

.welcome h1 {
    color: #315d3b;
    font-size: 30px;
    margin-bottom: 8px;
}

.welcome p {
    color: #777;
}

/* Statistics */

.stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 35px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
}

.stat-card h3 {
    color: #777;
    font-size: 15px;
    margin-bottom: 12px;
}

.stat-card h2 {
    color: #315d3b;
    font-size: 27px;
}

/* Content */

.content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 25px;
}

.products,
.quick-actions {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
}

.products-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.products-header h2,
.quick-actions h2 {
    color: #315d3b;
}

.add-btn {
    text-decoration: none;
    background: #315d3b;
    color: white;
    padding: 10px 15px;
    border-radius: 6px;
    font-size: 14px;
}

.add-btn:hover {
    background: #264b2f;
}

/* Table */

.table-container {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: #f1f5ef;
    text-align: left;
    padding: 12px;
    color: #555;
    font-size: 14px;
}

td {
    padding: 12px;
    border-bottom: 1px solid #eee;
    font-size: 14px;
}

.edit-btn {
    color: #315d3b;
    text-decoration: none;
    margin-right: 10px;
}

.delete-btn {
    color: #c0392b;
    text-decoration: none;
}

/* Quick actions */

.action {
    display: block;
    text-decoration: none;
    background: #f1f5ef;
    color: #333;
    padding: 14px;
    margin-bottom: 12px;
    border-radius: 6px;
}

.action:hover {
    background: #e4ecdf;
}

/* Farmer information */

.farmer-info {
    margin-top: 30px;
    background: #315d3b;
    color: white;
    padding: 25px;
    border-radius: 10px;
}

.farmer-info h2 {
    margin-bottom: 10px;
}

.farmer-info p {
    line-height: 1.6;
}

.farmer-info a {
    display: inline-block;
    margin-top: 15px;
    text-decoration: none;
    background: white;
    color: #315d3b;
    padding: 10px 18px;
    border-radius: 6px;
}

/* Responsive */

@media(max-width: 900px) {

    .stats {
        grid-template-columns: repeat(2, 1fr);
    }

    .content {
        grid-template-columns: 1fr;
    }

    .navbar {
        padding: 0 25px;
    }

}

@media(max-width: 650px) {

    .stats {
        grid-template-columns: 1fr;
    }

    .nav-links {
        display: none;
    }

    .dashboard {
        width: 94%;
    }

}

</style>

</head>

<body>

<header class="navbar">

<div class="logo">
    🌾 নবান্ন
</div>

<nav class="nav-links">

<a href="farmer_dashboard.php" class="active">
    Dashboard
</a>

<a href="my_products.php">
    My Products
</a>

<a href="farmer_orders.php">
    Orders
</a>

<a href="farmer_profile.php">
    Profile
</a>

</nav>

<a href="logout.php" class="logout-btn">
    Logout
</a>

</header>


<main class="dashboard">

<div class="welcome">

<h1>
    Welcome, <?php echo clean($farmer_name); ?> 🌾
</h1>

<p>
    Manage your agricultural products and orders from here.
</p>

</div>


<div class="stats">

<div class="stat-card">
<h3>Total Products</h3>
<h2><?php echo $total_products; ?></h2>
</div>

<div class="stat-card">
<h3>Pending Orders</h3>
<h2><?php echo $pending_orders; ?></h2>
</div>

<div class="stat-card">
<h3>Total Stock</h3>
<h2><?php echo $total_stock; ?> KG</h2>
</div>

<div class="stat-card">
<h3>Total Sales</h3>
<h2>৳<?php echo number_format($total_sales); ?></h2>
</div>

</div>


<div class="content">


<div class="products">

<div class="products-header">

<h2>My Products</h2>

<a href="add_product.php" class="add-btn">
    + Add Product
</a>

</div>


<div class="table-container">

<table>

<tr>
<th>Crop</th>
<th>Quantity</th>
<th>Price</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php foreach ($_SESSION["farmer_products"] as $product): ?>

<tr>

<td>
    🌱 <?php echo clean($product["name"]); ?>
</td>

<td>
    <?php echo $product["quantity"]; ?> KG
</td>

<td>
    ৳<?php echo $product["price"]; ?>/KG
</td>

<td>
    <?php echo clean($product["status"]); ?>
</td>

<td>

<a href="edit_product.php?id=<?php echo $product["id"]; ?>" class="edit-btn">
    Edit
</a>

<a href="delete_product.php?id=<?php echo $product["id"]; ?>"
   class="delete-btn"
   onclick="return confirm('Are you sure you want to delete this product?');">
    Delete
</a>

</td>

</tr>

<?php endforeach; ?>

</table>

</div>

</div>


<div class="quick-actions">

<h2>Quick Actions</h2>

<br>

<a href="add_product.php" class="action">
    ➕ Add New Product
</a>

<a href="my_products.php" class="action">
    🥬 Manage Products
</a>

<a href="farmer_orders.php" class="action">
    📦 View Orders
</a>

<a href="sales_history.php" class="action">
    📊 Sales History
</a>

<a href="farmer_profile.php" class="action">
    👤 My Profile
</a>

</div>

</div>


<section class="farmer-info">

<h2>
    Are You Ready to Sell More? 🌾
</h2>

<p>
    Add your fresh agricultural products and sell them
    directly to customers through Nobanno.
</p>

<a href="add_product.php">
    + Add Your Product
</a>

</section>

</main>

</body>

</html>