<?php
class ResourceOpResult
{
	const CODE_OK = 0;
	public $errorCode;
	public $errorMessage;
	public $data;
}

class Settings
{
	const ADMIN_LOGIN				= 'admin';
	const ADMIN_PASSWORD			= 'woot';

	const DB_SERVER					= 'localhost';
	const DB_USER_NAME				= 'root';
	const DB_PASSWORD				= 'abc123';
	const DB_SCHEMA_NAME			= 'genesys_core';
	const DIR_DATA_ROOT				= 'C:/_genesys_data_';

	const HD_IMAGE_DPI				= 600;

	const HOME_URL					= 'localhost/ssi_30_mm/';

	const SERVICE_RENDER_SCENE		= 'http://localhost:8080/ARTServer/RenderScene';
	const SERVICE_GET_IMAGE			= 'http://localhost:8080/ARTServer/GetImage';
	const SERVICE_RESOURCE_OP		= 'http://localhost:8080/ARTServer/ResourceOp';
	const SERVICE_CREATE_COLLAGE	= 'http://localhost:8080/ARTServer/CreateImageCollage';
	const SERVICE_SEND_MAIL			= 'http://localhost:8080/ARTServer/SendMail';
	const SERVICE_RENDER_IMAGE		= 'http://localhost:8080/ARTServer/RenderImage';
	const SERVICE_GET_FONT			= 'http://localhost:8080/ARTServer/GetFont';


	/**
	 * @return ResourceOpResult
	 */
	public static function resourceOpCopy($srcId, $destId)
	{
		$params = new stdClass();
		$params->opName = 'copy';
		$params->srcId = $srcId;
		$params->destId = $destId;

		return json_decode(file_get_contents(
				Settings::SERVICE_RESOURCE_OP .
				'?params=' . urlencode(json_encode($params))
			));
	}

	/**
	 * @return ResourceOpResult
	 */
	public static function resourceOpTrace($srcId, $destId, $dpi)
	{
		$params = new stdClass();
		$params->opName = 'trace';
		$params->srcId = $srcId;
		$params->destId = $destId;
		$params->dpi = $dpi;

		return json_decode(file_get_contents(
				Settings::SERVICE_RESOURCE_OP .
				'?params=' . urlencode(json_encode($params))
			));
	}

	/**
	 * @return ResourceOpResult
	 */
	public static function resourceOpProcessUserUploadedImage($srcId, $colorModel)
	{
		$params = new stdClass();
		$params->opName = 'process_user_uploaded_image';
		$params->srcId = $srcId;
		$params->param1 = $colorModel;

		return json_decode(file_get_contents(
				Settings::SERVICE_RESOURCE_OP .
				'?params=' . urlencode(json_encode($params))
		));
	}

	public static function getImageUrl($imageId, $noCaching = false, $saveas=null)
	{
		$result = Settings::SERVICE_GET_IMAGE . '?id=' . urlencode($imageId);
		if($noCaching) $result .= '&nocache=true';
		if($saveas) $result .= '&saveas=' . $saveas;
		return $result;
	}
}
?>
