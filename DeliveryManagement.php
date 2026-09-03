<?php

require_once __DIR__ . '/../admin_auth.php';

require_once __DIR__ . '/../db.php';



// ==========================================
// SUCCESS / ERROR MESSAGE
// ==========================================

$message = "";

$messageType = "success";


if (isset($_GET['assigned']) && $_GET['assigned'] == 1) {

    $message = "Delivery man assigned successfully.";

}


if (isset($_GET['updated']) && $_GET['updated'] == 1) {

    $message = "Delivery status updated successfully.";

}


if (isset($_GET['deleted']) && $_GET['deleted'] == 1) {

    $message = "Delivery assignment removed successfully.";

}


// ==========================================
// FETCH DELIVERIES
// ==========================================

$query = "
    SELECT

        d.delivery_id,
        d.order_id,
        d.delivery_status,
        d.assigned_at,
        d.delivered_at,

        o.order_date,
        o.total_amount,
        o.delivery_address,
        o.status AS order_status,

        c.customer_id,

        customer_user.first_name AS customer_first_name,
        customer_user.last_name AS customer_last_name,
        customer_user.phone AS customer_phone,

        d.delivery_man_id,

        delivery_user.first_name AS delivery_first_name,
        delivery_user.last_name AS delivery_last_name,
        delivery_user.phone AS delivery_phone,

        dm.vehicle_type,
        dm.vehicle_number

    FROM deliveries d

    INNER JOIN orders o
        ON d.order_id = o.order_id

    INNER JOIN customers c
        ON o.customer_id = c.customer_id

    INNER JOIN users customer_user
        ON c.user_id = customer_user.user_id

    LEFT JOIN delivery_men dm
        ON d.delivery_man_id = dm.delivery_man_id

    LEFT JOIN users delivery_user
        ON dm.user_id = delivery_user.user_id

    ORDER BY d.delivery_id DESC
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
        Agro-Farm | Delivery Management
    </title>

    <link
        rel="stylesheet"
        href="../css/DeliveryManagement.css"
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
                class="menu-item active"
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
             TOPBAR
        =========================================== -->

        <div class="topbar">


            <div class="page-title">

                <h1>
                    Delivery Management
                </h1>

                <p>
                    Manage delivery assignments and delivery status
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
             MESSAGE
        =========================================== -->

        <?php if (!empty($message)) { ?>

            <div
                class="message
                <?php
                echo htmlspecialchars(
                    $messageType
                );
                ?>"
            >

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
                        Delivery List
                    </h3>

                    <p>
                        Track orders and delivery assignments
                    </p>

                </div>

            </div>


            <div class="filter-row">


                <!-- SEARCH -->

                <div class="search-box">

                    <input
                        type="text"
                        id="searchInput"
                        placeholder="Search order, customer or delivery man..."
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

                    <option value="not assigned">
                        Not Assigned
                    </option>

                    <option value="assigned">
                        Assigned
                    </option>

                    <option value="picked up">
                        Picked Up
                    </option>

                    <option value="out for delivery">
                        Out for Delivery
                    </option>

                    <option value="delivered">
                        Delivered
                    </option>

                </select>


            </div>


        </div>



        <!-- ==========================================
             DELIVERY TABLE
        =========================================== -->

        <div class="table-card">


            <div class="table-wrapper">


                <table id="deliveryTable">


                    <thead>

                        <tr>

                            <th>
                                Delivery ID
                            </th>

                            <th>
                                Order
                            </th>

                            <th>
                                Customer
                            </th>

                            <th>
                                Delivery Man
                            </th>

                            <th>
                                Vehicle
                            </th>

                            <th>
                                Address
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Assigned At
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
                            $delivery =
                            mysqli_fetch_assoc($result)
                        ) {


                            // ==================================
                            // CUSTOMER NAME
                            // ==================================

                            $customerName =
                                $delivery[
                                    'customer_first_name'
                                ]
                                . " "
                                .
                                $delivery[
                                    'customer_last_name'
                                ];


                            // ==================================
                            // DELIVERY MAN NAME
                            // ==================================

                            if (
                                !empty(
                                    $delivery[
                                        'delivery_first_name'
                                    ]
                                )
                            ) {

                                $deliveryManName =
                                    $delivery[
                                        'delivery_first_name'
                                    ]
                                    . " "
                                    .
                                    $delivery[
                                        'delivery_last_name'
                                    ];

                            } else {

                                $deliveryManName =
                                    "Not Assigned";

                            }


                            // ==================================
                            // STATUS
                            // ==================================

                            $statusText =
                                ucwords(
                                    str_replace(
                                        "_",
                                        " ",
                                        $delivery[
                                            'delivery_status'
                                        ]
                                    )
                                );


                            $statusClass =
                                str_replace(
                                    "_",
                                    "-",
                                    $delivery[
                                        'delivery_status'
                                    ]
                                );


                            // ==================================
                            // CUSTOMER INITIAL
                            // ==================================

                            $customerInitial =
                                strtoupper(
                                    substr(
                                        $delivery[
                                            'customer_first_name'
                                        ],
                                        0,
                                        1
                                    )
                                );


                            // ==================================
                            // DELIVERY INITIAL
                            // ==================================

                            if (
                                !empty(
                                    $delivery[
                                        'delivery_first_name'
                                    ]
                                )
                            ) {

                                $deliveryInitial =
                                    strtoupper(
                                        substr(
                                            $delivery[
                                                'delivery_first_name'
                                            ],
                                            0,
                                            1
                                        )
                                    );

                            } else {

                                $deliveryInitial = "?";

                            }

                    ?>


                        <tr
                            class="delivery-row"
                        >


                            <!-- DELIVERY ID -->

                            <td>

                                <strong
                                    class="delivery-id"
                                >

                                    #<?php

                                    echo $delivery[
                                        'delivery_id'
                                    ];

                                    ?>

                                </strong>

                            </td>


                            <!-- ORDER -->

                            <td>

                                <div
                                    class="order-info"
                                >

                                    <strong>

                                        #<?php

                                        echo $delivery[
                                            'order_id'
                                        ];

                                        ?>

                                    </strong>


                                    <small>

                                        ৳<?php

                                        echo number_format(
                                            $delivery[
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

                                <div
                                    class="person-info"
                                >


                                    <div
                                        class="person-avatar"
                                    >

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


                                        <small>

                                            <?php

                                            echo !empty(
                                                $delivery[
                                                    'customer_phone'
                                                ]
                                            )
                                                ? htmlspecialchars(
                                                    $delivery[
                                                        'customer_phone'
                                                    ]
                                                )
                                                : "No phone";

                                            ?>

                                        </small>

                                    </div>


                                </div>

                            </td>


                            <!-- DELIVERY MAN -->

                            <td>

                                <div
                                    class="person-info"
                                >


                                    <div
                                        class="person-avatar delivery-avatar"
                                    >

                                        <?php

                                        echo htmlspecialchars(
                                            $deliveryInitial
                                        );

                                        ?>

                                    </div>


                                    <div>

                                        <strong>

                                            <?php

                                            echo htmlspecialchars(
                                                $deliveryManName
                                            );

                                            ?>

                                        </strong>


                                        <?php

                                        if (
                                            !empty(
                                                $delivery[
                                                    'delivery_phone'
                                                ]
                                            )
                                        ) {

                                        ?>

                                            <small>

                                                <?php

                                                echo htmlspecialchars(
                                                    $delivery[
                                                        'delivery_phone'
                                                    ]
                                                );

                                                ?>

                                            </small>

                                        <?php

                                        }

                                        ?>

                                    </div>


                                </div>

                            </td>


                            <!-- VEHICLE -->

                            <td>

                                <?php

                                if (
                                    !empty(
                                        $delivery[
                                            'vehicle_type'
                                        ]
                                    )
                                ) {

                                ?>

                                    <div
                                        class="vehicle-info"
                                    >

                                        <strong>

                                            <?php

                                            echo htmlspecialchars(
                                                $delivery[
                                                    'vehicle_type'
                                                ]
                                            );

                                            ?>

                                        </strong>


                                        <small>

                                            <?php

                                            echo !empty(
                                                $delivery[
                                                    'vehicle_number'
                                                ]
                                            )
                                                ? htmlspecialchars(
                                                    $delivery[
                                                        'vehicle_number'
                                                    ]
                                                )
                                                : "No number";

                                            ?>

                                        </small>

                                    </div>

                                <?php

                                } else {

                                    echo "—";

                                }

                                ?>

                            </td>


                            <!-- ADDRESS -->

                            <td>

                                <div
                                    class="address"
                                    title="<?php
                                    echo htmlspecialchars(
                                        $delivery[
                                            'delivery_address'
                                        ]
                                    );
                                    ?>"
                                >

                                    <?php

                                    echo !empty(
                                        $delivery[
                                            'delivery_address'
                                        ]
                                    )
                                        ? htmlspecialchars(
                                            $delivery[
                                                'delivery_address'
                                            ]
                                        )
                                        : "No address";

                                    ?>

                                </div>

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


                            <!-- ASSIGNED AT -->

                            <td>

                                <?php

                                if (
                                    !empty(
                                        $delivery[
                                            'assigned_at'
                                        ]
                                    )
                                ) {

                                ?>

                                    <div
                                        class="date-info"
                                    >

                                        <?php

                                        echo date(
                                            "d M Y",
                                            strtotime(
                                                $delivery[
                                                    'assigned_at'
                                                ]
                                            )
                                        );

                                        ?>


                                        <small>

                                            <?php

                                            echo date(
                                                "h:i A",
                                                strtotime(
                                                    $delivery[
                                                        'assigned_at'
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

                                <div
                                    class="actions"
                                >


                                    <?php

                                    if (
                                        empty(
                                            $delivery[
                                                'delivery_man_id'
                                            ]
                                        )
                                    ) {

                                    ?>

                                        <a
                                            href="AssignDelivery.php?id=<?php
                                            echo $delivery[
                                                'delivery_id'
                                            ];
                                            ?>"
                                            class="assign-btn"
                                        >

                                            Assign

                                        </a>

                                    <?php

                                    } else {

                                    ?>

                                        <a
                                            href="UpdateDelivery.php?id=<?php
                                            echo $delivery[
                                                'delivery_id'
                                            ];
                                            ?>"
                                            class="edit-btn"
                                        >

                                            Update

                                        </a>


                                        <a
                                            href="AssignDelivery.php?id=<?php
                                            echo $delivery[
                                                'delivery_id'
                                            ];
                                            ?>"
                                            class="reassign-btn"
                                        >

                                            Reassign

                                        </a>

                                    <?php

                                    }

                                    ?>


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

                                No deliveries found.

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

<script src="../controller/admin/AdminDelivery.js"></script>

</body>

</html>