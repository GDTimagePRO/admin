/*
 * 
 * This file will mostly handle the geolocation for the registration form.
 * 
 */
//alert("LOADED REGISTER.JS");
var geoLocateAddress = function () {
    var pubclass = {};
    
    pubclass.init = function() {
        pubclass.initEventHandlers();
    };
    
    pubclass.initEventHandlers = function() {
           if(!navigator.geolocation) {
                console.log("GeoLocation not supported, using IP");
                pubclass.loadAddressByIP();
            }
            else {
                console.log("Using your browser's GeoLocation API");
                navigator.geolocation.getCurrentPosition(
                    pubclass.loadAddressByGeolocation);
            }    
    };
    
    pubclass.loadAddressByGeolocation = function(position) {
        console.dir(position);
    
       /* if ("undefined" !== typeof position.address) {    
            pubclass.updateAddressFields({
                city : position.address.city,
                state: position.address.region,
                zipCode: position.address.postalCode,
                countryCode: position.address.countryCode
            });
        } else {*/
            console.log("Your browser only returns Long/Lat");      
            pubclass.loadAddressByLngLat(position);
        //}    
    };    
    
    jsonCallback = function(data, textStatus, xhr) {
            	console.log(textStatus);
            	console.dir(data);
                /*pubclass.updateAddressFields({
                    city : data.address.placename,
                    stateCode: data.address.adminCode1,
                    zipCode: data.address.postalcode,
                    countryCode: data.address.countryCode
                });*/
            }
    
    pubclass.loadAddressByLngLat = function(position){
    	//alert("loading address by long/lat");
        var data = {
            lat : position.coords.latitude,
            lng : position.coords.longitude
        };
        var latlng = position.coords.latitude+","+position.coords.longitude;
        //alert(latlng);
        var url = "json_address.php";
        //alert("Sending AJAX");
        $.ajax({
            url: url,
            dataType: 'json',
            data: data,
            success:  function(data, textStatus, xhr) {
            	console.log(textStatus);
            	console.dir(data.results[0].address_components);
            	//var city = data.results.
            	if ($("#street").val() == ""){
	            	$("#street").val(data.results[0].address_components[0].long_name+", "+data.results[0].address_components[1].long_name);
	            	$("#city").val(data.results[0].address_components[2].long_name);
	            	$("#state").val(data.results[0].address_components[4].short_name);
	            	$("#country").val(data.results[0].address_components[5].short_name);
	            	$("#zip").val(data.results[0].address_components[6].long_name);
	            }
                /*pubclass.updateAddressFields({
                    city : data.address.placename,
                    stateCode: data.address.adminCode1,
                    zipCode: data.address.postalcode,
                    countryCode: data.address.countryCode
                });*/
            }, 
            error: function(xhr, textStatus, errorThrown) {
                console.log(textStatus);
                //alert(textStatus+" "+errorThrown);
            }
        });
        //alert("Sent AJAX");
        return false;
    };
          
    pubclass.loadAddressByIP = function() {
        $.getJSON("http://www.geoplugin.net/json.gp?jsoncallback=?", 
            function (data) {
                pubclass.updateAddressFields({
                    city : data.geoplugin_city,
                    stateCode: data.geoplugin_regionCode,
                    countryCode: data.geoplugin_countryCode
                });        
            });  
    };
    
    pubclass.updateAddressFields = function(address) {
        console.dir(address);
        alert("updating address fields "+ address.city);
        console.log('City: ' + address.city);
        $("#city").val(address.city);
        
        var stateCode = address.stateCode;
        if (!stateCode) {
            stateCode = $("#state option:contains('" + 
                address.state + "')").attr('value');            
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