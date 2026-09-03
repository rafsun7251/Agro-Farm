<?php

require_once __DIR__ . '/../admin_auth.php';

require_once __DIR__ . '/../db.php';



/* ==========================================
   DELETE SUCCESS MESSAGE
========================================== */

$deleteMessage = "";

if (isset($_GET['deleted']) && $_GET['deleted'] == 1) {

    $deleteMessage = "User deleted successfully.";

}


/* ==========================================
   FETCH USERS
========================================== */

$query = "
    SELECT
        user_id,
        first_name,
        last_name,
        email,
        role,
        phone,
        created_at
    FROM users
    ORDER BY user_id DESC
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

    <title>Agro-Farm | User Management</title>

    <link
        rel="stylesheet"
        href="../css/UserManagement.css"
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
                class="menu-item active"
            >

                <span class="menu-icon">
                    👥
                </span>

                User Management

            </a>


            <a
                href="CropManagement.php"
                class="menu-item"
            >

                <span class="menu-icon">
                    🌱
                </span>

                Crop Management

            </a>


            <a href="OrderManagement.php" class="menu-item">

                <span class="menu-icon">
                    🛒
                </span>

                Order Management

            </a>


           <a href="DeliveryManagement.php" class="menu-item">

                <span class="menu-icon">
                    🚚
                </span>

                Delivery Management

            </a>


            <a href="PaymentManagement.php" class="menu-item">

                <span class="menu-icon">
                    💳
                </span>

                Payment Management

            </a>


           <a href="AdminSettings.php" class="menu-item">

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

                <h1>User Management</h1>

                <p>
                    Manage admins, farmers, delivery men
                    and customers
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
                        All Users
                    </h3>

                    <p>
                        View and manage registered users
                    </p>

                </div>


                <a
                    href="AddUser.php"
                    class="add-user-btn"
                >

                    + Add New User

                </a>


            </div>



            <!-- FILTER ROW -->

            <div class="filter-row">


                <!-- SEARCH -->

                <div class="search-box">

                    <input
                        type="text"
                        id="searchInput"
                        placeholder="Search by name or email..."
                    >

                  <button
    type="button"
    id="searchButton"
>
    Search
</button>

                </div>


                <!-- ROLE FILTER -->

              <select id="roleFilter">

                    <option value="">
                        All Roles
                    </option>

                    <option value="admin">
                        Admin
                    </option>

                    <option value="farmer">
                        Farmer
                    </option>

                    <option value="delivery man">
                        Delivery Man
                    </option>

                    <option value="customer">
                        Customer
                    </option>

                </select>


            </div>


        </div>



        <!-- ==========================================
             USER TABLE
        =========================================== -->

        <div class="table-card">


            <div class="table-wrapper">


                <table id="userTable">


                    <thead>

                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                Name
                            </th>

                            <th>
                                Email
                            </th>

                            <th>
                                Phone
                            </th>

                            <th>
                                Role
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
                            $user =
                            mysqli_fetch_assoc($result)
                        ) {

                            $fullName =
                                $user['first_name']
                                . " "
                                . $user['last_name'];


                            $roleText =
                                ucwords(
                                    str_replace(
                                        "_",
                                        " ",
                                        $user['role']
                                    )
                                );


                            $roleClass =
                                str_replace(
                                    "_",
                                    "-",
                                    $user['role']
                                );


                            $initial =
                                strtoupper(
                                    substr(
                                        $user['first_name'],
                                        0,
                                        1
                                    )
                                );

                    ?>


                        <tr class="user-row">


                            <!-- ID -->

                            <td>

                                <?php
                                echo $user['user_id'];
                                ?>

                            </td>


                            <!-- NAME -->

                            <td>

                                <div class="user-name">


                                    <div class="user-avatar">

                                        <?php
                                        echo htmlspecialchars(
                                            $initial
                                        );
                                        ?>

                                    </div>


                                    <div>

                                        <strong>

                                            <?php
                                            echo htmlspecialchars(
                                                $fullName
                                            );
                                            ?>

                                        </strong>

                                    </div>


                                </div>

                            </td>


                            <!-- EMAIL -->

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $user['email']
                                );
                                ?>

                            </td>


                            <!-- PHONE -->

                            <td>

                                <?php

                                if (
                                    !empty(
                                        $user['phone']
                                    )
                                ) {

                                    echo htmlspecialchars(
                                        $user['phone']
                                    );

                                } else {

                                    echo "N/A";

                                }

                                ?>

                            </td>


                            <!-- ROLE -->

                            <td>

                                <span
                                    class="role <?php
                                    echo $roleClass;
                                    ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $roleText
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
                                        $user['created_at']
                                    )
                                );

                                ?>

                            </td>


                            <!-- ACTION -->

                            <td>

                                <div class="actions">


                                    <a
                                        href="EditUser.php?id=<?php
                                        echo $user['user_id'];
                                        ?>"
                                        class="edit-btn"
                                    >
                                        Edit
                                    </a>


                                    <a
                                        href="DeleteUser.php?id=<?php
                                        echo $user['user_id'];
                                        ?>"
                                        class="delete-btn"
                                        onclick="
                                        return confirm(
                                        'Are you sure you want to delete this user?'
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
                                colspan="7"
                                class="no-data"
                            >

                                No users found.

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

<script src="../controller/admin/AdminUsers.js"></script>




</body>

</html>