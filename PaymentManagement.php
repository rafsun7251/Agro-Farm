<?php

require_once __DIR__ . '/../admin_auth.php';

require_once __DIR__ . '/../db.php';



// ==========================================
// SUCCESS MESSAGE
// ==========================================

$message = "";

if (isset($_GET['updated']) && $_GET['updated'] == 1) {
    $message = "Payment status updated successfully.";
}


// ==========================================
// FETCH PAYMENTS
// ==========================================

$query = "
    SELECT

        p.payment_id,
        p.order_id,
        p.payment_method,
        p.payment_status,
        p.transaction_id,
        p.amount,
        p.paid_at,

        o.order_date,
        o.total_amount,
        o.status AS order_status,

        c.customer_id,

        u.first_name,
        u.last_name,
        u.email,
        u.phone

    FROM payments p

    INNER JOIN orders o
        ON p.order_id = o.order_id

    INNER JOIN customers c
        ON o.customer_id = c.customer_id

    INNER JOIN users u
        ON c.user_id = u.user_id

    ORDER BY p.payment_id DESC
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
        Agro-Farm | Payment Management
    </title>

    <link
        rel="stylesheet"
        href="../css/PaymentManagement.css"
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
                class="menu-item active"
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
             TOPBAR
        =========================================== -->

        <div class="topbar">


            <div class="page-title">

                <h1>
                    Payment Management
                </h1>

                <p>
                    Monitor customer payments and transactions
                </p>

            </div>


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

            <div class="message success">

                <?php

                echo htmlspecialchars(
                    $message
                );

                ?>

            </div>

        <?php } ?>



        <!-- ==========================================
             PAYMENT SUMMARY
        =========================================== -->

        <?php

        $totalPayments = 0;
        $paidPayments = 0;
        $pendingPayments = 0;
        $failedPayments = 0;

        mysqli_data_seek($result, 0);

        while (
            $paymentSummary =
            mysqli_fetch_assoc($result)
        ) {

            $totalPayments++;

            if (
                $paymentSummary[
                    'payment_status'
                ] === 'paid'
            ) {

                $paidPayments++;

            } elseif (
                $paymentSummary[
                    'payment_status'
                ] === 'pending'
            ) {

                $pendingPayments++;

            } elseif (
                $paymentSummary[
                    'payment_status'
                ] === 'failed'
            ) {

                $failedPayments++;

            }

        }

        mysqli_data_seek($result, 0);

        ?>


        <div class="summary-grid">


            <div class="summary-card">

                <div class="summary-icon total-icon">
                    💳
                </div>

                <div>

                    <span>
                        Total Payments
                    </span>

                    <strong>
                        <?php echo $totalPayments; ?>
                    </strong>

                </div>

            </div>


            <div class="summary-card">

                <div class="summary-icon paid-icon">
                    ✓
                </div>

                <div>

                    <span>
                        Paid
                    </span>

                    <strong>
                        <?php echo $paidPayments; ?>
                    </strong>

                </div>

            </div>


            <div class="summary-card">

                <div class="summary-icon pending-icon">
                    ⏳
                </div>

                <div>

                    <span>
                        Pending
                    </span>

                    <strong>
                        <?php echo $pendingPayments; ?>
                    </strong>

                </div>

            </div>


            <div class="summary-card">

                <div class="summary-icon failed-icon">
                    !
                </div>

                <div>

                    <span>
                        Failed
                    </span>

                    <strong>
                        <?php echo $failedPayments; ?>
                    </strong>

                </div>

            </div>


        </div>



        <!-- ==========================================
             FILTER CARD
        =========================================== -->

        <div class="filter-card">


            <div class="filter-header">

                <div>

                    <h3>
                        Payment List
                    </h3>

                    <p>
                        View and manage all payment transactions
                    </p>

                </div>

            </div>


            <div class="filter-row">


                <div class="search-box">

                    <input
                        type="text"
                        id="searchInput"
                        placeholder="Search order, customer or transaction..."
                    >

                  <button
    type="button"
    id="searchButton"
>
    Search
</button>

                </div>

<select id="statusFilter">

                    <option value="">
                        All Status
                    </option>

                    <option value="paid">
                        Paid
                    </option>

                    <option value="pending">
                        Pending
                    </option>

                    <option value="failed">
                        Failed
                    </option>

                </select>


              <select id="methodFilter">

                    <option value="">
                        All Methods
                    </option>

                    <option value="bkash">
                        bKash
                    </option>

                    <option value="nagad">
                        Nagad
                    </option>

                    <option value="cash on delivery">
                        Cash on Delivery
                    </option>

                    <option value="card">
                        Card
                    </option>

                </select>


            </div>


        </div>



        <!-- ==========================================
             PAYMENT TABLE
        =========================================== -->

        <div class="table-card">


            <div class="table-wrapper">


                <table id="paymentTable">


                    <thead>

                        <tr>

                            <th>
                                Payment ID
                            </th>

                            <th>
                                Order
                            </th>

                            <th>
                                Customer
                            </th>

                            <th>
                                Method
                            </th>

                            <th>
                                Transaction ID
                            </th>

                            <th>
                                Amount
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Paid At
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php

                    if (
                        mysqli_num_rows($result) > 0
                    ) {

                        while (
                            $payment =
                            mysqli_fetch_assoc($result)
                        ) {


                            $customerName =
                                $payment['first_name']
                                . " "
                                .
                                $payment['last_name'];


                            $statusText =
                                ucfirst(
                                    $payment[
                                        'payment_status'
                                    ]
                                );


                            $methodText =
                                ucwords(
                                    str_replace(
                                        "_",
                                        " ",
                                        $payment[
                                            'payment_method'
                                        ]
                                    )
                                );


                            $initial =
                                strtoupper(
                                    substr(
                                        $payment[
                                            'first_name'
                                        ],
                                        0,
                                        1
                                    )
                                );

                    ?>


                        <tr
                            class="payment-row"
                            data-status="<?php
                            echo htmlspecialchars(
                                $payment[
                                    'payment_status'
                                ]
                            );
                            ?>"
                            data-method="<?php
                            echo htmlspecialchars(
                                strtolower(
                                    $methodText
                                )
                            );
                            ?>"
                        >


                            <!-- PAYMENT ID -->

                            <td>

                                <strong class="payment-id">

                                    #<?php

                                    echo $payment[
                                        'payment_id'
                                    ];

                                    ?>

                                </strong>

                            </td>


                            <!-- ORDER -->

                            <td>

                                <div class="order-info">

                                    <strong>

                                        #<?php

                                        echo $payment[
                                            'order_id'
                                        ];

                                        ?>

                                    </strong>

                                    <small>

                                        ৳<?php

                                        echo number_format(
                                            $payment[
                                                'total_amount'
                                            ],
                                            2
                                        );

                                        ?>

                                    </small>

                                </div>

                            </td>


                            <!-- CUSTOMER -->

                            <td>

                                <div class="customer-info">


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

                                        <small>

                                            <?php

                                            echo !empty(
                                                $payment[
                                                    'phone'
                                                ]
                                            )
                                                ? htmlspecialchars(
                                                    $payment[
                                                        'phone'
                                                    ]
                                                )
                                                : "No phone";

                                            ?>

                                        </small>

                                    </div>


                                </div>

                            </td>


                            <!-- PAYMENT METHOD -->

                            <td>

                                <span class="payment-method">

                                    <?php

                                    echo htmlspecialchars(
                                        $methodText
                                    );

                                    ?>

                                </span>

                            </td>


                            <!-- TRANSACTION ID -->

                            <td>

                                <span class="transaction-id">

                                    <?php

                                    echo !empty(
                                        $payment[
                                            'transaction_id'
                                        ]
                                    )
                                        ? htmlspecialchars(
                                            $payment[
                                                'transaction_id'
                                            ]
                                        )
                                        : "—";

                                    ?>

                                </span>

                            </td>


                            <!-- AMOUNT -->

                            <td>

                                <strong class="amount">

                                    ৳<?php

                                    echo number_format(
                                        $payment[
                                            'amount'
                                        ],
                                        2
                                    );

                                    ?>

                                </strong>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <span
                                    class="status
                                    <?php

                                    echo htmlspecialchars(
                                        $payment[
                                            'payment_status'
                                        ]
                                    );

                                    ?>"
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $statusText
                                    );

                                    ?>

                                </span>

                            </td>


                            <!-- PAID AT -->

                            <td>

                                <?php

                                if (
                                    !empty(
                                        $payment[
                                            'paid_at'
                                        ]
                                    )
                                ) {

                                ?>

                                    <div class="date-info">

                                        <?php

                                        echo date(
                                            "d M Y",
                                            strtotime(
                                                $payment[
                                                    'paid_at'
                                                ]
                                            )
                                        );

                                        ?>

                                        <small>

                                            <?php

                                            echo date(
                                                "h:i A",
                                                strtotime(
                                                    $payment[
                                                        'paid_at'
                                                    ]
                                                )
                                            );

                                            ?>

                                        </small>

                                    </div>

                                <?php

                                } else {

                                    echo "—";

                                }

                                ?>

                            </td>


                            <!-- ACTION -->

                            <td>

                                <?php

                                if (
                                    $payment[
                                        'payment_status'
                                    ] === 'pending'
                                ) {

                                ?>

                                    <a
                                        href="UpdatePayment.php?id=<?php
                                        echo $payment[
                                            'payment_id'
                                        ];
                                        ?>"
                                        class="update-btn"
                                    >

                                        Update

                                    </a>

                                <?php

                                } else {

                                    echo '<span class="no-action">—</span>';

                                }

                                ?>

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

                                No payments found.

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

<script src="../controller/admin/AdminPayment.js"></script>


</body>

</html>