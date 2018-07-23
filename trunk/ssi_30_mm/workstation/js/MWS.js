/**
 * @author SMARTypeset
 */
var currentTab = "All";
function showAll(id){
	
	
	var gridRows = document.getElementById("showGridTable").rows.length;
	
	if( gridRows > 1){
		
		for(i=gridRows; i>1; i--)
		{			
			document.getElementById("showGridTable").deleteRow(i-1);			
		}
	} 

	var xmlhttp;
	if (window.XMLHttpRequest)
	  {// code for IE7+, Firefox, Chrome, Opera, Safari
	  	xmlhttp=new XMLHttpRequest();
	}
	else
	  {// code for IE6, IE5
	  	xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	var line = "<tr>";	
	
	var url = "showallorderitems.php?id="+id;
	xmlhttp.open("GET",url,false);	
	xmlhttp.send();	
	var response = xmlhttp.responseText;	
	var object = jQuery.parseJSON(response);
	console.log(object.length);
	
	for(var i=0;i<object.length;i++){		
	line+= "<td><input name='selectedProduct' value='"+object[i].id+"' type='checkbox' id='selectedProduct' > </td>";
	line+= "<td>"+object[i].submitdate+"</td>"; //
	line+= "<td>"+object[i].startdate+"</td>";
	line+= "<td>"+object[i].order_id+"</td>";
	line+= "<td>"+object[i].id+"</td>";
	line+= "<td>"+object[i].longname+"</td>";
	line+= "<td>"+object[i].name+"</td>";
	line+= "<td><img src='getproductimage.php?id="+object[i].id+"' width='100px' alt='image: "+object[i].id+"' /> </td>";					
	line+= "<td><input name='envelope' value='envelope' type='checkbox' onclick=''></td>";
	line+= "<td><input name='laser' value='laser' type='checkbox' onclick=''></td></tr>";	
	}
	
	$('#showGridTable > tbody:last').append(line);	
};

function showOrderItems(category, id){
	var gridRows = document.getElementById("showGridTable").rows.length;
	
	if( gridRows > 1){		
		for(i=gridRows; i>1; i--)
		{		
			document.getElementById("showGridTable").deleteRow(i-1);			
		}
	}
	
	if (window.XMLHttpRequest)
	  {// code for IE7+, Firefox, Chrome, Opera, Safari
	  	xmlhttp=new XMLHttpRequest();
	}
	else
	  {// code for IE6, IE5
	  	xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	var line = "<tr>";	
	
	var url = "showorderitems.php?category="+category+"&id="+id;	
	xmlhttp.open("GET",url,false);	
	xmlhttp.send();	
	var response = xmlhttp.responseText;	
	var object = jQuery.parseJSON(response);
	
	for(var i=0;i<object.length;i++){		
	line+= "<td><input name='selectedProduct' value='"+object[i].id+"' type='checkbox' id='selectedProduct' > </td>";
	line+= "<td>"+object[i].submitdate+"</td>"; //
	line+= "<td>"+object[i].startdate+"</td>";
	line+= "<td>"+object[i].order_id+"</td>";
	line+= "<td>"+object[i].id+"</td>";
	line+= "<td>"+object[i].longname+"</td>";
	line+= "<td>"+object[i].name+"</td>";
	line+= "<td><img src='getproductimage.php?id="+object[i].id+"' width='100px' alt='image: "+object[i].id+"' /> </td>";					
	line+= "<td><input name='envelope' value='envelope' type='checkbox' onclick=''></td>";
	line+= "<td><input name='laser' value='laser' type='checkbox' onclick=''></td></tr>";	
	}
	
	$('#showGridTable > tbody:last').append(line);	

};

function showOrderplasticItems(plasticId, stageId){
	var gridRows = document.getElementById("showGridTable").rows.length;
	
	if( gridRows > 1){		
		for(i=gridRows; i>1; i--)
		{		
			document.getElementById("showGridTable").deleteRow(i-1);			
		}
	}
	
	if (window.XMLHttpRequest)
	  {// code for IE7+, Firefox, Chrome, Opera, Safari
	  	xmlhttp=new XMLHttpRequest();
	}
	else
	  {// code for IE6, IE5
	  	xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	var line = "<tr>";	
	
	var url = "showorderplasticitems.php?plasticId="+plasticId+"&stageId="+stageId;		
	xmlhttp.open("GET",url,false);	
	xmlhttp.send();		
	var response = xmlhttp.responseText;	
	var object = jQuery.parseJSON(response);
	
	
	for(var i=0;i<object.length;i++){		
	line+= "<td><input name='selectedProduct' value='"+object[i].id+"' type='checkbox' id='selectedProduct' > </td>";
	line+= "<td>"+object[i].submitdate+"</td>"; //
	line+= "<td>"+object[i].startdate+"</td>";
	line+= "<td>"+object[i].order_id+"</td>";
	line+= "<td>"+object[i].id+"</td>";
	line+= "<td>"+object[i].longname+"</td>";
	line+= "<td>"+object[i].name+"</td>";
	line+= "<td><img src='http://www.cameronmcguinness.com/ssi/getproductimage.php?id="+object[i].id+"' width='100px' alt='image: "+object[i].id+"' /> </td>";					
	line+= "<td><input name='envelope' value='envelope' type='checkbox' onclick=''></td>";
	line+= "<td><input name='laser' value='laser' type='checkbox' onclick=''></td></tr>";	
	}
	
	$('#showGridTable > tbody:last').append(line);		

};

function viewQPStatus(){
	showAll(30);	
}

function viewIPStatus(){
	showAll(40);	
}

function print(template) {
	var selected =  $('#tabs .ui-tabs-panel[aria-hidden="false"]').prop('id');
	//console.log("Selected: "+selected);
	var total = "";
	var count = 0;
	var checkboxes = document.getElementsByName('selectedProduct'+selected);
	//console.log(checkboxes.length);
	for(var i=0;i<checkboxes.length;i++){
		if(checkboxes[i].checked){
			count++;
			total+=checkboxes[i].value+"|";
		}
	}
	if(count>0){
		total = total.slice(0, -1);	
		 $('#dialog').dialog();
		var dataString = "template="+template+"&id="+total+"&user="+$('#userID').html();
		 $.ajax(
	        {

	          url : "pdf.php",
	          type: "GET",
	          data: dataString,
	          datatype:"json",
	          complete:function(){
	              /* $("#progressbar").progressbar({
	              value:100});
	              $('#message').html('Complete');
	              //$('#message').show();*/
	        	  var url = $('#userID').html()+"output.pdf";
	        	  window.open(url,"_blank");
	              }
	        } );
		 t = setTimeout("updateStatus()", 1000);
		
	}
	else{
		alert("You must select item/items for printing");
	}
	
}

function selectAll(checkbox){
	var checkboxes = document.getElementsByName('selectedProduct'+currentTab);
	for(var i=0;i<checkboxes.length;i++){
		checkboxes[i].checked = checkbox.checked;
	}
	
	
}
window.onload = function(){
$('ul.tabs').each(function(){
    // For each set of tabs, we want to keep track of
    // which tab is active and it's associated content
    var $active, $content, $links = $(this).find('a');
	
    // If the location.hash matches one of the links, use that as the active tab.
    // If no match is found, use the first link as the initial active tab.
    $active = $($links[0]);
    //$active.addClass('active');
    $content = $($active.attr('href'));
	
    // Hide the remaining content
    $links.not($active).each(function () {
        $($(this).attr('href')).hide();
    });

    // Bind the click event handler
    $(this).on('click', 'a', function(e){
        // Make the old tab inactive.
        var tag = $active.attr('href');
        $active.removeClass('active');
        $active.addClass('active'+tag.substring(1,tag.length));
        $content.hide();
		tag = $(this).attr('href');
        // Update the variables with the new link and content
        $active = $(this);
        $content = $($(this).attr('href'));

        // Make the tab active.
        currentTab = tag.substring(1,tag.length);
       $active.removeClass('active'+tag.substring(1,tag.length))
        $active.addClass('active');
        $content.show();

        // Prevent the anchor's default click action
        e.preventDefault();
    });
});
}