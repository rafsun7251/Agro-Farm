<?php

/* =========================================================
   DELIVERY MAN AUTHENTICATION
========================================================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$currentRole = strtolower(trim($_SESSION['role'] ?? ''));

if (
    $currentRole !== 'delivery_man' &&
    $currentRole !== 'delivery'
) {
    header("Location: ../login.php");
    exit();
}


/* =========================================================
   DATABASE
========================================================= */

require_once __DIR__ . '/../../db.php';


/* =========================================================
   GET DELIVERY ID
========================================================= */

$deliveryId = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($deliveryId <= 0) {
    header("Location: pending_deliveries.php");
    exit();
}


/* =========================================================
   LOGGED-IN USER
========================================================= */

$userId = (int) $_SESSION['user_id'];

$deliveryManId = null;
$deliveryManName = "Delivery Man";


/* =========================================================
   GET DELIVERY MAN
========================================================= */

$query = "
    SELECT
        dm.delivery_man_id,
        u.first_name,
        u.last_name

    FROM delivery_men dm

    INNER JOIN users u
        ON dm.user_id = u.user_id

    WHERE dm.user_id = ?

    LIMIT 1
";

$stmt = mysqli_prepare($conn, $query);

if ($stmt) {

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $userId
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (
        $result &&
        mysqli_num_rows($result) > 0
    ) {

        $deliveryMan = mysqli_fetch_assoc($result);

        $deliveryManId =
            (int) $deliveryMan['delivery_man_id'];

        $deliveryManName = trim(
            ($deliveryMan['first_name'] ?? '') .
            ' ' .
            ($deliveryMan['last_name'] ?? '')
        );

        if ($deliveryManName === '') {
            $deliveryManName = "Delivery Man";
        }
    }

    mysqli_stmt_close($stmt);
}


/* =========================================================
   DELIVERY MAN NOT FOUND
========================================================= */

if ($deliveryManId === null) {

    die("
        <div style='
            font-family:Arial;
            padding:50px;
            text-align:center;
        '>
            <h2>Delivery Man Profile Not Found</h2>
            <p>
                Your account is not connected to a delivery
                man profile.
            </p>
        </div>
    ");
}


/* =========================================================
   PROCESS STATUS UPDATE
========================================================= */

$message = "";
$messageType = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $newStatus =
        trim($_POST['delivery_status'] ?? '');

    /*
        Allowed delivery workflow
    */

    $allowedStatuses = [
        'assigned',
        'picked_up',
        'out_for_delivery',
        'delivered'
    ];

    if (!in_array(
        $newStatus,
        $allowedStatuses,
        true
    )) {

        $message =
            "Invalid delivery status.";

        $messageType = "error";

    } else {


        /* ================================================
           GET CURRENT STATUS
        ================================================= */

        $currentStatus = null;

        $statusQuery = "
            SELECT delivery_status

            FROM deliveries

            WHERE delivery_id = ?

            AND delivery_man_id = ?

            LIMIT 1
        ";

        $stmt = mysqli_prepare(
            $conn,
            $statusQuery
        );

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "ii",
                $deliveryId,
                $deliveryManId
            );

            mysqli_stmt_execute($stmt);

            $result =
                mysqli_stmt_get_result($stmt);

            if (
                $result &&
                mysqli_num_rows($result) > 0
            ) {

                $row =
                    mysqli_fetch_assoc($result);

                $currentStatus =
                    $row['delivery_status'];
            }

            mysqli_stmt_close($stmt);
        }


        /* ================================================
           DELIVERY NOT FOUND
        ================================================= */

        if ($currentStatus === null) {

            $message =
                "Delivery not found or it is not assigned to you.";

            $messageType = "error";

        } else {


            /* ============================================
               STATUS WORKFLOW VALIDATION
            ============================================= */

            $statusOrder = [
                'assigned' => 1,
                'picked_up' => 2,
                'out_for_delivery' => 3,
                'delivered' => 4
            ];

            $currentStep =
                $statusOrder[$currentStatus] ?? 0;

            $newStep =
                $statusOrder[$newStatus] ?? 0;


            /*
               Prevent skipping delivery stages.
            */

            if (
                $newStep !== $currentStep + 1 &&
                $newStatus !== $currentStatus
            ) {

                $message =
                    "Please update the delivery status step by step.";

                $messageType = "error";

            } else {


                /* ========================================
                   UPDATE DELIVERY
                ======================================== */

                if ($newStatus === 'delivered') {

                    $updateQuery = "
                        UPDATE deliveries

                        SET
                            delivery_status = ?,
                            delivered_at = NOW()

                        WHERE delivery_id = ?

                        AND delivery_man_id = ?
                    ";

                } else {

                    $updateQuery = "
                        UPDATE deliveries

                        SET
                            delivery_status = ?

                        WHERE delivery_id = ?

                        AND delivery_man_id = ?
                    ";
                }


                $stmt = mysqli_prepare(
                    $conn,
                    $updateQuery
                );

                if ($stmt) {

                    mysqli_stmt_bind_param(
                        $stmt,
                        "sii",
                        $newStatus,
                        $deliveryId,
                        $deliveryManId
                    );

                    if (
                        mysqli_stmt_execute($stmt)
                    ) {


                        /* =================================
                           UPDATE DELIVERY MAN AVAILABILITY
                        ================================= */

                        if (
                            $newStatus === 'delivered'
                        ) {

                            $availableStatus =
                                'available';

                            $availabilityQuery = "
                                UPDATE delivery_men

                                SET status = ?

                                WHERE delivery_man_id = ?
                            ";

                            $availabilityStmt =
                                mysqli_prepare(
                                    $conn,
                                    $availabilityQuery
                                );

                            if ($availabilityStmt) {

                                mysqli_stmt_bind_param(
                                    $availabilityStmt,
                                    "si",
                                    $availableStatus,
                                    $deliveryManId
                                );

                                mysqli_stmt_execute(
                                    $availabilityStmt
                                );

                                mysqli_stmt_close(
                                    $availabilityStmt
                                );
                            }
                        }


                        $message =
                            "Delivery status updated successfully.";

                        $messageType =
                            "success";

                    } else {

                        $message =
                            "Failed to update delivery status.";

                        $messageType =
                            "error";
                    }

                    mysqli_stmt_close($stmt);

                } else {

                    $message =
                        "Unable to prepare update query.";

                    $messageType =
                        "error";
                }
            }
        }
    }
}


/* =========================================================
   GET DELIVERY INFORMATION
========================================================= */

$delivery = null;

$query = "
    SELECT

        d.delivery_id,
        d.order_id,
        d.delivery_status,
        d.assigned_at,
        d.delivered_at,

        o.total_amount,
        o.delivery_address,
        o.order_date,

        customer_user.first_name
            AS customer_first_name,

        customer_user.last_name
            AS customer_last_name,

        customer_user.phone
            AS customer_phone

    FROM deliveries d

    INNER JOIN orders o
        ON d.order_id = o.order_id

    INNER JOIN customers c
        ON o.customer_id = c.customer_id

    INNER JOIN users customer_user
        ON c.user_id = customer_user.user_id

    WHERE d.delivery_id = ?

    AND d.delivery_man_id = ?

    LIMIT 1
";

$stmt = mysqli_prepare(
    $conn,
    $query
);

if ($stmt) {

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $deliveryId,
        $deliveryManId
    );

    mysqli_stmt_execute($stmt);

    $result =
        mysqli_stmt_get_result($stmt);

    if (
        $result &&
        mysqli_num_rows($result) > 0
    ) {

        $delivery =
            mysqli_fetch_assoc($result);
    }

    mysqli_stmt_close($stmt);
}


/* =========================================================
   DELIVERY NOT FOUND
========================================================= */

if (!$delivery) {

    die("
        <div style='
            font-family:Arial;
            padding:50px;
            text-align:center;
        '>

            <h2>Delivery Not Found</h2>

            <p>
                This delivery does not exist or
                is not assigned to you.
            </p>

            <a href='pending_deliveries.php'>
                Back to Pending Deliveries
            </a>

        </div>
    ");
}


/* =========================================================
   CUSTOMER
========================================================= */

$customerName = trim(
    ($delivery['customer_first_name'] ?? '') .
    ' ' .
    ($delivery['customer_last_name'] ?? '')
);

if ($customerName === '') {
    $customerName = "Customer";
}

$customerInitial =
    strtoupper(
        substr(
            $customerName,
            0,
            1
        )
    );


/* =========================================================
   STATUS
========================================================= */

$currentStatus =
    $delivery['delivery_status'] ?? 'assigned';


function statusLabel($status)
{
    switch ($status) {

        case 'assigned':
            return 'Assigned';

        case 'picked_up':
            return 'Picked Up';

        case 'out_for_delivery':
            return 'Out for Delivery';

        case 'delivered':
            return 'Delivered';

        default:
            return ucfirst(
                str_replace(
                    '_',
                    ' ',
                    $status
                )
            );
    }
}


function statusClass($status)
{
    switch ($status) {

        case 'assigned':
            return 'status-assigned';

        case 'picked_up':
            return 'status-picked';

        case 'out_for_delivery':
            return 'status-out';

        case 'delivered':
            return 'status-delivered';

        default:
            return 'status-assigned';
    }
}


/* =========================================================
   PROFILE INITIAL
========================================================= */

$nameParts = preg_split(
    '/\s+/',
    trim($deliveryManName)
);

$profileInitial =
    strtoupper(
        substr(
            $nameParts[0] ?? 'D',
            0,
            1
        )
    );


/* =========================================================
   NEXT STATUS
========================================================= */

$nextStatus = null;

switch ($currentStatus) {

    case 'assigned':
        $nextStatus = 'picked_up';
        break;

    case 'picked_up':
        $nextStatus = 'out_for_delivery';
        break;

    case 'out_for_delivery':
        $nextStatus = 'delivered';
        break;

    case 'delivered':
        $nextStatus = null;
        break;
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
        Nobanno | Manage Delivery
    </title>

    <link
        rel="stylesheet"
        href="../../css/DeliveryManDashboard/update_delivery.css"
    >

</head>


<body>


<!-- =====================================================
     HEADER
====================================================== -->

<header class="top-header">


    <div class="brand">

        <div class="brand-icon">
            🌾
        </div>

        <div>

            <h2>
                নবান্ন
            </h2>

            <span>
                Delivery Panel
            </span>

        </div>

    </div>


    <nav class="top-navigation">

        <a
            href="delivery_dashboard.php"
            class="nav-link"
        >
            Dashboard
        </a>


        <a
            href="pending_deliveries.php"
            class="nav-link active"
        >
            Pending Deliveries
        </a>


        <a
            href="delivery_history.php"
            class="nav-link"
        >
            Delivery History
        </a>

    </nav>


    <div class="profile-area">

        <div class="profile-avatar">

            <?php
            echo htmlspecialchars(
                $profileInitial
            );
            ?>

        </div>


        <div class="profile-text">

            <strong>

                <?php
                echo htmlspecialchars(
                    $deliveryManName
                );
                ?>

            </strong>

            <span>
                Delivery Man
            </span>

        </div>


        <a
            href="../../logout.php"
            class="logout-button"
        >
            Logout
        </a>

    </div>

</header>



<!-- =====================================================
     MAIN
====================================================== -->

<main class="main-container">


    <!-- PAGE HEADER -->

    <section class="page-header">

        <div>

            <p class="eyebrow">
                DELIVERY MANAGEMENT
            </p>

            <h1>
                Manage Delivery
            </h1>

            <p class="page-description">

                Update the delivery progress for
                Order #<?php
                echo htmlspecialchars(
                    $delivery['order_id']
                );
                ?>

            </p>

        </div>


        <a
            href="pending_deliveries.php"
            class="back-button"
        >
            ← Back
        </a>

    </section>



    <!-- =================================================
         MESSAGE
    ================================================== -->

    <?php if ($message !== ""): ?>

        <div
            class="message
            <?php
            echo htmlspecialchars(
                $messageType
            );
            ?>"
        >

            <span>

                <?php
                echo $messageType === 'success'
                    ? '✓'
                    : '!';
                ?>

            </span>


            <?php
            echo htmlspecialchars(
                $message
            );
            ?>

        </div>

    <?php endif; ?>



    <!-- =================================================
         DELIVERY INFORMATION
    ================================================== -->

    <section class="delivery-layout">


        <!-- LEFT -->

        <div class="main-card">


            <div class="card-header">

                <div>

                    <span class="card-label">
                        ORDER
                    </span>

                    <h2>

                        #<?php
                        echo htmlspecialchars(
                            $delivery['order_id']
                        );
                        ?>

                    </h2>

                </div>


                <span
                    class="status
                    <?php
                    echo statusClass(
                        $currentStatus
                    );
                    ?>"
                >

                    <span
                        class="status-dot"
                    ></span>

                    <?php
                    echo htmlspecialchars(
                        statusLabel(
                            $currentStatus
                        )
                    );
                    ?>

                </span>

            </div>



            <!-- CUSTOMER -->

            <div class="information-section">

                <h3>
                    Customer Information
                </h3>


                <div class="customer-box">


                    <div class="customer-avatar">

                        <?php
                        echo htmlspecialchars(
                            $customerInitial
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


                        <span>

                            <?php
                            echo htmlspecialchars(
                                $delivery[
                                    'customer_phone'
                                ] ?? 'No phone number'
                            );
                            ?>

                        </span>

                    </div>

                </div>

            </div>



            <!-- ADDRESS -->

            <div class="information-section">

                <h3>
                    Delivery Address
                </h3>


                <div class="address-box">

                    <span class="location-icon">
                        📍
                    </span>


                    <p>

                        <?php
                        echo nl2br(
                            htmlspecialchars(
                                $delivery[
                                    'delivery_address'
                                ] ?? 'Address unavailable'
                            )
                        );
                        ?>

                    </p>

                </div>

            </div>



            <!-- ORDER DETAILS -->

            <div class="information-section">

                <h3>
                    Order Information
                </h3>


                <div class="order-details">


                    <div>

                        <span>
                            Order Date
                        </span>

                        <strong>

                            <?php

                            if (
                                !empty(
                                    $delivery[
                                        'order_date'
                                    ]
                                )
                            ) {

                                echo date(
                                    'd M Y, h:i A',
                                    strtotime(
                                        $delivery[
                                            'order_date'
                                        ]
                                    )
                                );

                            } else {

                                echo 'N/A';

                            }

                            ?>

                        </strong>

                    </div>


                    <div>

                        <span>
                            Order Amount
                        </span>

                        <strong class="amount">

                            ৳<?php

                            echo number_format(
                                (float) (
                                    $delivery[
                                        'total_amount'
                                    ] ?? 0
                                ),
                                2
                            );

                            ?>

                        </strong>

                    </div>

                </div>

            </div>

        </div>



        <!-- RIGHT -->

        <aside class="side-card">


            <div class="side-header">

                <span class="card-label">
                    DELIVERY PROGRESS
                </span>

                <h2>
                    Update Status
                </h2>

            </div>



            <!-- PROGRESS -->

            <div class="progress">


                <div
                    class="progress-step
                    <?php
                    echo $currentStatus === 'assigned'
                        || $currentStatus === 'picked_up'
                        || $currentStatus === 'out_for_delivery'
                        || $currentStatus === 'delivered'
                        ? 'completed'
                        : '';
                    ?>"
                >

                    <span class="step-circle">
                        1
                    </span>

                    <div>

                        <strong>
                            Assigned
                        </strong>

                        <small>
                            Delivery assigned
                        </small>

                    </div>

                </div>



                <div
                    class="progress-step
                    <?php
                    echo in_array(
                        $currentStatus,
                        [
                            'picked_up',
                            'out_for_delivery',
                            'delivered'
                        ],
                        true
                    )
                        ? 'completed'
                        : '';
                    ?>"
                >

                    <span class="step-circle">
                        2
                    </span>

                    <div>

                        <strong>
                            Picked Up
                        </strong>

                        <small>
                            Order collected
                        </small>

                    </div>

                </div>



                <div
                    class="progress-step
                    <?php
                    echo in_array(
                        $currentStatus,
                        [
                            'out_for_delivery',
                            'delivered'
                        ],
                        true
                    )
                        ? 'completed'
                        : '';
                    ?>"
                >

                    <span class="step-circle">
                        3
                    </span>

                    <div>

                        <strong>
                            Out for Delivery
                        </strong>

                        <small>
                            On the way
                        </small>

                    </div>

                </div>



                <div
                    class="progress-step
                    <?php
                    echo $currentStatus === 'delivered'
                        ? 'completed'
                        : '';
                    ?>"
                >

                    <span class="step-circle">
                        4
                    </span>

                    <div>

                        <strong>
                            Delivered
                        </strong>

                        <small>
                            Successfully delivered
                        </small>

                    </div>

                </div>

            </div>



            <!-- UPDATE -->

            <?php if ($nextStatus !== null): ?>

                <form
                    method="POST"
                    class="status-form"
                >

                    <input
                        type="hidden"
                        name="delivery_status"
                        value="<?php
                        echo htmlspecialchars(
                            $nextStatus
                        );
                        ?>"
                    >


                    <p class="next-label">
                        Next Step
                    </p>


                    <div class="next-status">

                        <span class="next-icon">
                            →
                        </span>


                        <div>

                            <strong>

                                <?php
                                echo htmlspecialchars(
                                    statusLabel(
                                        $nextStatus
                                    )
                                );
                                ?>

                            </strong>


                            <small>
                                Update delivery to this stage
                            </small>

                        </div>

                    </div>


                    <button
                        type="submit"
                        class="update-status-button"
                    >

                        Mark as
                        <?php
                        echo htmlspecialchars(
                            statusLabel(
                                $nextStatus
                            )
                        );
                        ?>

                        →

                    </button>

                </form>


            <?php else: ?>


                <div class="completed-box">

                    <div class="completed-icon">
                        ✓
                    </div>


                    <h3>
                        Delivery Completed
                    </h3>


                    <p>

                        This order has been successfully
                        delivered.

                    </p>


                    <?php if (
                        !empty(
                            $delivery['delivered_at']
                        )
                    ): ?>

                        <small>

                            Delivered on

                            <?php
                            echo date(
                                'd M Y, h:i A',
                                strtotime(
                                    $delivery[
                                        'delivered_at'
                                    ]
                                )
                            );
                            ?>

                        </small>

                    <?php endif; ?>


                    <a
                        href="delivery_history.php"
                        class="history-button"
                    >
                        View Delivery History
                    </a>

                </div>


            <?php endif; ?>


        </aside>

    </section>

</main>



<!-- =====================================================
     FOOTER
====================================================== -->

<footer class="footer">

    <p>

        © <?php
        echo date('Y');
        ?>

        নবান্ন — Fresh From Farmers

    </p>


    <p>
        Delivery Management System
    </p>

</footer>


</body>

</html>