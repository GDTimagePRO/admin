	
	var TI = new function()
	{
		$(function() {
			
			TI.onPreviousButton = function() {
				window.location = TI.returnURL;
			};
			
			$("#previousButton").button().click(TI.onPreviousButton);

			TI.onFinishButton = function(e) {
				if(!$( "#chechbox" ).is(':checked') || ($( "#chechbox2" ).length && !$( "#chechbox2" ).is(':checked')))
				{
					if(e) e.preventDefault(); // Cancel the submit
					$( "#termsAccepted" ).dialog({
						modal: true,
						buttons: {
							Ok: function() {
								$( this ).dialog( "close" );
							}
						}
					});
				}
				else
				{
					if(!e) $("#TI_submit").submit();
				}				
			};
			
			$("#finishButton").button().click(TI.onFinishButton);			
		});
	}
