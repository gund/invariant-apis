<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 21.01.15
 * Time: 00:02
 */

spl_autoload_extensions(".php");
spl_autoload_register();

use GeoNames\Client;

try {
    if (!isset($_POST["method"]))
        throw new BadMethodCallException("Missing method parameter");

    $method = $_POST["method"];

    $client = new Client();

    if (!method_exists($client, $method) || !is_callable(array($client, $method)))
        throw new BadMethodCallException("Unknown method $method");

    // Get params
    $params = array();
    foreach ($_POST['param'] as $key => $value) {
        if (!empty($value)) $params[] = $value;
    }

    // Execute method
    $result = call_user_func_array(array($client, $method), $params);

    // Output
    switch ($method) {
        case "geolocate":
            echo "<strong>Geo Name Id</strong>: $result";
            break;
        case "describe":
            echo "<h1>Geo Name Info:</h1>";
            outputArray($result);
            break;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

function outputArray($array, $offset = 0) {
    foreach ($array as $k => $v) {
        echo str_repeat("&nbsp;", $offset * 4) . "<strong>$k</strong>: ";
        if (is_array($v)) {
            if (empty($v)) echo "---";
            echo "<br>";
            outputArray($v, $offset + 1);
        }
        else echo "$v<br>";
    }
}