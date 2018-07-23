<?php
/*
 * jQuery File Upload Plugin PHP Example 5.14
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */
require_once '../Backend/session.php';
require_once '../Backend/resource_manager.php';

$options = array(
		'upload_dir' => Session::uploadDirPath($_GET['sid']) .'/',
		'image_id_prefix' => Session::uploadDirId($_GET['sid']) .'/',
		'color_model' => $_GET['cm']
	);

error_reporting(E_ALL | E_STRICT);
require('UploadHandler.php');
$upload_handler = new UploadHandler($options);
