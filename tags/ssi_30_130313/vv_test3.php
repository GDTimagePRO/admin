<!DOCTYPE html>
<html>
<head>
<script type="text/javascript">
function doStuff()
{
	a = document.getElementById("pattern").value.toUpperCase();
	b = document.getElementById("pattern").value;
	
	alert(document.getElementById("comments").value.toUpperCase().replaceAll(a, b));
}

String.prototype.replaceAll = function(str1, str2, ignore)
{
   return this.replace(new RegExp(str1.replace(/([\,\!\\\^\$\{\}\[\]\(\)\.\*\+\?\|\<\>\-\&])/g, function(c){return "\\" + c;}), "g"+(ignore?"i":"")), str2);
};



</script>

</head>
<body>
<textarea id="comments" cols="25" rows="5">
</textarea><br />
<label>Pattern: </label>
<input type = "text" id = "pattern" value = ""><br />
<input type="button" onclick="doStuff()" value="submit">
</body>
</html>