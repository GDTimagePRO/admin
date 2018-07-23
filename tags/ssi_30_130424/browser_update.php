<!DOCTYPE html>
<html>
<head>
	<title>SMARTypeset Solutions Inc. Design Your Own</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />      
	<meta name="description" content="" />
	  	
  	<link type="text/css" href="css/themes/cupertino/jquery-ui.custom.min.css" rel="stylesheet" />
	<link type="text/css" href="css/design_wizard.css" rel="StyleSheet"/>
	
	<style>
		.browser_box {
			display:inline-block;
			margin-left:15px;
			margin-right:15px;
		}
	</style>
	
	<script src="js/lib/jquery-1.8.0.min.js"></script>
	<script src="js/lib/jquery-ui-1.8.23.custom.min.js"></script>
	
	<script type="text/javascript">


	
	function keepGoing()
	{
		document.cookie="no_chrome=true";
		window.location.replace("design_customize.php");
	}
	</script> 	
</head>	

<body unselectable="on" class="unselectable">
	<div class="wizard_frame">
		<div class="wizard_header">
		<div class="wizard_header_title">The web browser you are using is not currently supported</div>
			<div class="wizard_header_info">Please use one of the links below to get the latest version of a supported web browser.</div>
		</div>
		<div class="wizard_body ui-widget-content" style="text-align: center;">		
		
		<br><br><br><br>
		
		<div class="browser_box">
			<a href="https://www.google.com/intl/en/chrome/browser/">
				<img src="images/browser_chrome.png"><br>
				Download Google Chrome
			</a><br><br>
			For the best visual results, we recommend that you download Google Chrome.<br>  
			The image displayed in other Web Browsers may not reflect actual fonts and style used in our standard design templates.<br>
			Your finished product will replicate the template you originally selected on our site using the text entered.  <br>
						
			<br>or<br><br>
			
			<b>I ACKNOWLEDGE THE ABOVE AND WISH TO CONTINUE WITH MY CURRENT BROWSER</b><br>
			(You will be asked to simply approve that the text you have entered is accurate.)<br>
			<br>
			<button onclick="keepGoing()">Accept</button>
		</div>
		<!--
		<div class="browser_box">
			<a href="http://www.mozilla.org/en-US/firefox/new/">
				<img src="images/browser_firefox.png"><br>
				Firefox
			</a>
		</div>
		-->
		</div>
		<div class="ui-widget-header ui-corner-all wizard_footer">
		</div>
	</div>
</body>
</html>



