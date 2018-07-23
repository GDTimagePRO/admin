<!DOCTYPE html>
<html>
<head>


<script type="text/javascript">
function ContextLogger()
{
	var _log = [];
	this.logs = "";
	var _params = {};
	var _dictionary = {};
	var me = this;
	
	function log(id, value)
	{
		//_log.push([_dictionary[id],value]);
		if(value instanceof Array)
		{
			me.logs += id + " : [" + value.join(',') + "]\n";
		}
		else
		{
			me.logs += id + " : " + value + "\n";
		}		
	}
	
	function methodCall(name, args)
	{
		for(p in _params)
		{
			if(me[p] !== _params[p])
			{
				log(p, _params[p] = me[p]);
			}
		}
		
		log(name,Array.prototype.slice.call(args));
	}
	
	function collisionTest(name, id)
	{
		for(p in _dictionary)
		{
			if(_dictionary[p] == id)
			{
				alert(name + " and " + p + " are both assigned to " + id);
				return true;
			}
		}
		return false;
	}
	
	function addParam(name, id, value)
	{
		if (typeof value === "undefined") value = null; 
		if(collisionTest(name, id)) return;		
		_dictionary[name] = id;
		_params[name] = value;
		me[name] = value;
	}
	
	function addMethod(name, id)
	{
		if(collisionTest(name, id)) return;		
		_dictionary[name] = id;
		me[name] = function(){
			methodCall(name, arguments);
		};
	}
	
	//Colors, Styles, and Shadows	
	addParam("prop1","prop1_id");
	addParam("prop2","prop2_id");
	addParam("prop3","prop3_id");
	addParam("prop4","prop4_id");
	
	addMethod("m1","m1_id");
	addMethod("m2","m2_id");
}

function doStuff()
{
	var a = document.getElementById("a");
	var b = document.getElementById("b");
	var c = document.getElementById("c");

	
    //var args = Array.prototype.slice.call(arguments);
    //args.unshift('hello');
    //alert(arguments.callee.name + " : " + args.join(' '));

    var cl = new ContextLogger();

    cl.m1(123,321);

    cl.prop1 = '';
    cl.prop2 = 1;
    cl.prop3 = 'Hello World';
    cl.m2('asd','bbasd');

    cl.prop1 = null;
    cl.prop2 = '1';
    
    cl.m1(123,321);
        
    alert(cl.logs);
    	
}

</script>
</head>	
<textarea id="a" style="width:900px; height:170px"></textarea><br>
<textarea id="b" style="width:900px; height:170px"></textarea><br>
<textarea id="c" style="width:900px; height:170px"></textarea><br>
<input type="button" onclick="doStuff(123, 19.9, 'Hello')" value="go">
<body>
</body>
</html>