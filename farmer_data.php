<?php

session_start();

/*
    Demo farmer information

    If your login system already creates these session values,
    they will be used automatically.
*/

if (!isset($_SESSION["user_id"])) {
    $_SESSION["user_id"] = 1;
}

if (!isset($_SESSION["name"])) {
    $_SESSION["name"] = "Rahim Ahmed";
}

if (!isset($_SESSION["email"])) {
    $_SESSION["email"] = "rahim@example.com";
}

if (!isset($_SESSION["phone"])) {
    $_SESSION["phone"] = "01712345678";
}

if (!isset($_SESSION["address"])) {
    $_SESSION["address"] = "Dhaka, Bangladesh";
}


/*
    Sample products

    These are stored in session instead of database.
*/

if (!isset($_SESSION["farmer_products"])) {

    $_SESSION["farmer_products"] = [

        [
            "id" => 1,
            "name" => "Potato",
            "quantity" => 500,
            "price" => 25,
            "unit" => "KG",
            "status" => "Available"
        ],

        [
            "id" => 2,
            "name" => "Tomato",
            "quantity" => 150,
            "price" => 60,
            "unit" => "KG",
            "status" => "Available"
        ],

        [
            "id" => 3,
            "name" => "Onion",
            "quantity" => 200,
            "price" => 50,
            "unit" => "KG",
            "status" => "Available"
        ],

        [
            "id" => 4,
            "name" => "Chilli",
            "quantity" => 100,
            "price" => 80,
            "unit" => "KG",
            "status" => "Available"
        ]

    ];
}


/*
    Sample orders
*/

if (!isset($_SESSION["farmer_orders"])) {

    $_SESSION["farmer_orders"] = [

        [
            "id" => 1001,
            "customer" => "Karim Hasan",
            "product" => "Potato",
            "quantity" => 50,
            "price" => 25,
            "total" => 1250,
            "status" => "Pending",
            "date" => "24 Aug 2026"
        ],

        [
            "id" => 1002,
            "customer" => "Nusrat Jahan",
            "product" => "Tomato",
            "quantity" => 20,
            "price" => 60,
            "total" => 1200,
            "status" => "Pending",
            "date" => "23 Aug 2026"
        ],

        [
            "id" => 1003,
            "customer" => "Sakib Ahmed",
            "product" => "Onion",
            "quantity" => 30,
            "price" => 50,
            "total" => 1500,
            "status" => "Completed",
            "date" => "22 Aug 2026"
        ],

        [
            "id" => 1004,
            "customer" => "Tanvir Rahman",
            "product" => "Chilli",
            "quantity" => 10,
            "price" => 80,
            "total" => 800,
            "status" => "Completed",
            "date" => "20 Aug 2026"
        ]

    ];
}


/*
    Small helper functions
*/

function clean($value)
{
    return htmlspecialchars($value ?? "", ENT_QUOTES, "UTF-8");
}


function findProduct($id)
{
    foreach ($_SESSION["farmer_products"] as $product) {

        if ($product["id"] == $id) {
            return $product;
        }

    }

    return null;
}


function getTotalSales()
{
    $total = 0;

    foreach ($_SESSION["farmer_orders"] as $order) {

        if ($order["status"] == "Completed") {
            $total += $order["total"];
        }

    }

    return $total;
}


function getPendingOrders()
{
    $total = 0;

    foreach ($_SESSION["farmer_orders"] as $order) {

        if ($order["status"] == "Pending") {
            $total++;
        }

    }

    return $total;
}


function getTotalStock()
{
    $total = 0;

    foreach ($_SESSION["farmer_products"] as $product) {
        $total += $product["quantity"];
    }

    return $total;
}
?>