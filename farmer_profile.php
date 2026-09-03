<?php

require_once "farmer_data.php";

$errors = [];

$message = "";

$name = $_SESSION["name"];
$email = $_SESSION["email"];
$phone = $_SESSION["phone"];
$address = $_SESSION["address"];


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $address = trim($_POST["address"] ?? "");


    if ($name == "") {
        $errors[] = "Name is required.";
    }
    elseif (strlen($name) < 3) {
        $errors[] = "Name must contain at least 3 characters.";
    }


    if ($email == "") {
        $errors[] = "Email is required.";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }


    if ($phone == "") {
        $errors[] = "Phone number is required.";
    }
    elseif (!preg_match("/^01[3-9][0-9]{8}$/", $phone)) {
        $errors[] = "Enter a valid Bangladeshi phone number.";
    }


    if ($address == "") {
        $errors[] = "Address is required.";
    }


    if (empty($errors)) {

        $_SESSION["name"] = $name;
        $_SESSION["email"] = $email;
        $_SESSION["phone"] = $phone;
        $_SESSION["address"] = $address;

        $message = "Profile updated successfully.";

    }

}

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Farmer Profile - Nobanno</title>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background: #f5f7f2;
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
    color: #555;
    text-decoration: none;
    padding: 25px 0;
}

.nav-links a:hover,
.nav-links a.active {
    color: #315d3b;
    font-weight: bold;
    border-bottom: 2px solid #315d3b;
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
    max-width: 800px;
    margin: 40px auto;
}

.card {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,.06);
}

h1 {
    color: #315d3b;
    margin-bottom: 25px;
}

.message {
    background: #e7f4e8;
    color: #27632a;
    padding: 14px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.errors {
    background: #fdeaea;
    color: #a93226;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.errors li {
    margin-left: 18px;
    margin-bottom: 5px;
}

.form-group {
    margin-bottom: 20px;
}

label {
    display: block;
    font-weight: bold;
    margin-bottom: 8px;
}

input,
textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 15px;
}

textarea {
    height: 100px;
    resize: vertical;
}

button {
    background: #315d3b;
    color: white;
    border: none;
    padding: 12px 22px;
    border-radius: 6px;
    cursor: pointer;
}

button:hover {
    background: #264b2f;
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

<a href="farmer_orders.php">
    Orders
</a>

<a href="farmer_profile.php" class="active">
    Profile
</a>

</nav>

<a href="logout.php" class="logout">
    Logout
</a>

</header>


<main class="container">

<div class="card">

<h1>My Profile</h1>


<?php if ($message): ?>

<div class="message">
    <?php echo clean($message); ?>
</div>

<?php endif; ?>


<?php if (!empty($errors)): ?>

<div class="errors">

<ul>

<?php foreach ($errors as $error): ?>

<li>
    <?php echo clean($error); ?>
</li>

<?php endforeach; ?>

</ul>

</div>

<?php endif; ?>


<form method="POST">

<div class="form-group">

<label>Full Name</label>

<input
    type="text"
    name="name"
    value="<?php echo clean($name); ?>"
>

</div>


<div class="form-group">

<label>Email</label>

<input
    type="email"
    name="email"
    value="<?php echo clean($email); ?>"
>

</div>


<div class="form-group">

<label>Phone</label>

<input
    type="text"
    name="phone"
    value="<?php echo clean($phone); ?>"
    placeholder="017XXXXXXXX"
>

</div>


<div class="form-group">

<label>Address</label>

<textarea name="address"><?php echo clean($address); ?></textarea>

</div>


<button type="submit">
    Save Profile
</button>

</form>

</div>

</main>

</body>

</html>