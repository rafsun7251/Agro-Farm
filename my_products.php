<?php

require_once "farmer_data.php";

$message = $_SESSION["product_message"] ?? "";
unset($_SESSION["product_message"]);

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Nobanno - My Products</title>

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

.navbar {
    height: 70px;
    background: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 60px;
    box-shadow: 0 2px 10px rgba(0,0,0,.08);
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
    padding: 25px 0;
}

.nav-links a.active,
.nav-links a:hover {
    color: #315d3b;
    font-weight: bold;
    border-bottom: 2px solid #315d3b;
}

.logout-btn {
    background: #315d3b;
    color: white;
    text-decoration: none;
    padding: 10px 20px;
    border-radius: 6px;
}

.container {
    width: 90%;
    max-width: 1200px;
    margin: 40px auto;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.page-header h1 {
    color: #315d3b;
}

.add-btn {
    background: #315d3b;
    color: white;
    padding: 11px 18px;
    border-radius: 6px;
    text-decoration: none;
}

.message {
    background: #e7f4e8;
    color: #27632a;
    padding: 14px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.card {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,.06);
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: #f1f5ef;
    padding: 13px;
    text-align: left;
}

td {
    padding: 13px;
    border-bottom: 1px solid #eee;
}

.edit {
    color: #315d3b;
    text-decoration: none;
    margin-right: 12px;
}

.delete {
    color: #c0392b;
    text-decoration: none;
}

.empty {
    text-align: center;
    padding: 40px;
    color: #777;
}

@media(max-width: 700px) {

    .nav-links {
        display: none;
    }

    .navbar {
        padding: 0 20px;
    }

    .container {
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

<a href="farmer_dashboard.php">
    Dashboard
</a>

<a href="my_products.php" class="active">
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


<main class="container">

<div class="page-header">

<h1>My Products</h1>

<a href="add_product.php" class="add-btn">
    + Add Product
</a>

</div>


<?php if ($message): ?>

<div class="message">
    <?php echo clean($message); ?>
</div>

<?php endif; ?>


<div class="card">

<?php if (count($_SESSION["farmer_products"]) == 0): ?>

<div class="empty">

<h3>No products found.</h3>

<p>Add your first agricultural product.</p>

</div>

<?php else: ?>

<table>

<tr>

<th>Product</th>
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
    ৳<?php echo number_format($product["price"], 2); ?>/KG
</td>

<td>
    <?php echo clean($product["status"]); ?>
</td>

<td>

<a class="edit"
   href="edit_product.php?id=<?php echo $product["id"]; ?>">
   Edit
</a>

<a class="delete"
   href="delete_product.php?id=<?php echo $product["id"]; ?>"
   onclick="return confirm('Delete this product?');">
   Delete
</a>

</td>

</tr>

<?php endforeach; ?>

</table>

<?php endif; ?>

</div>

</main>

</body>

</html>