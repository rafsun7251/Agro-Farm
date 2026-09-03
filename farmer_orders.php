<?php

require_once "farmer_data.php";

$message = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $order_id = intval($_POST["order_id"] ?? 0);
    $new_status = $_POST["status"] ?? "";


    $allowed_status = [
        "Pending",
        "Processing",
        "Completed",
        "Cancelled"
    ];


    if (!in_array($new_status, $allowed_status)) {

        $message = "Invalid order status.";

    }
    else {

        $found = false;

        foreach ($_SESSION["farmer_orders"] as &$order) {

            if ($order["id"] == $order_id) {

                $order["status"] = $new_status;

                $found = true;

                break;
            }

        }

        unset($order);


        if ($found) {
            $message = "Order #".$order_id." updated successfully.";
        }
        else {
            $message = "Order not found.";
        }

    }

}

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Farmer Orders - Nobanno</title>

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
    padding: 25px 0;
}

.nav-links a.active,
.nav-links a:hover {
    color: #315d3b;
    font-weight: bold;
    border-bottom: 2px solid #315d3b;
}

.logout {
    text-decoration: none;
    background: #315d3b;
    color: white;
    padding: 10px 18px;
    border-radius: 6px;
}

.container {
    width: 90%;
    max-width: 1200px;
    margin: 40px auto;
}

.header {
    margin-bottom: 25px;
}

.header h1 {
    color: #315d3b;
    margin-bottom: 8px;
}

.header p {
    color: #777;
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

select {
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.update-btn {
    border: none;
    background: #315d3b;
    color: white;
    padding: 8px 12px;
    border-radius: 5px;
    cursor: pointer;
}

.update-btn:hover {
    background: #264b2f;
}

.status {
    padding: 5px 9px;
    border-radius: 5px;
    background: #f1f5ef;
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

<a href="my_products.php">
    My Products
</a>

<a href="farmer_orders.php" class="active">
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

<div class="header">

<h1>Orders</h1>

<p>View and manage orders received from customers.</p>

</div>


<?php if ($message): ?>

<div class="message">
    <?php echo clean($message); ?>
</div>

<?php endif; ?>


<div class="card">

<table>

<tr>

<th>Order ID</th>
<th>Customer</th>
<th>Product</th>
<th>Quantity</th>
<th>Total</th>
<th>Date</th>
<th>Status</th>
<th>Update</th>

</tr>


<?php foreach ($_SESSION["farmer_orders"] as $order): ?>

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

<td>
    <span class="status">
        <?php echo clean($order["status"]); ?>
    </span>
</td>

<td>

<form method="POST">

<input
    type="hidden"
    name="order_id"
    value="<?php echo $order["id"]; ?>"
>


<select name="status">

<option value="Pending"
<?php echo $order["status"] == "Pending" ? "selected" : ""; ?>>
Pending
</option>

<option value="Processing"
<?php echo $order["status"] == "Processing" ? "selected" : ""; ?>>
Processing
</option>

<option value="Completed"
<?php echo $order["status"] == "Completed" ? "selected" : ""; ?>>
Completed
</option>

<option value="Cancelled"
<?php echo $order["status"] == "Cancelled" ? "selected" : ""; ?>>
Cancelled
</option>

</select>

<button class="update-btn" type="submit">
    Update
</button>

</form>

</td>

</tr>

<?php endforeach; ?>

</table>

</div>

</main>

</body>

</html>