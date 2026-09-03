<?php

session_start();

/*
|--------------------------------------------------------------------------
| CUSTOMER AUTHENTICATION
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'customer'
) {
    header("Location: ../login.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
|
| cart.php location:
| views/CustomerDashboard/cart.php
|
| db.php location:
| Agro-Farm/db.php
|
*/

require_once __DIR__ . '/../../db.php';


/*
|--------------------------------------------------------------------------
| CURRENT USER
|--------------------------------------------------------------------------
*/

$userId = (int) $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| FIND CUSTOMER ID
|--------------------------------------------------------------------------
|
| users.user_id → customers.user_id
|
*/

$customerQuery = "
    SELECT
        c.customer_id,
        c.address,
        u.first_name,
        u.last_name,
        u.email,
        u.phone

    FROM customers c

    INNER JOIN users u
        ON c.user_id = u.user_id

    WHERE c.user_id = ?

    LIMIT 1
";


$customerStmt = mysqli_prepare(
    $conn,
    $customerQuery
);


if (!$customerStmt) {
    die(
        "Database error: "
        . mysqli_error($conn)
    );
}


mysqli_stmt_bind_param(
    $customerStmt,
    "i",
    $userId
);


mysqli_stmt_execute(
    $customerStmt
);


$customerResult =
    mysqli_stmt_get_result(
        $customerStmt
    );


if (
    mysqli_num_rows(
        $customerResult
    ) === 0
) {
    die(
        "Customer profile not found. Please contact support."
    );
}


$customer =
    mysqli_fetch_assoc(
        $customerResult
    );


$customerId =
    (int) $customer['customer_id'];


/*
|--------------------------------------------------------------------------
| MESSAGE VARIABLES
|--------------------------------------------------------------------------
*/

$successMessage = "";
$errorMessage = "";


/*
|--------------------------------------------------------------------------
| REMOVE CART ITEM
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    &&
    isset($_POST['remove_item'])
) {

    $cartId =
        isset($_POST['cart_id'])
            ? (int) $_POST['cart_id']
            : 0;


    if ($cartId > 0) {

        $deleteQuery = "
            DELETE FROM cart

            WHERE cart_id = ?
            AND customer_id = ?
        ";


        $deleteStmt =
            mysqli_prepare(
                $conn,
                $deleteQuery
            );


        mysqli_stmt_bind_param(
            $deleteStmt,
            "ii",
            $cartId,
            $customerId
        );


        if (
            mysqli_stmt_execute(
                $deleteStmt
            )
        ) {

            $successMessage =
                "Item removed from your cart.";

        } else {

            $errorMessage =
                "Unable to remove the item.";
        }


        mysqli_stmt_close(
            $deleteStmt
        );
    }
}


/*
|--------------------------------------------------------------------------
| UPDATE CART ITEM QUANTITY
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    &&
    isset($_POST['update_quantity'])
) {

    $cartId =
        isset($_POST['cart_id'])
            ? (int) $_POST['cart_id']
            : 0;


    $newQuantity =
        isset($_POST['quantity'])
            ? (float) $_POST['quantity']
            : 0;


    if (
        $cartId <= 0 ||
        $newQuantity <= 0
    ) {

        $errorMessage =
            "Please enter a valid quantity.";

    } else {

        /*
        | Get available stock first.
        */

        $stockQuery = "
            SELECT
                c.quantity,
                c.status

            FROM cart ca

            INNER JOIN crops c
                ON ca.crop_id = c.crop_id

            WHERE ca.cart_id = ?
            AND ca.customer_id = ?

            LIMIT 1
        ";


        $stockStmt =
            mysqli_prepare(
                $conn,
                $stockQuery
            );


        mysqli_stmt_bind_param(
            $stockStmt,
            "ii",
            $cartId,
            $customerId
        );


        mysqli_stmt_execute(
            $stockStmt
        );


        $stockResult =
            mysqli_stmt_get_result(
                $stockStmt
            );


        if (
            mysqli_num_rows(
                $stockResult
            ) === 0
        ) {

            $errorMessage =
                "Cart item not found.";

        } else {

            $stock =
                mysqli_fetch_assoc(
                    $stockResult
                );


            $availableStock =
                (float) $stock['quantity'];


            $cropStatus =
                $stock['status'];


            if (
                $cropStatus !== 'available'
            ) {

                $errorMessage =
                    "This crop is currently unavailable.";

            } elseif (
                $newQuantity > $availableStock
            ) {

                $errorMessage =
                    "Only "
                    . number_format(
                        $availableStock,
                        2
                    )
                    . " units are available.";

            } else {

                $updateQuery = "
                    UPDATE cart

                    SET quantity = ?

                    WHERE cart_id = ?
                    AND customer_id = ?
                ";


                $updateStmt =
                    mysqli_prepare(
                        $conn,
                        $updateQuery
                    );


                mysqli_stmt_bind_param(
                    $updateStmt,
                    "dii",
                    $newQuantity,
                    $cartId,
                    $customerId
                );


                if (
                    mysqli_stmt_execute(
                        $updateStmt
                    )
                ) {

                    $successMessage =
                        "Cart quantity updated.";

                } else {

                    $errorMessage =
                        "Unable to update cart.";
                }


                mysqli_stmt_close(
                    $updateStmt
                );
            }
        }


        mysqli_stmt_close(
            $stockStmt
        );
    }
}


/*
|--------------------------------------------------------------------------
| FETCH CART ITEMS
|--------------------------------------------------------------------------
|
| cart
|   ↓
| crops
|   ↓
| farmers
|   ↓
| users
|
*/

$cartQuery = "
    SELECT

        ca.cart_id,
        ca.crop_id,
        ca.quantity AS cart_quantity,
        ca.added_at,

        c.crop_name,
        c.category,
        c.description,
        c.price_per_kg,
        c.quantity AS available_quantity,
        c.unit,
        c.status,

        f.farmer_id,
        f.farm_name,
        f.location,

        u.first_name AS farmer_first_name,
        u.last_name AS farmer_last_name

    FROM cart ca

    INNER JOIN crops c
        ON ca.crop_id = c.crop_id

    INNER JOIN farmers f
        ON c.farmer_id = f.farmer_id

    INNER JOIN users u
        ON f.user_id = u.user_id

    WHERE ca.customer_id = ?

    ORDER BY ca.added_at DESC
";


$cartStmt =
    mysqli_prepare(
        $conn,
        $cartQuery
    );


if (!$cartStmt) {

    die(
        "Cart query failed: "
        . mysqli_error($conn)
    );
}


mysqli_stmt_bind_param(
    $cartStmt,
    "i",
    $customerId
);


mysqli_stmt_execute(
    $cartStmt
);


$cartResult =
    mysqli_stmt_get_result(
        $cartStmt
    );


/*
|--------------------------------------------------------------------------
| CALCULATE CART TOTALS
|--------------------------------------------------------------------------
*/

$cartItems = [];

$subtotal = 0;

$totalItems = 0;


while (
    $item =
    mysqli_fetch_assoc(
        $cartResult
    )
) {

    $quantity =
        (float) $item['cart_quantity'];


    $price =
        (float) $item['price_per_kg'];


    $itemSubtotal =
        $quantity * $price;


    $item['item_subtotal'] =
        $itemSubtotal;


    $cartItems[] =
        $item;


    $subtotal +=
        $itemSubtotal;


    $totalItems +=
        $quantity;
}


mysqli_stmt_close(
    $cartStmt
);


/*
|--------------------------------------------------------------------------
| DELIVERY CHARGE
|--------------------------------------------------------------------------
|
| For now:
|
| Cart total < ৳1000  → ৳60
| Cart total >= ৳1000 → Free
|
| This can be changed later according
| to your project's final delivery policy.
|--------------------------------------------------------------------------
*/

$deliveryCharge = 0;


if ($subtotal > 0) {

    if ($subtotal >= 1000) {

        $deliveryCharge = 0;

    } else {

        $deliveryCharge = 60;
    }
}


/*
|--------------------------------------------------------------------------
| GRAND TOTAL
|--------------------------------------------------------------------------
*/

$grandTotal =
    $subtotal +
    $deliveryCharge;


/*
|--------------------------------------------------------------------------
| CUSTOMER DISPLAY NAME
|--------------------------------------------------------------------------
*/

$customerName =
    trim(
        $customer['first_name']
        . " "
        . $customer['last_name']
    );


$initial =
    strtoupper(
        substr(
            $customer['first_name'],
            0,
            1
        )
    );

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
        নবান্ন | My Cart
    </title>


    <link
        rel="stylesheet"
        href="../../css/CustomerDashboard/cart.css"
    >

</head>


<body>


<!-- =========================================================
     NAVBAR
========================================================= -->

<header class="customer-navbar">


    <!-- LOGO -->

    <a
        href="customer_dashboard.php"
        class="brand"
    >

        <span class="brand-icon">
            🌾
        </span>

        <span class="brand-name">
            নবান্ন
        </span>

    </a>


    <!-- NAVIGATION -->

    <nav class="customer-nav">

        <a
            href="customer_dashboard.php"
        >
            Dashboard
        </a>


        <a
            href="../../crops.php"
        >
            Crops
        </a>


        <a
            href="cart.php"
            class="active"
        >

            Cart

            <?php if ($totalItems > 0): ?>

                <span class="nav-cart-count">
                    <?php
                    echo number_format(
                        $totalItems,
                        0
                    );
                    ?>
                </span>

            <?php endif; ?>

        </a>


        <a
            href="orders.php"
        >
            Orders
        </a>


        <a
            href="profile.php"
        >
            Profile
        </a>

    </nav>


    <!-- USER AREA -->

    <div class="user-area">


        <div class="user-avatar">
            <?php
            echo htmlspecialchars(
                $initial
            );
            ?>
        </div>


        <div class="user-info">

            <span>
                Welcome
            </span>

            <strong>
                <?php
                echo htmlspecialchars(
                    $customer['first_name']
                );
                ?>
            </strong>

        </div>


        <a
            href="../../logout.php"
            class="logout-btn"
        >
            Logout
        </a>

    </div>


</header>



<!-- =========================================================
     PAGE HEADER
========================================================= -->

<section class="cart-header">


    <div>

        <span class="page-label">
            CUSTOMER SHOPPING CART
        </span>


        <h1>
            My Cart
        </h1>


        <p>
            Review your selected products
            before placing your order.
        </p>

    </div>


    <div class="cart-header-icon">
        🛒
    </div>


</section>



<!-- =========================================================
     MAIN CONTENT
========================================================= -->

<main class="cart-container">


    <!-- =====================================================
         MESSAGES
    ====================================================== -->

    <?php if ($successMessage !== ""): ?>

        <div class="alert success">

            <span>
                ✓
            </span>

            <?php
            echo htmlspecialchars(
                $successMessage
            );
            ?>

        </div>

    <?php endif; ?>


    <?php if ($errorMessage !== ""): ?>

        <div class="alert error">

            <span>
                !
            </span>

            <?php
            echo htmlspecialchars(
                $errorMessage
            );
            ?>

        </div>

    <?php endif; ?>



    <!-- =====================================================
         EMPTY CART
    ====================================================== -->

    <?php if (empty($cartItems)): ?>


        <section class="empty-cart">


            <div class="empty-cart-icon">
                🛒
            </div>


            <h2>
                Your cart is empty
            </h2>


            <p>
                You haven't added any agricultural
                products to your cart yet.
            </p>


            <a
                href="../../crops.php"
                class="browse-btn"
            >
                Browse Fresh Crops
                <span>→</span>
            </a>


        </section>


    <?php else: ?>


        <!-- =================================================
             CART LAYOUT
        ================================================== -->

        <div class="cart-layout">


            <!-- =============================================
                 CART ITEMS
            ============================================== -->

            <section class="cart-items-section">


                <div class="section-top">


                    <div>

                        <span class="section-label">
                            SELECTED PRODUCTS
                        </span>

                        <h2>
                            Cart Items
                        </h2>

                    </div>


                    <span class="item-count">

                        <?php
                        echo count(
                            $cartItems
                        );
                        ?>

                        <?php
                        echo count(
                            $cartItems
                        ) === 1
                            ? ' Product'
                            : ' Products';
                        ?>

                    </span>


                </div>



                <div class="cart-items">


                    <?php foreach (
                        $cartItems
                        as $item
                    ): ?>


                        <?php

                        $categoryLabel =
                            ucfirst(
                                $item['category']
                            );


                        $cropIcon = "🌱";


                        if (
                            strtolower(
                                $item['crop_name']
                            ) === 'potato'
                        ) {

                            $cropIcon = "🥔";

                        } elseif (
                            strtolower(
                                $item['crop_name']
                            ) === 'tomato'
                        ) {

                            $cropIcon = "🍅";

                        } elseif (
                            strtolower(
                                $item['crop_name']
                            ) === 'onion'
                        ) {

                            $cropIcon = "🧅";

                        } elseif (
                            strtolower(
                                $item['crop_name']
                            ) === 'rice'
                        ) {

                            $cropIcon = "🌾";
                        }


                        $farmerName =
                            trim(
                                $item['farmer_first_name']
                                . " "
                                . $item['farmer_last_name']
                            );


                        if (
                            $item['farm_name']
                            !== null
                            &&
                            trim(
                                $item['farm_name']
                            ) !== ""
                        ) {

                            $farmerDisplay =
                                $item['farm_name'];

                        } else {

                            $farmerDisplay =
                                $farmerName;
                        }

                        ?>


                        <article class="cart-item">


                            <!-- PRODUCT ICON -->

                            <div class="cart-product-image">

                                <span>
                                    <?php
                                    echo $cropIcon;
                                    ?>
                                </span>

                            </div>


                            <!-- PRODUCT INFORMATION -->

                            <div class="cart-product-info">


                                <div class="product-top">


                                    <div>

                                        <span
                                            class="category-tag"
                                        >
                                            <?php
                                            echo htmlspecialchars(
                                                $categoryLabel
                                            );
                                            ?>
                                        </span>


                                        <h3>

                                            <?php
                                            echo htmlspecialchars(
                                                $item['crop_name']
                                            );
                                            ?>

                                        </h3>

                                    </div>


                                    <div class="item-price">

                                        ৳<?php
                                        echo number_format(
                                            $item['price_per_kg'],
                                            2
                                        );
                                        ?>

                                        <span>
                                            /
                                            <?php
                                            echo htmlspecialchars(
                                                $item['unit']
                                            );
                                            ?>
                                        </span>

                                    </div>


                                </div>



                                <div class="farmer-line">

                                    <span>
                                        👨‍🌾
                                    </span>

                                    <span>
                                        <?php
                                        echo htmlspecialchars(
                                            $farmerDisplay
                                        );
                                        ?>
                                    </span>

                                </div>



                                <div class="stock-line">

                                    Available:

                                    <strong>

                                        <?php
                                        echo number_format(
                                            $item['available_quantity'],
                                            2
                                        );
                                        ?>

                                        <?php
                                        echo htmlspecialchars(
                                            $item['unit']
                                        );
                                        ?>

                                    </strong>

                                </div>



                                <!-- ITEM ACTIONS -->

                                <div class="item-actions">


                                    <!-- QUANTITY FORM -->

                                    <form
                                        method="POST"
                                        class="quantity-form"
                                    >

                                        <input
                                            type="hidden"
                                            name="cart_id"
                                            value="<?php
                                            echo (int)
                                                $item['cart_id'];
                                            ?>"
                                        >


                                        <input
                                            type="hidden"
                                            name="update_quantity"
                                            value="1"
                                        >


                                        <label>
                                            Quantity
                                        </label>


                                        <div class="quantity-control">

                                            <button
                                                type="button"
                                                class="quantity-btn minus"
                                                onclick="changeQuantity(
                                                    this,
                                                    -1
                                                )"
                                            >
                                                −
                                            </button>


                                            <input
                                                type="number"
                                                name="quantity"
                                                class="quantity-input"
                                                value="<?php
                                                echo htmlspecialchars(
                                                    $item['cart_quantity']
                                                );
                                                ?>"
                                                min="0.01"
                                                max="<?php
                                                echo htmlspecialchars(
                                                    $item['available_quantity']
                                                );
                                                ?>"
                                                step="0.01"
                                            >


                                            <button
                                                type="button"
                                                class="quantity-btn plus"
                                                onclick="changeQuantity(
                                                    this,
                                                    1
                                                )"
                                            >
                                                +
                                            </button>

                                        </div>


                                        <button
                                            type="submit"
                                            class="update-btn"
                                        >
                                            Update
                                        </button>

                                    </form>



                                    <!-- SUBTOTAL -->

                                    <div class="item-subtotal">

                                        <span>
                                            Subtotal
                                        </span>

                                        <strong>

                                            ৳<?php
                                            echo number_format(
                                                $item['item_subtotal'],
                                                2
                                            );
                                            ?>

                                        </strong>

                                    </div>



                                    <!-- REMOVE -->

                                    <form
                                        method="POST"
                                        class="remove-form"
                                        onsubmit="
                                            return confirm(
                                                'Remove this item from your cart?'
                                            );
                                        "
                                    >

                                        <input
                                            type="hidden"
                                            name="cart_id"
                                            value="<?php
                                            echo (int)
                                                $item['cart_id'];
                                            ?>"
                                        >


                                        <input
                                            type="hidden"
                                            name="remove_item"
                                            value="1"
                                        >


                                        <button
                                            type="submit"
                                            class="remove-btn"
                                        >
                                            Remove
                                        </button>

                                    </form>


                                </div>


                            </div>


                        </article>


                    <?php endforeach; ?>


                </div>


                <!-- CONTINUE SHOPPING -->

                <a
                    href="../../crops.php"
                    class="continue-shopping"
                >
                    ← Continue Shopping
                </a>


            </section>



            <!-- =============================================
                 ORDER SUMMARY
            ============================================== -->

            <aside class="order-summary">


                <div class="summary-header">

                    <div>

                        <span>
                            ORDER SUMMARY
                        </span>

                        <h2>
                            Your Total
                        </h2>

                    </div>

                    <span class="summary-icon">
                        🧾
                    </span>

                </div>



                <div class="summary-details">


                    <div class="summary-row">

                        <span>
                            Items
                        </span>

                        <strong>
                            <?php
                            echo number_format(
                                $totalItems,
                                2
                            );
                            ?>
                        </strong>

                    </div>


                    <div class="summary-row">

                        <span>
                            Subtotal
                        </span>

                        <strong>

                            ৳<?php
                            echo number_format(
                                $subtotal,
                                2
                            );
                            ?>

                        </strong>

                    </div>


                    <div class="summary-row">

                        <span>
                            Delivery
                        </span>

                        <strong>

                            <?php if (
                                $deliveryCharge === 0
                            ): ?>

                                <span class="free">
                                    FREE
                                </span>

                            <?php else: ?>

                                ৳<?php
                                echo number_format(
                                    $deliveryCharge,
                                    2
                                );
                                ?>

                            <?php endif; ?>

                        </strong>

                    </div>


                    <?php if (
                        $subtotal > 0 &&
                        $subtotal < 1000
                    ): ?>

                        <p class="delivery-note">

                            Add ৳<?php
                            echo number_format(
                                1000 - $subtotal,
                                2
                            );
                            ?>

                            more to get
                            <strong>
                                FREE delivery
                            </strong>.

                        </p>

                    <?php endif; ?>


                </div>



                <div class="summary-total">

                    <span>
                        Grand Total
                    </span>

                    <strong>

                        ৳<?php
                        echo number_format(
                            $grandTotal,
                            2
                        );
                        ?>

                    </strong>

                </div>



                <!-- CHECKOUT -->

                <a
                    href="checkout.php"
                    class="checkout-btn"
                >

                    Proceed to Checkout

                    <span>
                        →
                    </span>

                </a>


                <div class="secure-note">

                    <span>
                        🔒
                    </span>

                    Secure checkout
                    &nbsp;•&nbsp;
                    Safe shopping

                </div>


            </aside>


        </div>


    <?php endif; ?>


</main>



<!-- =========================================================
     FOOTER
========================================================= -->

<footer class="dashboard-footer">


    <div>

        <strong>
            🌾 নবান্ন
        </strong>

        <span>
            Fresh From Farmers
        </span>

    </div>


    <p>
        © <?php echo date("Y"); ?>
        Nobanno Agro-Farm
    </p>


</footer>



<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<script>

function changeQuantity(button, amount) {

    const form =
        button.closest(
            ".quantity-form"
        );

    const input =
        form.querySelector(
            ".quantity-input"
        );


    let value =
        parseFloat(
            input.value
        );


    const min =
        parseFloat(
            input.min
        ) || 0.01;


    const max =
        parseFloat(
            input.max
        );


    if (isNaN(value)) {

        value = min;
    }


    value += amount;


    if (value < min) {

        value = min;
    }


    if (
        !isNaN(max)
        &&
        value > max
    ) {

        value = max;
    }


    input.value =
        value.toFixed(2);
}

</script>


</body>

</html>