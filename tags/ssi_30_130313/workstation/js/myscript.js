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
    $("#progressbar").progressbar({value: 0});
    

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

function printVellum(){
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
		var dataString = "id = "+total+"&user="+$('#userID').html();
		 $.ajax(
	        {

	          url : "pdftest.php",
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

/*
.getJSON will get the json string being updated by the server.php in server. every 3 second, the 'total' and 'current' count will be parsed and updated to the progress bar.
*/
function updateStatus(){
          $.getJSON($('#userID').html()+'status.json', function(data){

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

                                   t = setTimeout("updateStatus()", 1000);

                                }
          });


}


function select(){
    
}