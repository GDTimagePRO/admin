<?php
include_once "Backend/startup.php";
$startup = Startup::getInstance(".");
$session = $startup->session;
$session = new Session();
$session->close();
Header("location: http://".$startup->settings['url']."login.php");
