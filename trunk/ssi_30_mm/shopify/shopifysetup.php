<?php
    require_once "../backend/settings.php";
    require_once '../backend/startup.php';
    require_once "../backend/db_shopify.php";

    $system = Startup::getInstance();

    $shop = $_GET['shop'];
    $barcode = $_GET['code'];
    $submitUrl = $_GET['submitUrl'];

    $shopifyInstall = $system->db->shopify->getShopifyInstallByShop($shop);
    if(!$shopifyInstall) {
        http_response_code(500);
        echo(ShopifyDB::TRY_AGAIN_MESSAGE . "<br>Error: The shop was not found.");
        exit();
    }

    $site = $system->db->order->getCustomerById($shopifyInstall->customer_id);
    if(!$shopifyInstall) {
        http_response_code(500);
        echo(ShopifyDB::TRY_AGAIN_MESSAGE . "<br>Error: The customer key was not found.");
        exit();
    }

    $html = file_get_contents("http://". Settings::HOME_URL . "SetUp.php?" .
        "code=" . urlencode($barcode) .
        "&sName=" . urlencode($site->idKey) .
        "&url=" . urlencode($submitUrl) .
        "&return_url=" .
        "&system_name=Shopify" .
        "&redirect=false");
    $json = json_decode($html);

    if (!$json->error || $json->error == "") {
	header('Location: '. $json->url);
    }
    die();
?>
