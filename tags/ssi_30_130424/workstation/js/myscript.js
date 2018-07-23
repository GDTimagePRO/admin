/*
when the submit button is clicked we will hide the message div. get the input provided by user and start processing.
*/
$(document).ready(function(){
    $("#dialog").hide();
    $("#menu").menubar({
    autoExpand: true,
    menuIcon: true,
    buttons: true,
    select: select
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

function print(template) {
	var selected =  $('#tabs .ui-tabs-panel[aria-hidden="false"]').prop('id');
	//console.log("Selected: "+selected);
	var total = "";
	var count = 0;
	var checkboxes = document.getElementsByName('selectedProduct'+selected);
	//console.log(checkboxes.length);
	for(var i=0;i<checkboxes.length&&count<30;i++){
		if(checkboxes[i].checked){
			count++;
			total+=checkboxes[i].value+"|";
		}
	}
	if(count>0){
		$('#message').html("Creating PDF...");
		total = total.slice(0, -1);	
		 $('#dialog').dialog({
		        height: 130,
		        width:500,
		        autoOpen:true,
		        position: [100,100]
		    });
		 
		var dataString = "s=0&template="+template+"&id="+total+"&user="+$('#userID').html();
		
		 $.ajax(
	        {

	          url : "pdf.php",
	          type: "GET",
	          data: dataString,
	          datatype:"json",
	          complete:function(){
	              $('#dialog').hide();
	        	  var url = "output/0output.pdf";
	        	  window.open(url,"_blank");
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
          $.getJSON('output/0status.txt', function(data){

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
                                       $("#message").html(pbvalue+"% complete.");
                                    }

                                }
                                if(pbvalue < 100){

                                   t = setTimeout("updateStatus()", 3000);

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
	          type: "GET",
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

function selectAll(checkbox){
	var selected =  $('#tabs .ui-tabs-panel[aria-hidden="false"]').prop('id');
	var checkboxes = document.getElementsByName('selectedProduct'+selected);
	for(var i=0;i<checkboxes.length;i++){
		checkboxes[i].checked = checkbox.checked;
	}
	
	
}

function select(){
    
}