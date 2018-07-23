<?php
	require_once 'backend/session.php';

	if(!isset($_GET['sid']))
	{
		Header("location: index.php");
		exit;		
	}	

	$session = Session::load($_GET['sid']);
	if(is_null($session))
	{
		Header("location: index.php");
		exit;
	}
	
	$errorHTML = '';

	$isSubmit = isset($_POST['go']);
	$fieldErrorCount = 0;
	

	function getForm($key, $default='')
	{
		return isset($_POST[$key]) ? $_POST[$key] : $default;
	}
	
	function getFormHTML($key, $default='')
	{
		return htmlentities(isset($_POST[$key]) ? $_POST[$key] : $default);
	}
		
	function processOrder()
	{
		global $session;
		global $errorHTML;
		require_once 'backend/startup.php';
		
		$system = Startup::getInstance();
		$code = $system->db->redemption->getRedemptionCodeByCode($session->customerId, $session->code);
		if(!is_null($code))
		{
			$group = $system->db->redemption->getRedemptionCodeGroupById($code->id);
			if(!is_null($code))
			{
				if($code->dateUsed == 0)
				{
					$shippingDetails = new stdClass();
					$shippingDetails->firstName = getForm('firstName'); 
					$shippingDetails->lastName = getForm('lastName');
					$shippingDetails->email = getForm('email');
					$shippingDetails->phone = getForm('phone');
					$shippingDetails->country = getForm('country');
					$shippingDetails->region = getForm('region');
					$shippingDetails->city = getForm('city');
					$shippingDetails->street = getForm('street');
					$shippingDetails->unit = getForm('unit');
					$shippingDetails->zip = getForm('zip');
						
					$response = Settings::genesysUpdateOrderItem(
							$session->orderDetails->orderItemId,
							TRUE, 
							$code->id,
							0, 
							0,
							$shippingDetails
						);
						
					if($response->errorCode == Settings::ERROR_CODE_OK)
					{
						$code->dateUsed = time();
						$code->externalOrderId = $session->orderDetails->orderItemId;
						$code->externalOrderDetails = json_encode($session->orderDetails); 
	
						$code->shippingEmail = $shippingDetails->email;
						$code->shippingDetails = json_encode($shippingDetails);
												
						
						if($system->db->redemption->updateRedemptionCode($code))
						{
							
							require_once 'backend/email_service.php';
							$params = new EmailServiceParams();
							
							$params->to[] = $shippingDetails->email;
							$params->from = Settings::EMAIL_FROM_EMAIL;								
							$params->subject = 'Order Confirmation Email (code:' . $session->code . ')';							
							$params->messageHTML = "
									<html>
									<body>
									<table border=\"0\">
									<tr>
										<td colspan=\"2\"><h2>Contact Information</h2></td>							
									</tr><tr>
										<td>First Name : </td>							
										<td>" . htmlentities($shippingDetails->firstName) . "</td>							
									</tr><tr>
										<td>First Name : </td>							
										<td>" . htmlentities($shippingDetails->lastName) . "</td>							
									</tr><tr>
										<td>Email Address : </td>							
										<td>" . htmlentities($shippingDetails->email) . "</td>							
									</tr><tr>
										<td>Phone Number : </td>							
										<td>" . htmlentities($shippingDetails->phone) . "</td>							
									</tr><tr>
										<td colspan=\"2\"><h2>Shipping Address</h2></td>
									</tr><tr>
										<td>Country : </td>							
										<td>" . htmlentities($shippingDetails->country) . "</td>							
									</tr><tr>
										<td>" . ($shippingDetails->country == 'United States' ? 'State' : 'Province') . " : </td>							
										<td>" . htmlentities($shippingDetails->region) . "</td>							
									</tr><tr>
										<td>City : </td>							
										<td>" . htmlentities($shippingDetails->city) . "</td>							
									</tr><tr>
										<td>Street : </td>							
										<td>" . htmlentities($shippingDetails->street) . "</td>							
									</tr><tr>
										<td>Unity : </td>							
										<td>" . htmlentities($shippingDetails->unit) . "</td>							
									</tr><tr>
										<td>" . ($shippingDetails->country == 'United States' ? 'Zip Code' : 'Postal Code') . " : </td>							
										<td>" . htmlentities($shippingDetails->zip) . "</td>							
									</tr><tr>
										<td colspan=\"2\"><h2>Order Details</h2></td>
									</tr><tr>
										<td>Code : </td>							
										<td>" . htmlentities($session->code) . "</td>							
									</tr><tr>
										<td>CID : </td>
										<td>" . htmlentities($session->customerId) . "</td>							
									</tr><tr>
										<td>Order Id : </td>
										<td>" . htmlentities($session->orderDetails->orderItemId) . "</td>							
									</tr><tr>
										<td colspan=\"2\">
											Design :<br/>
											<img src=\"cid:design_preview\"/>
										</td>
									</tr>
									</table>
									</body>
									</html>
								";
							
							$params->attachments[] = new EmailServiceAttachmentParams(
									$session->orderDetails->imageId_L,
									'design_preview.png',
									'design_preview'
								);
							
							$response = EmailService::sendMail($params);
									
							if($response->errorCode == 0)
							{
								Header("location: " . Settings::HOME_URL . 'done.php');
							}
							else
							{
								echo 'Email fervice failed with error : ' . htmlentities($response->errorMessage);
							}
							exit();
						}
						else
						{
							$errorHTML .= 'Unable to update code.<br>';						
						}
					}
					else
					{
						$errorHTML .= htmlentities($response->errorMessage) . '<br>';						
					}
				}
				else
				{
					$errorHTML .= 'Code has already been used.<br>';
				}
			}
			else
			{
				$errorHTML .= 'Group not found.<br>';
			}
		}
		else
		{
			$errorHTML .= 'Code not found.<br>';
		}
		
	}
	

	function writeList($name, $label, $values, $js=NULL)
	{
		echo '<tr>';
		echo '<td class="label">' . htmlentities($label) . '</td>';				
		echo '<td><select class="input" name="' . htmlentities($name) . '"';
		if(!is_null($js)) echo ' onchange="' . $js . '"';
		echo '>';
		
		
		$selected  = getForm($name);
		foreach($values as $value)
		{
			echo $value == $selected ? '<option selected>' : '<option>';
			echo htmlentities($value);
			echo '</option>';				
		}
		
		echo '</select></td>';
		echo '</tr>';
	}
	
	function writeInput($name, $label, $required = FALSE)
	{
		global $isSubmit;
		global $fieldErrorCount;
		
		echo '<tr>';
		echo '<td class="label">' . htmlentities($label);
		echo $required ? ' (*)</td>' : '</td>';
		
		$formValue = getForm($name);
		if($isSubmit && $required)
		{			
			if(trim($formValue) == '')
			{
				$cssClass =  'input_error';
				$fieldErrorCount++;
			}
			else $cssClass = 'input';
		}
		else
		{
			$cssClass = 'input';
		}
		
		echo '<td><input class="' . $cssClass .'" type="text" name="' . htmlentities($name) . '" value="' . htmlentities($formValue) . '"></td>';
		echo '</tr>';
	}
	
	
	function writeCountryList()
	{
		writeList(
			'country', 
			'Country', 
			array('United States', 'Canada'),
			"$('#form').submit()"
		);
	}
	
	function writeRegionList()
	{
		$selected = getForm('country', 'United States');
		if($selected == 'United States')
		{
			writeList(
					'region', 
					'State',
					array('Alabama','Alaska','Arizona','Arkansas','California','Colorado','Connecticut','Delaware','Florida','Georgia','Hawaii','Idaho','Illinois','Indiana','Iowa','Kansas','Kentucky','Louisiana','Maine','Maryland','Massachusetts','Michigan','Minnesota','Mississippi','Missouri','Montana','Nebraska','Nevada','New Hampshire','New Jersey','New Mexico','New York','North Carolina','North Dakota','Ohio','Oklahoma','Oregon','Pennsylvania','Rhode Island','South Carolina','South Dakota','Tennessee','Texas','Utah','Vermont','Virginia','Washington','West Virginia','Wisconsin','Wyoming')
				);
		}
		else
		{
			writeList(
					'region',
					'Province',
					array('Nunavut','Quebec','Northwest Territories','Ontario','British Columbia','Alberta','Saskatchewan','Manitoba','Yukon','Newfoundland and Labrador','New Brunswick','Nova Scotia','Prince Edward Island')
				);
		}
	}
	
	
?>
<!DOCTYPE html>
<html>
<head>
	<title></title>
	<link type="text/css" href="css/themes/humanity/jquery-ui.custom.min.css" rel="stylesheet" />
	<link type="text/css" href="css/design_wizard.css" rel="StyleSheet"/>	

	<script src="js/jquery-1.8.0.min.js"></script>
	<script src="js/jquery-ui-1.8.23.custom.min.js"></script>
</head>
<body>
	<div class = "form">
		<form method="post" id="form">
			<img src="images/logo/MR_LogoHoriz.png" alt="MasonRow" width="400px" height="106px" style="padding-left:100px">
			<table style="margin: 0 auto;">
			<tr><td colspan="2" style="padding-top:2px;padding-bottom:20px;font-weight:normal; ">Please enter your information below.</td></tr>
			<?php
				echo '<tr><td colspan="2" class="header">Contact Information</td></tr>';
				writeInput('firstName', 'First Name', TRUE);
				writeInput('lastName', 'Last Name', TRUE);				
				writeInput('email', 'Email Address', TRUE);
				writeInput('phone', 'Phone Number', FALSE);
				
				echo '<tr><td colspan="2" class="header">Shipping Address</td></tr>';
				
				writeInput('street', 'Street', TRUE);
				writeInput('unit', 'Unit');
				writeInput('city', 'City', TRUE);
				writeCountryList();
				writeRegionList();
				
				
				
				$country = getForm('country', 'United States');
				writeInput('zip', $country == 'United States' ? 'Zip Code' : 'Postal Code', TRUE);
				
				echo '<tr><td colspan="2" class="info" style="padding-left:120px; padding-top:15px; padding-bottom:20px;">(*) fields are required.</td></tr>';
				
				if($isSubmit)
				{
					if($fieldErrorCount == 0)
					{
						if(!strpos(getForm('email'), '@') )
						{
							$errorHTML .= 'Please enter a valid email address.<br>';
						}
						else
						{
							processOrder();
						}					
					}
					else
					{
						$errorHTML .= 'Please fill in all required fields<br>';						
					}
				}				
			?>
			<tr>
				<td colspan="2" style="text-align: center;">
				<?php 
					if($errorHTML != '' ) echo '<div class="error">' . $errorHTML . '</div><br>';	
				?>
				<input type="submit" value="Submit" name="go" class="button" />
				</td>
			</tr>
			</table>
		</form>
	</div>
</body>
</html>