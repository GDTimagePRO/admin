<?php
	require_once 'backend/db_redemption.php';

	$errorHTML = "";
	
	$codeStr = "";
	if(isset($_POST['barcode']))
	{
		$codeStr =  RedemptionCode::formatCode($_POST['barcode']);
		if($codeStr != "")
		{
			require_once 'backend/startup.php';

			$system = Startup::getInstance();
			$code = $system->db->redemption->getRedemptionCodeByCode(Settings::CUSTOMER_ID, $codeStr);
			if(!is_null($code))
			{
				$group = $system->db->redemption->getRedemptionCodeGroupById($code->groupId);
				if(!is_null($code))
				{
					if($code->dateUsed == 0)
					{
						$customer = $system->db->redemption->getCustomerById($code->customerId);
						if(!is_null($customer))
						{					
							$customerConfig =  $customer->getConfigObj();
							$groupConfig = $group->getConfigObj();
							
							$query = Settings::SERVICE_GENESYS_INIT;
							$query .= '?code=' . urlencode($groupConfig->genesis->code); 
							$query .= '&sName=' . urlencode($customer->key);
							$query .= '&url=' . urlencode(Settings::HOME_URL . 'genesys_submit.php');
							$query .= '&return_url=' . urlencode(Settings::HOME_URL);
							$query .= '&attachment=' . urlencode(json_encode(array($code->code,$code->customerId)));								
							$query .= '&system_name=' . urlencode(Settings::SYSTEM_NAME);								
							$query .= '&redirect=false';
							
							$response = file_get_contents($query);
							$response = json_decode($response);
							if(!isset($response->error) || is_null($response->error))
							{
								Header("location: " . $response->url);
							}
							else
							{
								$errorHTML = htmlentities($response->error) . '<br>';
							}
						}
						else
						{
							$errorHTML = 'Customer not found.<br>';
						}
					}
					else
					{
						$errorHTML = 'Code has already been used.<br>';
					}				
				}
				else
				{
					$errorHTML = 'Group not found.<br>';
				}				
			}
			else
			{
				$errorHTML = 'Code not found.<br>';
			}
		}
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title></title>
	<link type="text/css" href="css/design_wizard.css" rel="StyleSheet"/>
	<style>
	input, textarea, select
	{ 
		padding: 4px;
		border: solid 1px #E5E5E5;
		outline: 0;
		font: normal 13px/100% Verdana, Tahoma, sans-serif;
		width: 250px;
		background: #FFFFFF left top repeat-x;
		background: -webkit-gradient(linear, left top, left 25, from(#FFFFFF), color-stop(4%, #EEEEEE), to(#FFFFFF));
		background: -moz-linear-gradient(top, #FFFFFF, #EEEEEE 1px, #FFFFFF 25px);
		box-shadow: rgba(0,0,0, 0.1) 0px 0px 8px;
		-moz-box-shadow: rgba(0,0,0, 0.1) 0px 0px 8px;
		-webkit-box-shadow: rgba(0,0,0, 0.1) 0px 0px 8px;
	}
	.button
	{
		width: 350px;
		padding: 9px 15px;
		background: #f4742f url(images/ui-bg_glass_25_EA580C_1x400.png) 50% 50% repeat-x;
		border: 0;
		font-size: 14px;
		color: #FFFFFF;
		-moz-border-radius: 5px;
		-webkit-border-radius: 5px;
	}
	</style>
</head>
<body>
	<div class = "form">
		<form method="post" id="form">
			<img src="images/logo/MR_LogoHoriz.png" alt="MasonRow" width="400px" height="106px" style="padding-left:100px"><br />
			<?php 
					if($errorHTML != '' ) echo '<div class="error" style="padding-top:30px;">' . $errorHTML . '</div>';	
				?>
			<table style="padding-left:120px; text-align:center">
				<tbody>
				<tr><td colspan="2" style="padding-top:30px;padding-bottom:20px;font-weight:normal; text-align:center">Please enter your redemption code below.</td></tr>
					<tr>
						<td style="width:20%; text-align:right">Code: </td>
						<td style="width:80%; text-align:left; padding-left:10px"><input name="barcode" type="text" value="<?php echo htmlentities($codeStr); ?>"></td>
					</tr>
					<tr>
						<td colspan="2" style="text-align:center; margin-left:-50px"><input type="submit" value="Let's get started!" class="button"></td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
</body>
</html>