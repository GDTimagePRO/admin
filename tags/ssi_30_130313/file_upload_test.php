<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>jQuery File Upload Example</title>
<style>
.bar {
    height: 18px;
    background: green;
}
</style>
</head>
<body>
<input id="fileupload" type="file" name="files[]" data-url="file_upload_php/" multiple>
<input id="fileupload2" type="file" name="files[]" data-url="file_upload_php/" multiple>
<script src="js/lib/jquery-1.8.0.min.js"></script>
<script src="js/lib/file_upload/vendor/jquery.ui.widget.js"></script>
<script src="js/lib/file_upload/jquery.iframe-transport.js"></script>
<script src="js/lib/file_upload/jquery.fileupload.js"></script>
<script src="js/lib/file_upload/canvas-to-blob.js"></script>

<script>
////ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js
//https://github.com/blueimp/jQuery-File-Upload/wiki/Basic-plugin
//https://github.com/blueimp/JavaScript-Canvas-to-Blob
$(function () {
    //$('<input type="file" name="files[]" data-url="file_upload_php/">').fileupload({
	var a = $('<input type="file" name="files[]" data-url="file_upload_php/">');
	var b = $('#fileupload'); 
	
	a.fileupload(
		{
	    dataType: 'json',
        done: function (e, data) {
            alert("done");
            $.each(data.result, function (index, file) {
                $('<p/>').text(file.name).appendTo(document.body);
            });
        },
	    progressall: function (e, data) {
	        var progress = parseInt(data.loaded / data.total * 100, 10);
	        $('#progress .bar').css(
	            'width',
	            progress + '%'
	        );
	    }        
    });
});


function upload()
{
	var c=document.getElementById("myCanvas");
	var ctx=c.getContext("2d");

	
	ctx.fillStyle="#00FF00";
	ctx.fillRect(0,0,650,675);

	ctx.fillStyle="#FF0000";
	ctx.fillRect(0,0,150,75);
	//alert(c.toDataURL("image/png"));				
    c.toBlob(
        function (blob)
        {
        	//formData.append('file', blob, fileName);

            //var file = $('#fileupload2')[0].files[0];
            blob.name = "newfile.png";

        	var a = $('<input type="file" name="files[]" data-url="file_upload_php/">');
        	a.fileupload({
        		    dataType: 'json',
        	        done: function (e, data) {
        	            alert("done");
        	            $.each(data.result, function (index, file) {
        	                $('<p/>').text(file.name).appendTo(document.body);
        	            });
        	        },
        		    progressall: function (e, data) {
        		        var progress = parseInt(data.loaded / data.total * 100, 10);
        		        $('#progress .bar').css(
        		            'width',
        		            progress + '%'
        		        );
        		    }        
        	    });

            a.fileupload('send', {files: [blob]});
		},
		"image/png"
    );
	
	
	// This method is exposed to the widget API and allows sending files
    // using the fileupload API. The data parameter accepts an object which
    // must have a files or fileInput property and can contain additional options:
    // .fileupload('send', {files: filesList});
    // The method returns a Promise object for the file upload call.
    
    //var file = $('#fileupload2')[0].files[0];
    //$('#fileupload').fileupload('send', {files: [file]});
}
</script>

<div id="progress"> <div class="bar" style="width: 0%;"></div> </div>
<input type="button" value="test" onclick="upload()">;
<canvas id="myCanvas" width="200" height="100" style="border:1px solid #c3c3c3;">
Your browser does not support the HTML5 canvas tag.
</canvas>

</body> 
</html>