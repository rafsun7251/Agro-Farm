<?php

require_once __DIR__ . '/../admin_auth.php';

require_once __DIR__ . '/../db.php';


/* ==========================================
   DELETE SUCCESS MESSAGE
========================================== */
$deleteMessage = "";

if (isset($_GET['deleted']) && $_GET['deleted'] == 1) {

    $deleteMessage = "Crop deleted successfully.";

}

if (isset($_GET['updated']) && $_GET['updated'] == 1) {

    $deleteMessage = "Crop updated successfully.";

}
/* ==========================================
   FETCH ALL CROPS
========================================== */

$query = "
    SELECT
        c.crop_id,
        c.crop_name,
        c.category,
        c.description,
        c.price_per_kg,
        c.quantity,
        c.unit,
        c.status,
        c.created_at,

        f.farmer_id,

        u.first_name,
        u.last_name

    FROM crops c

    INNER JOIN farmers f
        ON c.farmer_id = f.farmer_id

    INNER JOIN users u
        ON f.user_id = u.user_id

    ORDER BY c.crop_id DESC
";


$result = mysqli_query($conn, $query);


if (!$result) {

    die(
        "Database query failed: "
        . mysqli_error($conn)
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

    <title>Agro-Farm | Crop Management</title>

    <link
        rel="stylesheet"
        href="../css/CropManagement.css"
    >

</head>


<body>


<div class="dashboard">


    <!-- ==========================================
         SIDEBAR
    =========================================== -->

    <aside class="sidebar">


        <!-- LOGO -->

        <div class="logo-section">

            <div class="logo-icon">
                🌾
            </div>

            <div class="logo-text">

                <h2>নবান্ন</h2>

                <p>Admin Panel</p>

            </div>

        </div>


        <!-- MENU -->

        <nav class="sidebar-menu">


            <a
                href="AdminDashboard.php"
                class="menu-item"
            >

                <span class="menu-icon">
                    ▣
                </span>

                Dashboard

            </a>


            <a
                href="UserManagement.php"
                class="menu-item"
            >

                <span class="menu-icon">
                    👥
                </span>

                User Management

            </a>


            <a
                href="CropManagement.php"
                class="menu-item active"
            >

                <span class="menu-icon">
                    🌱
                </span>

                Crop Management

            </a>


            <a
                href="OrderManagement.php"
                class="menu-item"
            >

                <span class="menu-icon">
                    🛒
                </span>

                Order Management

            </a>


            <a
                href="DeliveryManagement.php"
                class="menu-item"
            >

                <span class="menu-icon">
                    🚚
                </span>

                Delivery Management

            </a>


            <a
                href="PaymentManagement.php"
                class="menu-item"
            >

                <span class="menu-icon">
                    💳
                </span>

                Payment Management

            </a>


            <a
                href="AdminSettings.php"
                class="menu-item"
            >

                <span class="menu-icon">
                    ⚙
                </span>

                Settings

            </a>


        </nav>


        <!-- LOGOUT -->

        <div class="sidebar-bottom">

           <a
    href="../logout.php"
    class="menu-item logout"
>

                <span class="menu-icon">
                    ↪
                </span>

                Logout

            </a>

        </div>


    </aside>



    <!-- ==========================================
         MAIN CONTENT
    =========================================== -->

    <main class="main-content">


        <!-- ==========================================
             TOP BAR
        =========================================== -->

        <div class="topbar">


            <div class="page-title">

                <h1>
                    Crop Management
                </h1>

                <p>
                    Manage crops listed by farmers
                </p>

            </div>


            <!-- ADMIN PROFILE -->

            <div class="admin-profile">


                <div class="profile-icon">
                    A
                </div>


                <div class="profile-info">

                    <strong>
                        Administrator
                    </strong>

                    <small>
                        Admin
                    </small>

                </div>


            </div>


        </div>



        <!-- ==========================================
             SUCCESS MESSAGE
        =========================================== -->

        <?php if (!empty($deleteMessage)) { ?>

            <div class="success-message">

                <?php
                echo htmlspecialchars(
                    $deleteMessage
                );
                ?>

            </div>

        <?php } ?>



        <!-- ==========================================
             FILTER CARD
        =========================================== -->

        <div class="filter-card">


            <div class="filter-header">


                <div>

                    <h3>
                        All Crops
                    </h3>

                    <p>
                        View and manage farmer crop listings
                    </p>

                </div>


            </div>



            <!-- FILTER ROW -->

            <div class="filter-row">


                <!-- SEARCH -->

                <div class="search-box">

                    <input
                        type="text"
                        id="searchInput"
                        placeholder="Search crop or farmer..."
                    >

                  <button
    type="button"
    id="searchButton"
>
    Search
</button>

                </div>


                <!-- CATEGORY -->

               <select id="categoryFilter">

                    <option value="">
                        All Categories
                    </option>

                    <option value="vegetables">
                        Vegetables
                    </option>

                    <option value="grains">
                        Grains
                    </option>

                    <option value="fruits">
                        Fruits
                    </option>

                    <option value="other">
                        Other
                    </option>

                </select>


                <!-- STATUS -->

              <select id="statusFilter">
                    <option value="">
                        All Status
                    </option>

                    <option value="available">
                        Available
                    </option>

                    <option value="out of stock">
                        Out of Stock
                    </option>

                    <option value="inactive">
                        Inactive
                    </option>

                </select>


            </div>


        </div>



        <!-- ==========================================
             CROPS TABLE
        =========================================== -->

        <div class="table-card">


            <div class="table-wrapper">


                <table id="cropTable">


                    <thead>

                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                Crop
                            </th>

                            <th>
                                Farmer
                            </th>

                            <th>
                                Category
                            </th>

                            <th>
                                Price / Kg
                            </th>

                            <th>
                                Quantity
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Created
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php

                    if (mysqli_num_rows($result) > 0) {

                        while (
                            $crop =
                            mysqli_fetch_assoc($result)
                        ) {


                            /* Crop name */

                            $cropName =
                                $crop['crop_name'];


                            /* Farmer name */

                            $farmerName =
                                $crop['first_name']
                                . " "
                                . $crop['last_name'];


                            /* Category */

                            $categoryText =
                                ucfirst(
                                    $crop['category']
                                );


                            /* Status */

                            $statusText =
                                ucwords(
                                    str_replace(
                                        "_",
                                        " ",
                                        $crop['status']
                                    )
                                );


                            /* CSS class */

                            $statusClass =
                                str_replace(
                                    "_",
                                    "-",
                                    $crop['status']
                                );


                            $categoryClass =
                                $crop['category'];


                    ?>


                        <tr class="crop-row">


                            <!-- ID -->

                            <td>

                                <?php
                                echo $crop['crop_id'];
                                ?>

                            </td>


                            <!-- CROP -->

                            <td>

                                <div class="crop-name">


                                    <div class="crop-icon">

                                        🌱

                                    </div>


                                    <div>

                                        <strong>

                                            <?php
                                            echo htmlspecialchars(
                                                $cropName
                                            );
                                            ?>

                                        </strong>


                                        <?php

                                        if (
                                            !empty(
                                                $crop['description']
                                            )
                                        ) {

                                        ?>

                                            <small>

                                                <?php

                                                echo htmlspecialchars(
                                                    $crop['description']
                                                );

                                                ?>

                                            </small>

                                        <?php

                                        }

                                        ?>

                                    </div>


                                </div>

                            </td>


                            <!-- FARMER -->

                            <td>

                                <div class="farmer-name">

                                    <div class="farmer-avatar">

                                        <?php

                                        echo strtoupper(
                                            substr(
                                                $crop['first_name'],
                                                0,
                                                1
                                            )
                                        );

                                        ?>

                                    </div>


                                    <span>

                                        <?php

                                        echo htmlspecialchars(
                                            $farmerName
                                        );

                                        ?>

                                    </span>

                                </div>

                            </td>


                            <!-- CATEGORY -->

                            <td>

                                <span
                                    class="category
                                    <?php
                                    echo $categoryClass;
                                    ?>"
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $categoryText
                                    );

                                    ?>

                                </span>

                            </td>


                            <!-- PRICE -->

                            <td>

                                <strong class="price">

                                    ৳<?php

                                    echo number_format(
                                        $crop['price_per_kg'],
                                        2
                                    );

                                    ?>

                                </strong>

                            </td>


                            <!-- QUANTITY -->

                            <td>

                                <?php

                                echo number_format(
                                    $crop['quantity'],
                                    2
                                );

                                ?>

                                <?php

                                echo htmlspecialchars(
                                    $crop['unit']
                                );

                                ?>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <span
                                    class="status
                                    <?php
                                    echo $statusClass;
                                    ?>"
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $statusText
                                    );

                                    ?>

                                </span>

                            </td>


                            <!-- CREATED -->

                            <td>

                                <?php

                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $crop['created_at']
                                    )
                                );

                                ?>

                            </td>


                            <!-- ACTION -->

                            <td>

                                <div class="actions">


                                    <a
                                        href="EditCrop.php?id=<?php
                                        echo $crop['crop_id'];
                                        ?>"
                                        class="edit-btn"
                                    >

                                        Edit

                                    </a>


                                    <a
                                        href="DeleteCrop.php?id=<?php
                                        echo $crop['crop_id'];
                                        ?>"
                                        class="delete-btn"
                                        onclick="
                                        return confirm(
                                        'Are you sure you want to delete this crop?'
                                        );
                                        "
                                    >

                                        Delete

                                    </a>


                                </div>

                            </td>


                        </tr>


                    <?php

                        }

                    } else {

                    ?>


                        <tr>

                            <td
                                colspan="9"
                                class="no-data"
                            >

                                No crops found.

                            </td>

                        </tr>


                    <?php

                    }

                    ?>


                    </tbody>


                </table>


            </div>


        </div>


    </main>


</div>



<script src="../controller/admin/AdminCrops.js"></script>

</body>

</html>