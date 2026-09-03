<?php

require_once __DIR__ . '/../admin_auth.php';

require_once __DIR__ . '/../db.php';

$adminId = (int) $_SESSION['user_id'];
// ==========================================
// ADMIN ID
// ==========================================

// For now, we use the first admin account.
// Later, when login/session is implemented,
// this will come from $_SESSION['user_id'].

$adminQuery = "
    SELECT *
    FROM users
    WHERE role = 'admin'
    ORDER BY user_id ASC
    LIMIT 1
";

$adminResult = mysqli_query($conn, $adminQuery);

if (!$adminResult || mysqli_num_rows($adminResult) === 0) {
    die("No admin account found.");
}

$admin = mysqli_fetch_assoc($adminResult);

$adminId = (int) $admin['user_id'];


// ==========================================
// MESSAGES
// ==========================================

$successMessage = "";
$errorMessage = "";


// ==========================================
// UPDATE PROFILE
// ==========================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    &&
    isset($_POST['update_profile'])
) {

    $firstName =
        trim($_POST['first_name'] ?? "");

    $lastName =
        trim($_POST['last_name'] ?? "");

    $email =
        trim($_POST['email'] ?? "");

    $phone =
        trim($_POST['phone'] ?? "");


    if (
        $firstName === ""
        ||
        $lastName === ""
        ||
        $email === ""
    ) {

        $errorMessage =
            "First name, last name and email are required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errorMessage =
            "Please enter a valid email address.";

    } else {


        // Check whether another user
        // already uses this email.

        $emailCheckQuery = "
            SELECT user_id
            FROM users
            WHERE email = ?
            AND user_id != ?
            LIMIT 1
        ";

        $emailStmt =
            mysqli_prepare(
                $conn,
                $emailCheckQuery
            );

        mysqli_stmt_bind_param(
            $emailStmt,
            "si",
            $email,
            $adminId
        );

        mysqli_stmt_execute(
            $emailStmt
        );

        $emailResult =
            mysqli_stmt_get_result(
                $emailStmt
            );


        if (
            mysqli_num_rows(
                $emailResult
            ) > 0
        ) {

            $errorMessage =
                "This email is already being used by another user.";

        } else {


            $updateQuery = "
                UPDATE users

                SET
                    first_name = ?,
                    last_name = ?,
                    email = ?,
                    phone = ?

                WHERE user_id = ?
                AND role = 'admin'
            ";


            $updateStmt =
                mysqli_prepare(
                    $conn,
                    $updateQuery
                );


            mysqli_stmt_bind_param(
                $updateStmt,
                "ssssi",
                $firstName,
                $lastName,
                $email,
                $phone,
                $adminId
            );


            if (
                mysqli_stmt_execute(
                    $updateStmt
                )
            ) {

                $successMessage =
                    "Profile updated successfully.";

                // Refresh admin information.

                $adminQuery = "
                    SELECT *
                    FROM users
                    WHERE user_id = ?
                    AND role = 'admin'
                    LIMIT 1
                ";

                $refreshStmt =
                    mysqli_prepare(
                        $conn,
                        $adminQuery
                    );

                mysqli_stmt_bind_param(
                    $refreshStmt,
                    "i",
                    $adminId
                );

                mysqli_stmt_execute(
                    $refreshStmt
                );

                $refreshResult =
                    mysqli_stmt_get_result(
                        $refreshStmt
                    );

                $admin =
                    mysqli_fetch_assoc(
                        $refreshResult
                    );

            } else {

                $errorMessage =
                    "Failed to update profile.";

            }

        }

    }

}


// ==========================================
// CHANGE PASSWORD
// ==========================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    &&
    isset($_POST['change_password'])
) {

    $currentPassword =
        $_POST['current_password'] ?? "";

    $newPassword =
        $_POST['new_password'] ?? "";

    $confirmPassword =
        $_POST['confirm_password'] ?? "";


    if (
        $currentPassword === ""
        ||
        $newPassword === ""
        ||
        $confirmPassword === ""
    ) {

        $errorMessage =
            "All password fields are required.";

    } elseif (
        strlen($newPassword) < 6
    ) {

        $errorMessage =
            "New password must be at least 6 characters.";

    } elseif (
        $newPassword !== $confirmPassword
    ) {

        $errorMessage =
            "New password and confirm password do not match.";

    } elseif (
        !password_verify(
            $currentPassword,
            $admin['password']
        )
    ) {

        $errorMessage =
            "Current password is incorrect.";

    } else {


        $hashedPassword =
            password_hash(
                $newPassword,
                PASSWORD_DEFAULT
            );


        $passwordQuery = "
            UPDATE users

            SET password = ?

            WHERE user_id = ?
            AND role = 'admin'
        ";


        $passwordStmt =
            mysqli_prepare(
                $conn,
                $passwordQuery
            );


        mysqli_stmt_bind_param(
            $passwordStmt,
            "si",
            $hashedPassword,
            $adminId
        );


        if (
            mysqli_stmt_execute(
                $passwordStmt
            )
        ) {

            $successMessage =
                "Password changed successfully.";

        } else {

            $errorMessage =
                "Failed to change password.";

        }

    }

}


// ==========================================
// ADMIN DISPLAY DATA
// ==========================================

$adminFullName =
    $admin['first_name']
    . " "
    .
    $admin['last_name'];


$adminInitial =
    strtoupper(
        substr(
            $admin['first_name'],
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
        Agro-Farm | Admin Settings
    </title>

    <link
        rel="stylesheet"
        href="../css/AdminSettings.css"
    >

</head>


<body>


<div class="dashboard">


    <!-- ==========================================
         SIDEBAR
    =========================================== -->

    <aside class="sidebar">


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
                class="menu-item"
            >

                <span class="menu-icon">
                    🛒
                </span>

                Orders

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
                class="menu-item active"
            >

                <span class="menu-icon">
                    ⚙
                </span>

                Settings

            </a>


        </nav>


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


        <!-- TOPBAR -->

        <div class="topbar">


            <div class="page-title">

                <h1>
                    Admin Settings
                </h1>

                <p>
                    Manage your administrator profile and account
                </p>

            </div>


            <div class="admin-profile">


                <div class="profile-icon">

                    <?php

                    echo htmlspecialchars(
                        $adminInitial
                    );

                    ?>

                </div>


                <div class="profile-info">

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $adminFullName
                        );

                        ?>

                    </strong>

                    <small>
                        Administrator
                    </small>

                </div>


            </div>


        </div>



        <!-- ==========================================
             MESSAGES
        =========================================== -->

        <?php if (!empty($successMessage)) { ?>

            <div class="message success">

                ✓

                <?php

                echo htmlspecialchars(
                    $successMessage
                );

                ?>

            </div>

        <?php } ?>


        <?php if (!empty($errorMessage)) { ?>

            <div class="message error">

                !

                <?php

                echo htmlspecialchars(
                    $errorMessage
                );

                ?>

            </div>

        <?php } ?>



        <!-- ==========================================
             PROFILE HEADER CARD
        =========================================== -->

        <div class="profile-card">


            <div class="large-avatar">

                <?php

                echo htmlspecialchars(
                    $adminInitial
                );

                ?>

            </div>


            <div class="profile-card-info">

                <h2>

                    <?php

                    echo htmlspecialchars(
                        $adminFullName
                    );

                    ?>

                </h2>

                <p>
                    Administrator
                </p>

                <span>
                    Account ID:
                    #<?php
                    echo $adminId;
                    ?>
                </span>

            </div>


        </div>



        <!-- ==========================================
             PROFILE INFORMATION
        =========================================== -->

        <div class="settings-card">


            <div class="card-header">

                <div>

                    <h3>
                        Profile Information
                    </h3>

                    <p>
                        Update your personal information
                    </p>

                </div>

            </div>


            <form
                method="POST"
                action=""
            >


                <input
                    type="hidden"
                    name="update_profile"
                    value="1"
                >


                <div class="form-grid">


                    <!-- FIRST NAME -->

                    <div class="form-group">

                        <label
                            for="first_name"
                        >
                            First Name
                        </label>

                        <input
                            type="text"
                            id="first_name"
                            name="first_name"
                            value="<?php

                            echo htmlspecialchars(
                                $admin['first_name']
                            );

                            ?>"
                            maxlength="50"
                            required
                        >

                    </div>


                    <!-- LAST NAME -->

                    <div class="form-group">

                        <label
                            for="last_name"
                        >
                            Last Name
                        </label>

                        <input
                            type="text"
                            id="last_name"
                            name="last_name"
                            value="<?php

                            echo htmlspecialchars(
                                $admin['last_name']
                            );

                            ?>"
                            maxlength="50"
                            required
                        >

                    </div>


                    <!-- EMAIL -->

                    <div class="form-group">

                        <label
                            for="email"
                        >
                            Email Address
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?php

                            echo htmlspecialchars(
                                $admin['email']
                            );

                            ?>"
                            maxlength="100"
                            required
                        >

                    </div>


                    <!-- PHONE -->

                    <div class="form-group">

                        <label
                            for="phone"
                        >
                            Phone Number
                        </label>

                        <input
                            type="text"
                            id="phone"
                            name="phone"
                            value="<?php

                            echo htmlspecialchars(
                                $admin['phone'] ?? ""
                            );

                            ?>"
                            maxlength="20"
                            placeholder="Enter phone number"
                        >

                    </div>


                </div>


                <div class="form-actions">

                    <button
                        type="submit"
                        class="save-btn"
                    >
                        Save Changes
                    </button>

                </div>


            </form>


        </div>



        <!-- ==========================================
             CHANGE PASSWORD
        =========================================== -->

        <div class="settings-card">


            <div class="card-header">

                <div>

                    <h3>
                        Change Password
                    </h3>

                    <p>
                        Update your administrator account password
                    </p>

                </div>

            </div>


            <form
                method="POST"
                action=""
            >


                <input
                    type="hidden"
                    name="change_password"
                    value="1"
                >


                <div class="password-grid">


                    <!-- CURRENT PASSWORD -->

                    <div class="form-group">

                        <label
                            for="current_password"
                        >
                            Current Password
                        </label>

                        <input
                            type="password"
                            id="current_password"
                            name="current_password"
                            placeholder="Enter current password"
                            required
                        >

                    </div>


                    <!-- NEW PASSWORD -->

                    <div class="form-group">

                        <label
                            for="new_password"
                        >
                            New Password
                        </label>

                        <input
                            type="password"
                            id="new_password"
                            name="new_password"
                            placeholder="Minimum 6 characters"
                            minlength="6"
                            required
                        >

                    </div>


                    <!-- CONFIRM PASSWORD -->

                    <div class="form-group">

                        <label
                            for="confirm_password"
                        >
                            Confirm New Password
                        </label>

                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            placeholder="Re-enter new password"
                            minlength="6"
                            required
                        >

                    </div>


                </div>


                <div class="form-actions">

                    <button
                        type="submit"
                        class="save-btn"
                    >
                        Change Password
                    </button>

                </div>


            </form>


        </div>



        <!-- ==========================================
             ACCOUNT INFORMATION
        =========================================== -->

        <div class="settings-card">


            <div class="card-header">

                <div>

                    <h3>
                        Account Information
                    </h3>

                    <p>
                        Basic information about this administrator account
                    </p>

                </div>

            </div>


            <div class="account-grid">


                <div class="account-item">

                    <span>
                        User ID
                    </span>

                    <strong>
                        #<?php
                        echo $adminId;
                        ?>
                    </strong>

                </div>


                <div class="account-item">

                    <span>
                        Role
                    </span>

                    <strong class="role-badge">
                        Administrator
                    </strong>

                </div>


                <div class="account-item">

                    <span>
                        Account Created
                    </span>

                    <strong>

                        <?php

                        echo date(
                            "d M Y",
                            strtotime(
                                $admin['created_at']
                            )
                        );

                        ?>

                    </strong>

                </div>


            </div>


        </div>


    </main>


</div>


</body>

</html>