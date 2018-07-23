<?php
	include_once 'backend/email_service.php';

	$params = new EmailServiceParams();
	$params->to[] = 'ValtchanV@GMail.com';
	$params->subject = 'Subject';
	$params->messageHTML = "
			<html>
			<body>
			<h2>A title</h2>
			Some text in here<br/>
			<img src=\"cid:mysid\"/><br/>
			some more text
			</body>
			</html>
		";
	$params->attachments[] = new EmailServiceAttachmentParams(
			'design_templates/2_prev.png',
			'SomePng.png',
			'mysid'
		);
			
	; 
	
	echo print_r(EmailService::sendMail($params));

?>