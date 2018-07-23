<?php
	include_once "_common.php";
	
	$email			= getPost('email');
	$password		= getPost('password');
	$password2		= getPost('password2');
	$name			= getPost('name');
	$contactname	= getPost('contactname');
	$department		= getPost('department');
	$street			= getPost('street');
	$city			= getPost('city');	
	$statecode		= getPost('statecode');
	$countrycode	= getPost('countrycode');
	$postalcode		= getPost('postalcode');
	$phone			= getPost('phone');
	$fax			= getPost('fax');
	$error			= array();

	if(isset($_POST['submit']))
	{
		if ($_user_db->getUserByEmail($email) != NULL) 
		{
			$error[] = 'Email that you have provided exists in the system.';
		}
		if($password!=$password2)
		{
			$error[] = "Passwords do not match";
		}
		
		//TODO: More validation ?
		
		if(sizeof($error) == 0)
		{
			$user = new User();
			$user->email = $email;
			$user->name = $name;
			$user->contactName = $contactname;
			$user->department = $department;
			$user->street = $street;
			$user->city = $city;
			$user->stateCode = $statecode;
			$user->countryCode = $countrycode;
			$user->postalCode = $postalcode;
			$user->phone = $phone;
			$user->fax = $fax;
			$user->password = $password;
		
			if ($_user_db->createUser($user)) 
			{
				$_system->loginUser($user->id);
				Header("location: http://".$_url."enter_barcode.php");
				exit();
			}
			else
			{
				/* error code goes */
			}
		}
	}

	//htmlspecialchars
	include "preamble.php";
	
	if(sizeof($error) > 0)
	{
		echo '<div id="error">';
		for($i=0;$i<sizeof($error);$i++)
		{
			echo htmlspecialchars($error[$i]).'<br />';
		}
		echo '</div>';
		echo '<div id="blank">&nbsp;</div>';
	}
?>
	<script language="JavaScript">

		/* This file will mostly handle the geolocation for the registration form. */
		var geoLocateAddress = function ()
		{
			var pubclass = {};		    

		    pubclass.init = function()
		    {
		        pubclass.initEventHandlers();
		    };
		    
			pubclass.initEventHandlers = function()
			{
				if(!navigator.geolocation)
				{
					console.log("GeoLocation not supported, using IP");
					pubclass.loadAddressByIP();
				}
				else
				{
					console.log("Using your browser's GeoLocation API");
					navigator.geolocation.getCurrentPosition( 
							pubclass.loadAddressByGeolocation
					);
				}    
			};
					    
		    pubclass.loadAddressByGeolocation = function(position)
		    {
				console.dir(position);
				console.log("Your browser only returns Long/Lat");      
				pubclass.loadAddressByLngLat(position);
		    };    
		    
		    jsonCallback = function(data, textStatus, xhr)
		    {
				console.log(textStatus);
				console.dir(data);
			};
		    
		    pubclass.loadAddressByLngLat = function(position)
		    {
				var data = {
					lat : position.coords.latitude,
					lng : position.coords.longitude
				};
				
		        var latlng = position.coords.latitude + "," + position.coords.longitude;
				var url = "json_address.php";
				$.ajax({
					url: url,
					dataType: 'json',
					data: data,
					success:  function(data, textStatus, xhr)
					{
						console.log(textStatus);
						console.dir(data.results[0].address_components);
						
						if ($("#street").val() == "")
						{
							$("#street").val(data.results[0].address_components[0].long_name+", "+data.results[0].address_components[1].long_name);
							$("#city").val(data.results[0].address_components[2].long_name);
							$("#state").val(data.results[0].address_components[4].short_name);
							$("#country").val(data.results[0].address_components[5].short_name);
							$("#zip").val(data.results[0].address_components[6].long_name);
						}
					}, 
		            error: function(xhr, textStatus, errorThrown)
		            {
						console.log(textStatus);
					}
				});
				return false;
			};
		          
			pubclass.loadAddressByIP = function()
			{
				$.getJSON(
					"http://www.geoplugin.net/json.gp?jsoncallback=?", 
					function (data)
					{
						pubclass.updateAddressFields({
							city : data.geoplugin_city,
							stateCode: data.geoplugin_regionCode,
							countryCode: data.geoplugin_countryCode
						});        
					}
				);  
		    };
		    
			pubclass.updateAddressFields = function(address)
			{
				console.dir(address);
		        alert("updating address fields "+ address.city);
		        console.log('City: ' + address.city);

		        $("#city").val(address.city);
		        
		        var stateCode = address.stateCode;
				if (!stateCode)
				{
					stateCode = $("#state option:contains('" + address.state + "')").attr('value');            
				}

				console.log('State: ' + stateCode);
		        $("#state").val(stateCode);
		        
				console.log('Zip: ' + address.zipCode);
				$("#zip").val(address.zipCode);
		        
				console.log('Country: ' + address.countryCode);
				$("#country").val(address.countryCode);
			};    
	
			return pubclass;
		} ();

		geoLocateAddress.init();
	</script>


	<form method="post" action="register.php">
		<table id="register_table" width="100">
		<tr>
			<td width="30%">Email:</td>
			<td width="70%"><input type="email" name="email" placeholder="email address" value="<?php echo htmlspecialchars($email); ?>" required="TRUE"/></td>
		</tr>	
		<tr>
			<td width="30%">Password:</td>
			<td width="70%"><input type="password" name="password" placeholder="password" required="true"/></td>
		</tr>
		<tr>
			<td width="30%">Re-type Password:</td>
			<td width="70%"><input type="password" name="password2" placeholder="password" required="true"/></td>
		</tr>
		<tr>
			<td width="30%">Name:</td>
			<td width="70%"><input type="text" name="name" placeholder="Name" value="<?php echo htmlspecialchars($name); ?>" required="TRUE"/></td>
		</tr>
		<tr>
			<td width="30%">Contact Name:</td>
			<td width="70%"><input type="text" name="contactname" value="<?php echo htmlspecialchars($contactname); ?>" placeholder="contact name (if necessary)"/></td>
		</tr>
		<tr>
			<td width="30%">Department:</td>
			<td width="70%"><input type="text" name="department" value="<?php echo htmlspecialchars($department); ?>" placeholder="Department (if necessary)"/></td>
		</tr>
		<tr>
			<td width="30%">Street:</td>
			<td width="70%"><input id="street" type="text" name="street" value="<?php echo htmlspecialchars($street); ?>" placeholder="Street" required="TRUE"/></td>
		</tr>
		<tr>
			<td width="30%">City:</td>
			<td width="70%"><input id="city" type="text" name="city" value="<?php echo htmlspecialchars($city); ?>" placeholder="City" required="TRUE"/></td>
		</tr>
		<tr>
			<td width="30%">State:</td>
			<td width="70%"><input id="state" type="text" name="statecode" value="<?php echo htmlspecialchars($statecode); ?>" placeholder="State" required="TRUE"/></td>
		</tr>
		<tr>
			<td width="30%">Country:</td>
			<td width="70%"><input id="country" type="text" name="countrycode" value="<?php echo htmlspecialchars($countrycode); ?>" placeholder="Country" required="TRUE"/></td>
		</tr>
		<tr>
			<td width="30%">Postal Code:</td>
			<td width="70%"><input id="zip" type="text" name="postalcode" value="<?php echo htmlspecialchars($postalcode); ?>" placeholder="Postal Code" required="TRUE"/></td>
		</tr>
		<tr>
			<td width="30%">Phone:</td>
			<td width="70%"><input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>" placeholder="Ex. (555) 555-5555" required="TRUE"/></td>
		</tr>
		<tr>
			<td width="30%">Fax:</td>
			<td width="70%"><input type="text" name="fax" value="<?php echo htmlspecialchars($fax); ?>" placeholder="Ex. (555) 555-5555"/></td>
		</tr>
		<tr>
			<td colspan="2"><input name="submit" class="submit_button" type="submit" value="Register" /></td>
		</tr>
		</table>
	</form>
	<div id="blank">&nbsp;</div>

<?php include "postamble.php"; ?>