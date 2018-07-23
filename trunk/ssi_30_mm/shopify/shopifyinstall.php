<?php
    require_once "../backend/settings.php";
    require_once '../backend/startup.php';
    require_once "../backend/db_shopify.php";

    $system = Startup::getInstance();

    $hmac = $_GET['hmac'];
    $shop = $_GET['shop'];
    $timestamp = $_GET['timestamp'];
    $state_code = uniqid();

    $shopifyInstall = new ShopifyInstall();
    $shopifyInstall->shop = $shop;
    $shopifyInstall->state_code = $state_code;
    $result = $system->db->shopify->createOrUpdateShopifyInstall($shopifyInstall);
    if(!result) {
        http_response_code(500);
        echo(ShopifyDB::TRY_AGAIN_MESSAGE . "<br>Error: Failed to create or update entry for shop.");
        exit();
    }

    $url = "https://" . $shop . "/admin/oauth/authorize" .
           "?client_id=" . Settings::SHOPIFY_KEY .
           "&redirect_uri=http://" . Settings::HOME_URL . "shopify/shopifycallback.php" .
           "&response_type=code&scope=write_script_tags%2Cwrite_orders" .
           "&state=" . $state_code;

    header('Location: '. $url);
    die();
?>