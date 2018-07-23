<?php
/**
 * @param ThemeInterface $ti
 * @param DesignCustomize $container
 */
function themeMain_single($ti, $container) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Peerless</title>

	<link href="<?php echo $ti->HOME_URL; ?>/css/global.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo $ti->HOME_URL; ?>/css/layout.css" rel="stylesheet" type="text/css" />
	
	<link type="text/css" href="<?php echo $ti->HOME_URL; ?>/css/cupertino/jquery-ui.custom.min.css" rel="stylesheet" />
	<link type="text/css" href="<?php echo $ti->HOME_URL; ?>/css/design_wizard.css" rel="StyleSheet"/>	
	<link type="text/css" href="<?php echo $ti->HOME_URL; ?>/design_customize.css" rel="StyleSheet"/>	
	
	
	<script src="<?php echo $ti->HOME_URL; ?>/js/jquery-1.8.0.min.js"></script>
	<script src="<?php echo $ti->HOME_URL; ?>/js/jquery-ui-1.8.23.custom.min.js"></script>
	
	<script>

	  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){

	  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),

	  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)

	  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	 

	  ga('create', 'UA-65742416-1', 'auto', {'allowLinker': true});

	  ga('require', 'linker');

	  ga('linker:autoLink', ['peerlesstamp.com'] );

	  ga('send', 'pageview');

	</script>
	
	<?php $container->writeHead(); ?>
	
	<style>
		.inputLabel {
			white-space: nowrap;
			padding-left:14px;
			padding-bottom:20px;
			padding-top:20px;
			padding-right:14px;
			vertical-align:text-top;
			color: #00539f;
		}
		
		.inputRow {
			vertical-align: top
		}
		
	</style>
	<script type="text/javascript">
		Scene.DEFAULT_BACKGROUND_COLOR = "#FFFFFF";

		function showSavingDialog()
		{
			var frame = $('#dialog_saving_design');
			frame.css("visibility","visible");
			var updatePosition = function() {
				frame.css('top', $(window).scrollTop() + 'px');		
				frame.css('left', $(window).scrollLeft() + 'px');		
			};			
			$(window).bind('resize', updatePosition);
			$(window).bind('scroll', updatePosition);			
			updatePosition();
		}
		
		function onNext()
		{
			showSavingDialog();
			
			TI.savePreview(function(successful) {
				if(successful)
				{
					TI.navTo("<?php echo $ti->systemURL("confirm_design.php?stamp=".$_GET["stamp"]); ?>");
				}
				else
				{
					alert("Server error.");
					$("#dialog_saving_design").css("visibility","hidden");
				}
			});
		}

		function onPrevious()
		{
			showSavingDialog();
		
			TI.savePreview(function(successful) {
				if(successful)
				{
					TI.navTo("<?php echo $ti->systemURL("design_customize.php?stamp=".$_GET["stamp"]); ?>");
				}
				else
				{
					alert("Server error.");
					$("#dialog_saving_design").css("visibility","hidden");
				}
			});
		}

		function findField(id)
		{
			for(var i=0; i<_system.elements.length; i++)
			{
				var ele = _system.elements[i];
				if((ele.className == "TextElement") && (ele.id == id))
				{
					return ele;
				}
			}
			return null;
		}
		
		$(function() {
			$("#canvas")
				.unbind( "mousemove" )
				.unbind( "mousedown" )
				.unbind( "mouseup" );
			
			_system.clearSelection();
			_system.changeInkColour('Blue','#00539f');
			
			var initField = function(inputId, fieldId, getter) {

				var field = findField(fieldId);
				if(!field) alert("Missing field \"" + fieldId + "\""); 

				var input = $("#" + inputId);
				input.val(field.getText());
				input.bind(
						'change keydown keyup', 
						function() {
							field.setText($(this).val());
							_system.scene.redraw();
						}
					);
			};

			initField("inpBrokerName","Realtor Name");
			initField("inpRealtyCompany","Realtor Firm Name");
			initField("inpFranchiseAffiliation","Realty Company Affiliation");
			initField("inpOfficeNumber","Office Phone");
			initField("inpMobileNumber","Mobile Phone #");
		});
	
	</script>	
</head>

<body unselectable="on" class="unselectable" id="<?php echo ($container->simpleMode ? 'simple_mode' : ''); ?>">
	<?php $container->writeBodyHeader(); ?>
	
	<div id="wrapper">
	
	<?php include 'nav_bar.php'; ?>
			
  <div id="content">
    <h1>Create Your Agent/Broker Stamp Identification</h1>
    <div class="septr mb4"></div>
    <div id="bxt"></div>
    <div id="bxbg">
		<div class="wizard_body">
			<div class="controls_section" style="width:55%">
				<h4 style="padding-left:12px;padding-top:4px;padding-bottom:15px;">
		    		If you do not wish to include your contact information on the<br>
		    		inside stamp base, please click the "Next" button at the bottom<br>
		    		of the screen.
		    	</h4>
				 
				<table border="0" cellpadding="0" cellspacing="0">
				<tr class="inputRow">
					<td><div class="inputLabel">Agent/ Broker Name</div></td>
					<td><input type="text" id="inpBrokerName" class="txt mt2"/></td>
				</tr><tr class="inputRow">
					<td><div class="inputLabel">Realty Company</div></td>
					<td><input type="text" id="inpRealtyCompany" class="txt mt2"/></td>
				</tr><tr class="inputRow">
					<td><div class="inputLabel">Franchise Affiliation</div></td>
					<td><input type="text" id="inpFranchiseAffiliation" class="txt mt2"/><br><div style="font-size:80%;font-style:italic;color:#00539f;">optional</div></td>
				</tr><tr class="inputRow">
					<td><div class="inputLabel">Office Number</div></td>
					<td><input type="text" id="inpOfficeNumber" class="txt mt2"/></td>
				</tr><tr class="inputRow">
					<td><div class="inputLabel">Mobile Number</div></td>
					<td><input type="text" id="inpMobileNumber" class="txt mt2"/></td>
				</tr>
				</table>
	    	</div>
			<div class="preview_section"  style="width:45%; padding-top:150px">
				<canvas id="canvas" width="352px" height="121px"></canvas>
			</div>
		</div>
		<div>
    		<a onclick="onPrevious();" class="big-btn pull-left mt5" id="previous"></a>
			<a onclick="onNext();" class="big-btn pull-right mt5" id="next"></a>
    	</div>
    
      <div class="clear"></div>
    </div>
    <div id="bxb"></div>
  </div>
  <div id="footer-box">PEERLESS&trade; &ndash; The Number One Hand Stamp&trade;, the closing gift that doubles as a marketing tool!</div>
  <div class="clear"></div>
  <div id="copy">&copy; 
    <script language="JavaScript">
    <!--
    today=new Date();
    year0=today.getFullYear();
    document.write(year0);
    //-->
    </script> Peerless</div>
  <div class="clear"></div>

  	</div>
	<?php $container->writeBodyFooter(); ?>
</body>
</html>

<?php
}

//============================================================================================================================================================================================
//============================================================================================================================================================================================
//============================================================================================================================================================================================
//============================================================================================================================================================================================
/**
 * @param ThemeInterface $ti
 * @param DesignCustomize $container
 */
function themeMain_frame($ti, $container) {
?>
<!DOCTYPE html>
<html>
<head>
	<script src="<?php echo $ti->HOME_URL; ?>/js/jquery-1.8.0.min.js"></script>
	<script src="<?php echo $ti->HOME_URL; ?>/js/jquery-ui-1.8.23.custom.min.js"></script>
	<?php $container->writeHead(); ?>	  	
</head>	
<body style="padding: 0px; margin: 0px;">
	<!--canvas id="canvas" style="padding: 0px; margin: 0px; width:224px; height:77px;"></canvas-->
	<canvas id="canvas" style="padding: 0px; margin: -4px;" width="250x" height="85px"></canvas>
	
	<div style="display:none;">
	<?php $container->writeBodyHeader(); ?>
	<?php $container->writeBodyFooter(); ?>
	</div>
	
	<script type="text/javascript">
		Scene.DEFAULT_BACKGROUND_COLOR = "#FFFFFF";

		function findField(id)
		{
			for(var i=0; i<_system.elements.length; i++)
			{
				var ele = _system.elements[i];
				if((ele.className == "TextElement") && (ele.id == id))
				{
					return ele;
				}
			}
			return null;
		}

		TI.BIND_ON_LOAD = window.parent.BIND_ON_LOAD;

		if(TI.BIND_ON_LOAD)
		{
			$(function() {
				$("#canvas")
					.unbind( "mousemove" )
					.unbind( "mousedown" )
					.unbind( "mouseup" );
				
				_system.clearSelection();
				_system.changeInkColour('Blue','#00539f');
				_system.scene.backgroundColor = "#FFFFFF";
	
				window.parent.doneFrameCallback(<?php echo $_GET["page"]; ?>);
			});
		}
		else
		{
			window.parent.doneFrameCallback(<?php echo $_GET["page"]; ?>);			
		}

	</script>
</body>
</html>

<?php
}

//============================================================================================================================================================================================
//============================================================================================================================================================================================
//============================================================================================================================================================================================
//============================================================================================================================================================================================
/** 
 * @param ThemeInterface $ti
 * @param DesignCustomize $container
 */
function themeMain_multi($ti, $container) {
	$stamp = isset($_GET["stamp"]) ? $_GET["stamp"] : '0';
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Peerless</title>

	<link href="<?php echo $ti->HOME_URL; ?>/css/global.css" rel="stylesheet" type="text/css" />
	<link href="<?php echo $ti->HOME_URL; ?>/css/layout.css" rel="stylesheet" type="text/css" />
	
	<link type="text/css" href="<?php echo $ti->HOME_URL; ?>/css/cupertino/jquery-ui.custom.min.css" rel="stylesheet" />
	<link type="text/css" href="<?php echo $ti->HOME_URL; ?>/css/design_wizard.css" rel="StyleSheet"/>	
	<link type="text/css" href="<?php echo $ti->HOME_URL; ?>/design_customize.css" rel="StyleSheet"/>	
	
	
	<style>
		.frameLabel
		{
			padding-top:3px;
			padding-bottom:15px;
			color:#00539f;
			text-align:center;
			width:254px;
		}
	</style>
	
	<script>

	  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){

	  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),

	  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)

	  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	 

	  ga('create', 'UA-65742416-1', 'auto', {'allowLinker': true});

	  ga('require', 'linker');

	  ga('linker:autoLink', ['peerlesstamp.com'] );

	  ga('send', 'pageview');

	</script>
	
	<script src="<?php echo $ti->HOME_URL; ?>/js/jquery-1.8.0.min.js"></script>
	<script src="<?php echo $ti->HOME_URL; ?>/js/jquery-ui-1.8.23.custom.min.js"></script>

	<script type="text/javascript">

		function getFrameURL(page)
		{
			return "<?php echo $ti->systemURL("design_customize.php?peerless_frame=true"); ?>&page=" + page;
		}
		
		var BIND_ON_LOAD = true;
		
		var _slaveCount = 0;
		var _slaveFrameIds = [];
		var _slaveFrames = [];

		function setField(id, value)
		{
			for(var i in _slaveFrames)
			{
				var ele = _slaveFrames[i].contentWindow.findField(id); 
				if(ele)
				{
					ele.setText(value);
				} 
				_slaveFrames[i].contentWindow._system.scene.redraw();
			}
		}

		function savePreviews(onDone)
		{
			var doneCount = 0;
			var onFrameDone = function(successful)
			{
				if(doneCount < 0) return;
				if(!successful)
				{
					doneCount = -1; 
					onDone(false);
					return;
				}

				doneCount++;
				if(doneCount == 1) //_slaveCount
				{
					onDone(true);
				} 
			};
			
			//for(var i in _slaveFrames)
			//{
				var selectedStamp = $('input[name=radio]:checked').val();
				_slaveFrames[selectedStamp].contentWindow.TI.savePreview(onFrameDone);
			//}
		}

		var _doneFrameCallbackCount = 0;
		var doneFrameCallback = function(iFrame) {

			_doneFrameCallbackCount++;
			if(_doneFrameCallbackCount < _slaveFrameIds.length) return;

			for(var i in _slaveFrameIds) 
			{
				_slaveFrames.push($("#" + _slaveFrameIds[i])[0]);
			}

			var initField = function(inputId, fieldId, getter) {

				var field = null;
				for(var i in _slaveFrames)
				{
					field = _slaveFrames[i].contentWindow.findField(fieldId);
					if(field) break;
				}
				
				var input = $("#" + inputId);
				
				if(getter)
				{
					input.bind(
							'change keydown keyup', 
							function() { setField(fieldId, getter.apply(this)); }
						);
				}
				else
				{					
					if(field) input.val(field.getText());
	
					input.bind(
						'change keydown keyup', 
						function() { setField(fieldId, $(this).val()); }
					);
				}
			};
			
			initField("inpName", "name");
			/*initField("inpName", "surname", function() {
				var s = $.trim($(this).val());
				var i = s.lastIndexOf(" ");
				if(i < 0) return "";
				return s.substring(i + 1);
			});*/
			initField("inpName", "surname");
			initField("inpStreet", "street address");
			initField("intpCityState", "city,state,zip");
			initField("inpInitial", "initial");
		};
		
		function saveState(onDone)
		{
			var iFrame = 0;

			doneFrameCallback = function() {
				if(iFrame == _slaveCount)
				{
					onDone(true);
				}
				else
				{
					BIND_ON_LOAD = false;
					_slaveFrames[iFrame].contentWindow.TI.navTo(getFrameURL(iFrame));
					iFrame++;
				}
			};
			
			doneFrameCallback();
		}

		function showSavingDialog()
		{
			var frame = $('#dialog_saving_design');
			frame.css("visibility","visible");
			var updatePosition = function() {
				frame.css('top', $(window).scrollTop() + 'px');		
				frame.css('left', $(window).scrollLeft() + 'px');		
			};			
			$(window).bind('resize', updatePosition);
			$(window).bind('scroll', updatePosition);			
			updatePosition();
		}
		
		function onNext()
		{
			showSavingDialog();
			savePreviews( function(successful) {	
				if(!successful)
				{
					alert("Error generating previews.");
					$("#dialog_saving_design").css("visibility","hidden");
					return;
				}
				
				saveState( function(successful) {
					if(!successful)
					{
						 alert("Error saving data.");
							$("#dialog_saving_design").css("visibility","hidden");
						 return;
					}

					var selectedStamp = $('input[name=radio]:checked').val();
					//window.location.href = "<?php echo $ti->systemURL("confirm_design.php"); ?>&stamp=" + selectedStamp;
					window.location.href = "<?php echo $ti->systemURL("design_customize.php?page=4"); ?>&stamp=" + selectedStamp;
				});
			});
		}

		function onPrevious()
		{
			$( "#dialog_confirm_previous" ).dialog({
				resizable: false,
				height:200,
				width:350,
				modal: true,
				buttons: {
					"Yes": function() {
						$( this ).dialog( "close" );
						window.location.href = "<?php echo Common::$session->urlReturn; ?>";
					},
					"No": function() {
						$( this ).dialog( "close" );
					}
				}
			});
		}
		
		function writeSlaveFrame(page)
		{
			var frameId = "frame" + (_slaveCount++); 
			_slaveFrameIds.push(frameId);
			
			//document.write('<iframe id="' + frameId + '" src="' + getFrameURL(page) + '" style="padding:0px; margin:0px; border-style:none; width:224px; height:77px; overflow:hidden" scrolling="no" frameBorder="0"></iframe>');
			document.write('<iframe id="' + frameId + '" src="' + getFrameURL(page) + '" style="padding:0px; margin:0px; border-style:solid; width:250px; height:85px; overflow:hidden;" scrolling="no" frameBorder="1"></iframe>');
		}

	</script>
</head>

<body unselectable="on" class="unselectable">
	<div id="wrapper">
	
		<?php include 'nav_bar.php'; ?>
		
		<div id="content">		
    	<h1>Create Your Client's Stamp</h1>
		<div class="septr mb4"></div>
		    <div id="bxt"></div>
		    <div id="bxbg">
		 		<div class="wizard_body">
		
			 		<table width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td width="297" valign="top" class="pl7" style="padding-top:15px">				
							<table border="0" cellpadding="0" cellspacing="0" class="mt3 data-normal">
			                <tr>
								<h4 class="tac" style="padding-top:20px;">Client Information</h4>
			                </tr><tr>
								<td width="115">Full Name <br /> <input type="text" id="inpName" class="txt mt2"/></td>
			                </tr><tr>
			                  <td>Street Address<br /> <input type="text" id="inpStreet" class="txt mt2"/></td>
			                </tr><tr>
			                  <td>City State Zip <br /> <input type="text" id="intpCityState" class="txt mt2"/></td>
			                </tr><tr>
			                  <td><div style="font-size:9pt">
If you do not see all of the text entered on the design of your choice, you have exceeded the allowed characters. First try removing characters like middle initials, titles etc. If that does not work, please select another design that allows more characters. Classic holds the most.
<br/><br/>
If you still cannot fit all the text on the design selected, please click the Contact Us link above typing the exact copy you want on which design, we will contact you.
								</div></td>
			                </tr>
			              	</table>
						</td>
			            <td width="546" valign="top" style="padding-left:0px;padding-right:0px; padding-top:25px">
							<h4 class="tal" style="text-align: center; padding-left:0px; padding-top: 7px">
								To select a stamp design, click <img src="<?php echo $ti->HOME_URL; ?>/images/radio-button.png"> button next to one of the examples.<br /> If choosing Monogram, first pick or type the last
								name initial here :&nbsp;&nbsp;
								<span class="f13">			
									<select id="inpInitial">
										<script type="text/javascript">	
											(function() {
												var a = ["A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z"];
												for(var c in a) document.write('<option value="' + a[c] + '">' + a[c] + '</option>');
											})();
										</script>
									</select>		
								</span>
							</h4>
							<br>
							<table width="100%" border="0" cellpadding="0" cellspacing="0" style="padding-top:20px">
			                <tr>
								<td valign="top" style="text-align: center;width:18px">								
									<input name="radio" type="radio" style="margin-top:36px" value="0" <?php echo ($stamp == '0') ? 'checked' : '' ?>/>
								</td><td>
									<script type="text/javascript">writeSlaveFrame(0);</script>
									<div class="frameLabel">MONOGRAM STAMP</div>
								</td>
								<td valign="top" style="text-align: center; width:18px">
									<input name="radio" type="radio" style="margin-top:36px" value="1" <?php echo ($stamp == '1') ? 'checked' : '' ?>/>
								</td><td>
									<script type="text/javascript">writeSlaveFrame(1);</script>
									<div class="frameLabel">CROWN STAMP</div>
								</td>
							</tr><tr>
								<td valign="top" style="text-align: center;width:18px">
									<input name="radio" type="radio" style="margin-top:36px" value="2" <?php echo ($stamp == '2') ? 'checked' : '' ?>/>
								</td><td>
									<script type="text/javascript">writeSlaveFrame(2);</script>
									<div class="frameLabel">CARTOUCHE STAMP</div>									
								</td>
								<td valign="top" style="text-align: center; width:18px">
									<input name="radio" type="radio" style="margin-top:36px" value="3" <?php echo ($stamp == '3') ? 'checked' : '' ?>/>
								</td><td>
									<script type="text/javascript">writeSlaveFrame(3);</script>
									<div class="frameLabel">CLASSIC STAMP</div>
								</td>
							</tr><tr>
								<td colspan="4" style="text-align:center;color:#00539f"><br />Maximum imprint area is <br />
								<span>0.75 x 2.25</span> Inches.
								<p><br />
								</p></td>
							</tr>
							</table>
			            </td>
					</tr>
					</table>
					<div class="clear"></div>
				</div>
				<div>
			    	<a onclick="onPrevious();" class="big-btn pull-left mt5" id="previous"></a>
					<a onclick="onNext();" class="big-btn pull-right mt5" id="next"></a>
			    </div>
				<div class="clear"></div>
			</div>
		    <div id="bxb"></div>
		</div>
		<div id="footer-box">PEERLESS&trade; &ndash; The Number One Hand Stamp&trade;, the closing gift that doubles as a marketing tool!</div>
  		<div class="clear"></div>
  		<div id="copy">
  			&copy; 
		    <script language="JavaScript"  type="text/javascript">
			    today=new Date();
			    year0=today.getFullYear();
			    document.write(year0);
		    </script> 
		    Peerless
		</div>
  		<div class="clear"></div>
	</div>
  	
	<div id="dialog_confirm_previous" title="Abandon Design" class="hidden">
		<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>If you return to the previous page all changes your design will be lost.<br><br> Are you sure you wish to continue ?</p>
	</div>
			
	<div id="dialog_saving_design" style="visibility:hidden;width: 100%; height: 100%; display: block; position: absolute; left: 0; top: 0; z-index: 99999;">
		<div class="ui-widget-overlay"></div>
		<div style="width: 250px; margin: 0 auto;  position:relative; top: 30%; border-radius: 10px; border: 6px solid; border-color: #000; color: #000000; background: #FFFFFF;text-align: center;">
			<h2>Your design is being saved, please stand by.</h2>
			<div id="save_progress_bar"></div>			
		</div>
	</div>
</body>
</html>

<?php
}



/**
 * @param ThemeInterface $ti
 * @param DesignCustomize $container
 */
function themeMain($ti, $container)
{
	if(isset($_GET["peerless_frame"]))
	{
		themeMain_frame($ti, $container);
	}
	else
	{
		if(isset($_GET["page"]) && ($_GET["page"] == "4"))
		{
			themeMain_single($ti, $container);
		}
		else
		{
			themeMain_multi($ti, $container);
		}
	}
}
?>
