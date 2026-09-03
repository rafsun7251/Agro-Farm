<!DOCTYPE html>
<head>
    <title>Nobanno - Crops</title>
    <link rel="stylesheet" href="../css/crops.css">
</head>
<body>
    <header class="navbar">
        <div class="logo">🌾নবান্ন</div>
        <nav><a href="home.php">Home</a>
            <a href="crops.php" class="active">Crops</a>
            <a href="about.php">About</a>
            <a href="contact.php">Contact</a>
        </nav>
        <div class="nav-buttons">
            <a href="login.php" class="login-btn">Login</a>
            <a href="register.php" class="register-btn">Register</a>
        </div>
    </header>
    <section class="page-header">
        <p>FRESH FROM FARM</p>
        <h1>Browse Crops</h1>
        <span>Find fresh agricultural products directly from local farmers.</span>
    </section>
    <section class="crop-tools">
        <div class="search-box">
            <input type="text" placeholder="Search crops..." >
            <button>Search</button>
        </div>
        <select>
            <option value="">All Categories</option>
            <option value="vegetables"> Vegetables</option>
            <option value="grains">Grains</option>
        <option value="fruits">Fruits</option>
        </select>
    </section>
    <section class="all-crops">
        <div class="crop-grid">
            <div class="crop-card">
            <div class="crop-info">
            <span class="category"> Vegetable</span>
            <h3>Potato</h3>
            <p>Fresh potato</p>
    <div class="crop-bottom">
 <strong> ৳60 / kg</strong>
<a href="product_details.php"> View Details</a>
</div>
</div>
</div>
    <div class="crop-card">
    <div class="crop-info">
    <span class="category">Vegetable</span>
    <h3>Tomato</h3>
    <p>Fresh red tomatoes</p>
    <div class="crop-bottom">
    <strong>৳80 / kg</strong>
    <a href="product_details.php">View Details</a>
</div>
</div>
</div>

    <div class="crop-card">
    <div class="crop-info">
    <span class="category">Vegetable</span>
    <h3>Onion</h3>
    <p>Fresh local onion</p>
    <div class="crop-bottom">
    <strong>৳70 / kg</strong>
<a href="product_details.php">View Details</a>
</div>
</div>
</div>
<div class="crop-card">
<div class="crop-info">
<span class="category">Grain</span>
<h3>Rice</h3>
<p>Premium quality rice</p>
<div class="crop-bottom">
<strong>৳75 / kg</strong>
<a href="product_details.php">View Details</a>
</div>
</div>
</div>        
</div>
</section>
</body>

</html>