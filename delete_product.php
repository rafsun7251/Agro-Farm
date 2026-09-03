<?php

require_once "farmer_data.php";

$id = intval($_GET["id"] ?? 0);

$found = false;

foreach ($_SESSION["farmer_products"] as $key => $product) {

    if ($product["id"] == $id) {

        unset($_SESSION["farmer_products"][$key]);

        $found = true;

        break;
    }

}


$_SESSION["farmer_products"] = array_values($_SESSION["farmer_products"]);


if ($found) {
    $_SESSION["product_message"] = "Product deleted successfully.";
}
else {
    $_SESSION["product_message"] = "Product was not found.";
}


header("Location: my_products.php");

exit();

?>