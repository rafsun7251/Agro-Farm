<?php

require_once "farmer_data.php";

$total = getTotalSales();

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Sales History - Nobanno</title>

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
    color: #315d3b;
    font-size: 24px;
    font-weight: bold;
}

.nav-links {
    display: flex;
    gap: 28px;
}

.nav-links a {
    text-decoration: none;
    color: #555;
}

.logout {
    background: #315d3b;
    color: white;
    text-decoration: none;
    padding: 10px 18px;
    border-radius: 6px;
}

.container {
    width: 90%;
    max-width: 1100px;
    margin: 40px auto;
}

h1 {
    color: #315d3b;
    margin-bottom: 25px;
}

.total-box {
    background: #315d3b;
    color: white;
    padding: 25px;
    border-radius: 10px;
    margin-bottom: 25px;
}

.total-box p {
    margin-bottom: 8px;
}

.total-box h2 {
    font-size: 30px;
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

@media(max-width:700px) {

    .nav-links {
        display: none;
    }

    .navbar {
        padding: 0 20px;
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

<a href="logout.php" class="logout">
    Logout
</a>

</header>


<main class="container">

<h1>Sales History</h1>


<div class="total-box">

<p>Total Completed Sales</p>

<h2>
    ৳<?php echo number_format($total); ?>
</h2>

</div>


<div class="card">

<table>

<tr>

<th>Order</th>
<th>Customer</th>
<th>Product</th>
<th>Quantity</th>
<th>Amount</th>
<th>Date</th>

</tr>


<?php

$hasSales = false;

foreach ($_SESSION["farmer_orders"] as $order):

    if ($order["status"] != "Completed") {
        continue;
    }

    $hasSales = true;

?>

<tr>

<td>
    #<?php echo $order["id"]; ?>
</td>

<td>
    <?php echo clean($order["customer"]); ?>
</td>

<td>
    <?php echo clean($order["product"]); ?>
</td>

<td>
    <?php echo $order["quantity"]; ?> KG
</td>

<td>
    ৳<?php echo number_format($order["total"]); ?>
</td>

<td>
    <?php echo clean($order["date"]); ?>
</td>

</tr>

<?php endforeach; ?>


<?php if (!$hasSales): ?>

<tr>

<td colspan="6" style="text-align:center;padding:30px;">
    No completed sales yet.
</td>

</tr>

<?php endif; ?>

</table>

</div>

</main>

</body>

</html>