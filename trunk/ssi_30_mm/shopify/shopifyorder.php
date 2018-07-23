<?php
    require_once "../backend/settings.php";

    $data = json_decode( file_get_contents('php://input') );

    $orderItemId = '';
    for($i = 0; $i < count($data->line_items[0]->properties); $i++) {
        if($data->line_items[0]->properties[$i]->name == "orderId") {
            $orderItemId = $data->line_items[0]->properties[$i]->value;
            break;
        }
    }
    $externalOrderId = $data->order_number;
    $confirm = true;
    $shippingInfo = array(
        'first_name' => $data->shipping_address->first_name,
        'last_name' => $data->shipping_address->last_name,
        'address_1' =>  $data->shipping_address->address1,
        'address_2' =>  $data->shipping_address->address2,
        'city' => $data->shipping_address->city,
        'state_province' => $data->shipping_address->province,
        'zip_postal_code' => $data->shipping_address->zip,
        'country' => $data->shipping_address->country,
        'email' => $data->customer->email,
        'company' => $data->shipping_address->company
    );

    $shippingInfo = urlencode(json_encode($shippingInfo, JSON_FORCE_OBJECT));

    $result = new stdClass();
    $result->errorCode = 1;
    $result = file_get_contents("http://" . Settings::HOME_URL. "services/update_order_item.php?orderItemId=" . $orderItemId . "&externalOrderId=" . $externalOrderId . "&confirm=true&shippingInfo=" . $shippingInfo);

    if($result) {
        $result = json_decode($result);
    }
    if($result && $result->errorCode == 0) {
        http_response_code(200);
    }
    else {
        error_log("Failed to upate order in shopifyorder.php. Order Item: " . $orderItemId . ", Error: " . $result->errorMessage);
        http_response_code(500);
    }
?>
