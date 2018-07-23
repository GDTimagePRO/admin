/*
when the submit button is clicked we will hide the message div. get the input provided by user and start processing.
*/
$(document).ready(function(){
    $("#dialog").hide();
    $("#menu").menubar({
    autoExpand: true,
    menuIcon: true,
    buttons: true
});
    //$('#menu').menu();
    $('#tabs').tabs({
        select: function(event, ui) { // select event
        	 console.log($(ui.tab)); // the tab selected
             console.log(ui.index);
        	console.log(ui.panel.id);
        }});
    $("#progressbar").progressbar({value: false});
    

});




/*function printVellum(){    
    
     //$('#message').hide();
     $('#dialog').dialog();
    
      userinput = $("#num").val();
    
      var dataString = "num=" + $("input#num").val();

  
      $.ajax(
        {

          url : "server.php",
          type: "GET",
          data: dataString,
          datatype:"json",
          complete:function(){
               $("#progressbar").progressbar({
              value:100});
              $('#message').html('Complete');
              //$('#message').show();
              }
        } );

   
      t = setTimeout("updateStatus()", 1000);
    }*/
var filename = "0output";
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
	switch(template){
		case 0:
			filename = "polymer";
			break;
		case 1:
			filename = "trio";
			break;
		case 2:
			filename = "trioIndex";
			break;
		case 3:
			filename = "embosser";
			break;
		case 4:
			filename = "embosserIndex";
			break;
		case 5:
			filename = "laser";
			break;
		case 6:
			filename = "laserIndex";
			break;
		case 7:
			filename = "dynamicPolymer";
			break;
		case 8:
			filename = "dynamicLaser";
			break;
		case 9:
			filename = "dynamicIndex";
			break;
	}
	if(count>0){
		$('#message').html("Creating PDF...");
		total = total.slice(0, -1);	
		 $('#dialog').dialog({
		        height: 220,
		        width:500,
		        autoOpen:true,
		        position: [100,100]
		    });
		 
		var dataString = "s=0&template="+template+"&id="+total+"&user="+$('#userID').html();
		
		 $.ajax(
	        {

	          url : "pdf.php",
	          type: "POST",
	          data: dataString,
	          datatype:"json",
	          complete:function(){
	              
	        	  //location.reload();
	              }
	        } );
		 t = setTimeout("updateStatus()", 3000);
		/*var url = "pdf.php?"+dataString;
		window.open(url,"_blank");*/
		
	}
	else{
		alert("You must select item/items for printing");
	}
	
}


/*
.getJSON will get the json string being updated by the server.php in server. every 3 second, the 'total' and 'current' count will be parsed and updated to the progress bar.
*/
function updateStatus(){
          $.getJSON('output/'+filename+'status.txt', function(data){

                               var items = [];

                               pbvalue = 0;

                               if(data){

                                    var total = data['total'];

                                    var current = data['current'];

                                    var pbvalue = Math.floor((current / total) * 100);

                                    if(pbvalue>0){

                                        $("#progressbar").progressbar({

                                            value:pbvalue
                                        });
                                        document.title = pbvalue+"% complete - SM-ART Manufacturing Workstation by In-Stamp Solutions";
                                       $("#message").html(pbvalue+"% complete. <br>"+data['message']);
                                    }

                                }
                                if(pbvalue < 100){

                                   t = setTimeout("updateStatus()", 3000);

                                }
                                else{
                                	//$('#dialog').hide();
                                	document.title = "SM-ART Manufacturing Workstation by In-Stamp Solutions";
                                	/*var date = new Date();
                                	var day = date.getDate();
                                	var month = date.getMonth()+1;
                                	var year = date.getFullYear()+"";
                                	filename += "_";
                                	if(day < 10){
                                		filename+="0";
                                	}
                                	filename+=day;
                                	if(month < 10){
                                		filename+="0";
                                	}
                                	filename+=month;
                                	filename+=year.substring(2,4);*/
                                	filename+="_"+data['date'];
                                	console.log(filename);
                  	        	  	var url = "output/"+filename+".zip";
                  	        	  	window.open(url,"_blank");
                                }
          });


}

function changeStatus(status){
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
		$('#message').html("Changing status...");
		 $('#dialog').dialog({
		        height: 130,
		        width:500,
		        autoOpen:true,
		        position: [100,100]
		    });
		 
		var dataString = "status="+status+"&id="+total+"&user="+$('#userID').html();
		
		 $.ajax(
	        {

	          url : "changeStatus.php",
	          type: "POST",
	          data: dataString,
	          datatype:"json",
	          complete:function(){
	              $('#dialog').hide();
	        	  location.reload();
	              }
	        } );
		// t = setTimeout("updateStatus()", 1000);
		/*var url = "pdf.php?"+dataString;
		window.open(url,"_blank");*/
		
	}
	else{
		alert("You must select item/items for changing");
	}
}

function summary(){
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
		var form = document.createElement("form");
	    form.setAttribute("method", "POST");
	    form.setAttribute("action", "summary.php");
	    form.setAttribute("target","_blank");
	    var hiddenField = document.createElement("input");
	    hiddenField.setAttribute("type", "hidden");
	    hiddenField.setAttribute("name", "id");
	    hiddenField.setAttribute("value", total);
	    form.appendChild(hiddenField);
	    
	    document.body.appendChild(form);
	    form.submit();
	}
	else{
		alert("You must select item/items for summarizing");
	}
}

function selectAll(checkbox){
	var selected =  $('#tabs .ui-tabs-panel[aria-hidden="false"]').prop('id');
	var checkboxes = document.getElementsByName('selectedProduct'+selected);
	for(var i=0;i<checkboxes.length;i++){
		checkboxes[i].checked = checkbox.checked;
	}
	
	
}

