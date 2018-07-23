<?php
	require_once 'settings.php';
	
	
	class EmailServiceAttachmentParams
	{
		public $rid;
		public $sid;
		public $fileName;
		
		public function __construct($rid = NULL, $fileName = NULL, $sid = NULL)
		{
			$this->rid = $rid;
			$this->sid = $sid;
			$this->fileName = $fileName;
		}
	}
	
	class EmailServiceParams
	{
		public $from = NULL;
		public $to = array();
		public $subject = '';
		public $messageHTML = '';
		public $attachments = array();
	}
	
	class EmailServiceResult
	{
		public $errorCode;
		public $errorMessage;
	}
	
	class EmailService
	{
		/**
		 * @param EmailServiceParams $params
		 * @return EmailServiceResult
		 */
		public static function sendMail($params)
		{
			return json_decode(file_get_contents(
					Settings::SERVICE_SEND_MAIL .
					'?params=' . urlencode(json_encode($params))
			));
		}
	}
?>