<?php

require_once __DIR__ . '/../admin_auth.php';

require_once __DIR__ . '/../db.php';



// ==========================================
// SUCCESS MESSAGE
// ==========================================

$message = "";

if (isset($_GET['updated']) && $_GET['updated'] == 1) {

    $message = "Order updated successfully.";

}

if (isset($_GET['deleted']) && $_GET['deleted'] == 1) {

    $message = "Order deleted successfully.";

}


// ==========================================
// FETCH ORDERS
// ==========================================

$query = "
    SELECT
        o.order_id,
        o.order_date,
        o.total_amount,
        o.delivery_charge,
        o.platform_fee,
        o.status,
        o.delivery_address,

        c.customer_id,

        u.first_name,
        u.last_name,
        u.email,
        u.phone

    FROM orders o

    INNER JOIN customers c
        ON o.customer_id = c.customer_id

    INNER JOIN users u
        ON c.user_id = u.user_id

    ORDER BY o.order_id DESC
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

    <title>
        Agro-Farm | Order Management
    </title>

    <link
        rel="stylesheet"
        href="../css/OrderManagement.css"
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

                <h2>
                    নবান্ন
                </h2>

                <p>
                    Admin Panel
                </p>

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
                class="menu-item"
            >

                <span class="menu-icon">
                    🌱
                </span>

                Crop Management

            </a>


            <a
                href="OrderManagement.php"
                class="menu-item active"
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
                    Order Management
                </h1>

                <p>
                    View and manage customer orders
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

        <?php if (!empty($message)) { ?>

            <div class="success-message">

                <?php
                echo htmlspecialchars(
                    $message
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
                        All Orders
                    </h3>

                    <p>
                        View customer order information
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
                        placeholder="Search order or customer..."
                    >

                  <button
    type="button"
    id="searchButton"
>
    Search
</button>

                </div>


                <!-- STATUS FILTER -->

               <select id="statusFilter">

                    <option value="">
                        All Status
                    </option>

                    <option value="pending">
                        Pending
                    </option>

                    <option value="processing">
                        Processing
                    </option>

                    <option value="out for delivery">
                        Out for Delivery
                    </option>

                    <option value="completed">
                        Completed
                    </option>

                    <option value="cancelled">
                        Cancelled
                    </option>

                </select>


            </div>


        </div>



        <!-- ==========================================
             ORDERS TABLE
        =========================================== -->

        <div class="table-card">


            <div class="table-wrapper">


                <table id="orderTable">


                    <thead>

                        <tr>

                            <th>
                                Order ID
                            </th>

                            <th>
                                Customer
                            </th>

                            <th>
                                Contact
                            </th>

                            <th>
                                Total
                            </th>

                            <th>
                                Delivery Charge
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Order Date
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
                            $order =
                            mysqli_fetch_assoc($result)
                        ) {


                            // Customer name

                            $customerName =
                                $order['first_name']
                                . " "
                                . $order['last_name'];


                            // Status text

                            $statusText =
                                ucwords(
                                    str_replace(
                                        "_",
                                        " ",
                                        $order['status']
                                    )
                                );


                            // Status CSS class

                            $statusClass =
                                str_replace(
                                    "_",
                                    "-",
                                    $order['status']
                                );


                            // Customer initial

                            $initial =
                                strtoupper(
                                    substr(
                                        $order['first_name'],
                                        0,
                                        1
                                    )
                                );

                    ?>


                        <tr class="order-row">


                            <!-- ORDER ID -->

                            <td>

                                <strong class="order-id">

                                    #<?php

                                    echo $order['order_id'];

                                    ?>

                                </strong>

                            </td>


                            <!-- CUSTOMER -->

                            <td>

                                <div class="customer-name">


                                    <div class="customer-avatar">

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
                                                $customerName
                                            );

                                            ?>

                                        </strong>

                                    </div>


                                </div>

                            </td>


                            <!-- CONTACT -->

                            <td>

                                <div class="contact-info">

                                    <span>

                                        <?php

                                        echo htmlspecialchars(
                                            $order['email']
                                        );

                                        ?>

                                    </span>


                                    <small>

                                        <?php

                                        if (
                                            !empty(
                                                $order['phone']
                                            )
                                        ) {

                                            echo htmlspecialchars(
                                                $order['phone']
                                            );

                                        } else {

                                            echo "No phone";

                                        }

                                        ?>

                                    </small>

                                </div>

                            </td>


                            <!-- TOTAL -->

                            <td>

                                <strong class="amount">

                                    ৳<?php

                                    echo number_format(
                                        $order['total_amount'],
                                        2
                                    );

                                    ?>

                                </strong>

                            </td>


                            <!-- DELIVERY CHARGE -->

                            <td>

                                ৳<?php

                                echo number_format(
                                    $order['delivery_charge'],
                                    2
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


                            <!-- ORDER DATE -->

                            <td>

                                <div class="date-info">

                                    <?php

                                    echo date(
                                        "d M Y",
                                        strtotime(
                                            $order['order_date']
                                        )
                                    );

                                    ?>


                                    <small>

                                        <?php

                                        echo date(
                                            "h:i A",
                                            strtotime(
                                                $order['order_date']
                                            )
                                        );

                                        ?>

                                    </small>

                                </div>

                            </td>


                            <!-- ACTION -->

                            <td>

                                <div class="actions">


                                    <a
                                        href="EditOrder.php?id=<?php
                                        echo $order['order_id'];
                                        ?>"
                                        class="edit-btn"
                                    >

                                        Edit

                                    </a>


                                  <a
    href="DeleteOrder.php?id=<?php
    echo $order['order_id'];
    ?>"
    class="delete-btn"
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
                                colspan="8"
                                class="no-data"
                            >

                                No orders found.

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


<script src="../controller/admin/AdminOrders.js"></script>

</body>

</html>