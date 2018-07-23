<?php
    require_once "../backend/settings.php";
    require_once '../backend/startup.php';
    require_once "../backend/db_shopify.php";

    function compute_hmac($request_vars ){

        if (isset($request_vars['signature'])) {
            unset($request_vars['signature']);
        }

        if (isset($request_vars['hmac'])) {
            unset($request_vars['hmac']);
        }
        $compute_array = array();

        foreach ($request_vars as  $k => $val) {
            $k = str_replace('%', '%25', $k);
            $k = str_replace('&', '%26', $k);
            $k = str_replace('=', '%3D', $k);
            $val = str_replace('%', '%25', $val);
            $val = str_replace('&', '%26', $val);
            $compute_array[$k] = $val;
        }

        $message = http_build_query($compute_array);
        $key = Settings::SHOPIFY_SECRET;

        $digest = hash_hmac ( 'sha256' , $message , $key , false ) ;

        return $digest;
    }

    $system = Startup::getInstance();

    $code = $_GET['code'];
    $shop = $_GET['shop'];
    $state = $_GET['state'];
    $hmac = $_GET['hmac'];

    $shopifyInstall = $system->db->shopify->getShopifyInstallByShop($shop);
    if(!$shopifyInstall) {
        http_response_code(400);
        echo(ShopifyDB::TRY_AGAIN_MESSAGE . "<br>Error: The shop was not found.");
        exit();
    }

    if($state != $shopifyInstall->state_code) {
        http_response_code(400);
        echo(ShopifyDB::TRY_AGAIN_MESSAGE . "<br>Error: The state code doesn't match.");
        exit();
    }

    if((substr($shop, -strlen("myshopify.com")) != "myshopify.com") ||
            !preg_match("/^(([a-z0-9]|[a-z0-9][a-z0-9\-]*[a-z0-9])\.)*([a-z0-9]|[a-z0-9][a-z0-9\-]*[a-z0-9])$/i", $shop)) {
        http_response_code(400);
        echo(ShopifyDB::TRY_AGAIN_MESSAGE . "<br>Error: The shop name is not a valid hostname or does not end with myshopify.com.");
        exit();
    }

    $hash = compute_hmac($_GET);
    if($hash != $hmac) {
        http_response_code(400);
        echo(ShopifyDB::TRY_AGAIN_MESSAGE . "<br>Error: The hmac signature is invalid.");
        exit();
    }

    $url = "https://" . $shop . "/admin/oauth/access_token";
    $data = array(
        "client_id" => Settings::SHOPIFY_KEY_PRIVATE,
        "client_secret" => Settings::SHOPIFY_SECRET_PRIVATE,
        "code" => $code
    );
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data)
        )
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if ($result === FALSE) {
        http_response_code(500);
        echo(ShopifyDB::TRY_AGAIN_MESSAGE . "<br>Error: Authorization request failed.");
        exit();
    }

    $response = json_decode($result);
    $shopifyInstall->auth_token = $response->access_token;
    $shopifyInstall->scope = $response->scope;
    $result = $system->db->shopify->updateShopifyInstall($shopifyInstall);
    if(!$result) {
        http_response_code(500);
        echo(ShopifyDB::TRY_AGAIN_MESSAGE . "<br>Failed to update the shop entry.");
        exit();
    }

    //install script tag
    $url = "https://" . $shop . "/admin/script_tags.json";
    $script_tag_src = "https://" . Settings::HOME_URL . "shopify/shopifyScriptTag.js";
    //check if already installed, don't want duplicates
    $options = array('http' => array('header'  => "X-Shopify-Access-Token: " . $response->access_token . "\r\n",'method'  => 'GET'));
    $script_tags = file_get_contents($url, false, stream_context_create($options));
    if($script_tags == false) { http_response_code(500); echo("Error installing script tags."); exit(); }
    $script_tags = json_decode($script_tags);
    $already_installed = false;
    for($i = 0; $i < count($script_tags->script_tags); $i++) {
        if($script_tags->script_tags[$i]->src == $script_tag_src) {
            $already_installed = true;
            break;
        }
    }
    if(!$already_installed) {
        $data = array(
            "script_tag" => array(
                "event" => "onload",
                "src" => $script_tag_src
            )
        );
        $options = array(
            'http' => array(
                'header' => "X-Shopify-Access-Token: " . $response->access_token . "\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result === FALSE) {
            http_response_code(500);
            echo("Unable to install script tag. Please try the installation again.");
            exit();
        }
    }

    //create webhooks
    $url = "https://" . $shop . "/admin/webhooks.json";
    $webhook_src = "https://" . Settings::HOME_URL . "shopify/shopifyorder.php";
    //check if already installed, don't want duplicates
    $options = array('http' => array('header'  => "X-Shopify-Access-Token: " . $response->access_token . "\r\n",'method'  => 'GET'));
    $webhooks = file_get_contents($url, false, stream_context_create($options));
    if($webhooks == false) { http_response_code(500); echo("Error installing webhooks."); exit(); }
    $webhooks = json_decode($webhooks);
    $already_installed = false;
    for($i = 0; $i < count($webhooks->webhooks); $i++) {
        if($webhooks->webhooks[$i]->topic == "orders/create" && $webhooks->webhooks[$i]->address == $webhook_src) {
            $already_installed = true;
            break;
        }
    }
    if(!$already_installed) {
        $data = array(
            "webhook" => array(
                "topic" => "orders/create",
                "address" => $webhook_src,
                "format" => "json"
            )
        );
        $blah = http_build_query($data);
        $options = array(
            'http' => array(
                'header' => "X-Shopify-Access-Token: " . $response->access_token . "\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result === FALSE) {
            http_response_code(500);
            echo("Unable to create webhook. Please try the installation again.");
            exit();
        }
    }

    header("location: http://". Settings::HOME_URL . "shopify/shopifysuccess.php");
    die();
?>
