<?php

session_start();

require_once __DIR__ . '/../../db.php';

/* 
   LOGIN CHECK
 */

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (
    !isset($_SESSION['role']) ||
    strtolower(trim($_SESSION['role'])) !== 'customer'
) {
    header("Location: ../login.php");
    exit();
}

$userId = (int) $_SESSION['user_id'];

$successMessage = '';
$errorMessage = '';

/* 
   GET CUSTOMER DATA
 */

$sql = "
    SELECT
        u.user_id,
        u.first_name,
        u.last_name,
        u.email,
        u.phone,
        c.customer_id,
        c.address
    FROM users u
    INNER JOIN customers c
        ON u.user_id = c.user_id
    WHERE u.user_id = ?
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$customer = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$customer) {
    die("Customer profile not found.");
}

/* 
   UPDATE PROFILE
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $address   = trim($_POST['address'] ?? '');

    if ($firstName === '') {

        $errorMessage = "First name is required.";

    } else {

        /* Update users table */

        $updateUser = "
            UPDATE users
            SET
                first_name = ?,
                last_name = ?,
                phone = ?
            WHERE user_id = ?
        ";

        $stmt = mysqli_prepare($conn, $updateUser);

        mysqli_stmt_bind_param(
            $stmt,
            "sssi",
            $firstName,
            $lastName,
            $phone,
            $userId
        );

        if (mysqli_stmt_execute($stmt)) {

            mysqli_stmt_close($stmt);

            /* Update customers table */

            $updateCustomer = "
                UPDATE customers
                SET address = ?
                WHERE user_id = ?
            ";

            $stmt = mysqli_prepare(
                $conn,
                $updateCustomer
            );

            mysqli_stmt_bind_param(
                $stmt,
                "si",
                $address,
                $userId
            );

            mysqli_stmt_execute($stmt);

            mysqli_stmt_close($stmt);

            /* Update session */

            $_SESSION['first_name'] = $firstName;
            $_SESSION['last_name'] = $lastName;
            $_SESSION['name'] =
                trim($firstName . ' ' . $lastName);

            $_SESSION['phone'] = $phone;
            $_SESSION['address'] = $address;

            $successMessage =
                "Profile updated successfully.";

            /* Update displayed values */

            $customer['first_name'] = $firstName;
            $customer['last_name'] = $lastName;
            $customer['phone'] = $phone;
            $customer['address'] = $address;

        } else {

            mysqli_stmt_close($stmt);

            $errorMessage =
                "Unable to update profile.";
        }
    }
}

/* 
   DISPLAY DATA
 */

$fullName = trim(
    $customer['first_name'] . ' ' .
    $customer['last_name']
);

$initials = strtoupper(
    substr($customer['first_name'], 0, 1) .
    substr($customer['last_name'], 0, 1)
);

if ($initials === '') {
    $initials = 'CU';
}

function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
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

    <title>Nobanno - Customer Profile</title>

    <link
        rel="stylesheet"
        href="../../css/CustomerDashboard/profile.css"
    >

</head>

<body>

<div class="profile-page">

    <!-- =========================
         HEADER
    ========================== -->

    <header class="profile-header">

        <div class="profile-header-left">

            <a
                href="customer_dashboard.php"
                class="brand"
            >
                <span class="brand-icon">🌾</span>
                <span>নবান্ন</span>
            </a>

            <div class="page-title">

                <span>CUSTOMER DASHBOARD</span>

                <h1>My Profile</h1>

            </div>

        </div>


        <nav class="profile-navigation">

            <a
                href="customer_dashboard.php"
                class="nav-link"
            >
                Dashboard
            </a>

            <a
                href="crops.php"
                class="nav-link"
            >
                Crops
            </a>

            <a
                href="cart.php"
                class="nav-link"
            >
                Cart
            </a>

            <a
                href="orders.php"
                class="nav-link"
            >
                Orders
            </a>

            <a
                href="profile.php"
                class="nav-link active"
            >
                Profile
            </a>

            <a
                href="../../logout.php"
                class="logout-btn"
            >
                Logout
            </a>

        </nav>

    </header>


    <!-- =========================
         MAIN
    ========================== -->

    <main class="profile-container">

        <section class="profile-intro">

            <p class="small-label">
                ACCOUNT SETTINGS
            </p>

            <h2>
                My Profile
            </h2>

            <p>
                View and update your customer account information.
            </p>

        </section>


        <!-- =========================
             MESSAGES
        ========================== -->

        <?php if ($successMessage !== ''): ?>

            <div class="message success-message">
                <?php echo e($successMessage); ?>
            </div>

        <?php endif; ?>


        <?php if ($errorMessage !== ''): ?>

            <div class="message error-message">
                <?php echo e($errorMessage); ?>
            </div>

        <?php endif; ?>


        <section class="profile-layout">

            <!-- =========================
                 PROFILE CARD
            ========================== -->

            <div class="profile-card">

                <div class="profile-card-top">

                    <div class="profile-avatar">

                        <?php echo e($initials); ?>

                    </div>

                    <div class="profile-name-area">

                        <h3>
                            <?php echo e($fullName); ?>
                        </h3>

                        <span class="customer-badge">
                            Customer
                        </span>

                        <p>
                            Nobanno Customer Account
                        </p>

                    </div>

                </div>


                <!-- =========================
                     PROFILE DETAILS
                ========================== -->

                <div class="profile-details">

                    <div class="detail-row">

                        <div class="detail-icon">
                            👤
                        </div>

                        <div class="detail-content">

                            <span>Full Name</span>

                            <strong>
                                <?php echo e($fullName); ?>
                            </strong>

                        </div>

                    </div>


                    <div class="detail-row">

                        <div class="detail-icon">
                            ✉
                        </div>

                        <div class="detail-content">

                            <span>Email Address</span>

                            <strong>
                                <?php echo e($customer['email']); ?>
                            </strong>

                        </div>

                    </div>


                    <div class="detail-row">

                        <div class="detail-icon">
                            ☎
                        </div>

                        <div class="detail-content">

                            <span>Phone Number</span>

                            <strong>
                                <?php
                                echo e(
                                    $customer['phone']
                                    ?: 'Not provided'
                                );
                                ?>
                            </strong>

                        </div>

                    </div>


                    <div class="detail-row">

                        <div class="detail-icon">
                            📍
                        </div>

                        <div class="detail-content">

                            <span>Address</span>

                            <strong>
                                <?php
                                echo e(
                                    $customer['address']
                                    ?: 'Not provided'
                                );
                                ?>
                            </strong>

                        </div>

                    </div>


                    <div class="detail-row">

                        <div class="detail-icon">
                            #
                        </div>

                        <div class="detail-content">

                            <span>Customer ID</span>

                            <strong>
                                <?php
                                echo e(
                                    $customer['customer_id']
                                );
                                ?>
                            </strong>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =========================
                 EDIT PROFILE
            ========================== -->

            <div class="account-side">

                <div class="side-card edit-card">

                    <div class="side-card-heading">

                        <div class="side-icon">
                            ✎
                        </div>

                        <div>

                            <h3>
                                Edit Profile
                            </h3>

                            <span>
                                Update your information
                            </span>

                        </div>

                    </div>


                    <form
                        method="POST"
                        action="profile.php"
                        class="profile-form"
                    >

                        <div class="form-group">

                            <label for="first_name">
                                First Name
                            </label>

                            <input
                                type="text"
                                id="first_name"
                                name="first_name"
                                value="<?php echo e($customer['first_name']); ?>"
                                required
                            >

                        </div>


                        <div class="form-group">

                            <label for="last_name">
                                Last Name
                            </label>

                            <input
                                type="text"
                                id="last_name"
                                name="last_name"
                                value="<?php echo e($customer['last_name']); ?>"
                            >

                        </div>


                        <div class="form-group">

                            <label for="email">
                                Email Address
                            </label>

                            <input
                                type="email"
                                id="email"
                                value="<?php echo e($customer['email']); ?>"
                                disabled
                            >

                            <small>
                                Email cannot be changed here.
                            </small>

                        </div>


                        <div class="form-group">

                            <label for="phone">
                                Phone Number
                            </label>

                            <input
                                type="text"
                                id="phone"
                                name="phone"
                                value="<?php echo e($customer['phone']); ?>"
                            >

                        </div>


                        <div class="form-group">

                            <label for="address">
                                Address
                            </label>

                            <textarea
                                id="address"
                                name="address"
                                rows="4"
                            ><?php echo e($customer['address']); ?></textarea>

                        </div>


                        <button
                            type="submit"
                            class="save-btn"
                        >
                            Save Changes
                        </button>

                    </form>

                </div>


                <div class="side-card">

                    <div class="side-card-heading">

                        <div class="side-icon">
                            ✓
                        </div>

                        <div>

                            <h3>
                                Account Status
                            </h3>

                            <span>
                                Nobanno customer account
                            </span>

                        </div>

                    </div>


                    <div class="account-status">

                        <span class="status-dot"></span>

                        <strong>
                            Active
                        </strong>

                    </div>

                    <p>
                        Your customer account is active
                        and ready for shopping.
                    </p>

                </div>

            </div>

        </section>

    </main>


    <footer class="profile-footer">

        <span>
            © <?php echo date('Y'); ?> Nobanno Agro-Farm
        </span>

        <span>
            Fresh Products • Trusted Farmers • Easy Shopping
        </span>

    </footer>

</div>

</body>

</html>