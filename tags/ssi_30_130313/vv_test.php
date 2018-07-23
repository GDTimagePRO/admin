<!DOCTYPE html>
<html>
<head>


<script type="text/javascript">

function ScriptContainerAPISelector(objects)
{
	this.isEmpty = function() { return objects.length == 0; };
		
	this.val = function()
	{
		if(arguments.length > 0)
		{
			for(var i in objects)
			{
				if(objects[i].className === "TextElement")
				{
					objects[i].setText(arguments[0]);
				}				
			}
			return this;
		}
		else
		{
			if(objects.length < 0) return;			
			for(var i in objects)
			{
				if(objects[i].className === "TextElement")
				{
					return objects[i].getText();
				}
			}
			return;
		}
	};
}

function ScriptContainerAPI(query)
{
	var selection = [];
	if(query.charAt(0) == '#')
	{
		var id = query.substring(1);
		
		for(var i in _system.elements)
		{
			if(_system.elements[i].id == id)
			{
				selection.push(_system.elements[i]);	
			}
		}

	}
	return new ScriptContainerAPISelector(selection); 
}

function ScriptContainerCore( $, _, __src)
{	
	try { eval(__src); } catch(e) { alert("Script error " + e.name + ": " + e.message); }
	this.__extract = function(name)
	{
		return eval(name);
	};
}

function ScriptContainer(bindings, context)
{
	var me = this;
	var source = "";
	var stateVariables = {};
	var contextSelector = new ScriptContainerAPISelector([context]);
	var core = null;
	
	function bind(methodName)
	{
		var funct = core ? core.__extract(methodName) : false;
		if(funct)
		{
			me[methodName] = function(args, fallbackResult)
			{
				try
				{
					return funct.apply(contextSelector, args);		
				}
				catch(e) 
				{
					alert("Script error " + e.name + ": " + e.message);
					return fallbackResult;
				}
			};
		}
		else
		{
			me[methodName] = function(args, fallbackResult) { return fallbackResult; };
		}
	}
	
	function bindAll()
	{
		for(var i in bindings) bind(bindings[i]);
	}

	this.getSource = function() { return source; };  
	this.setSource = function(value, vars)
	{
		source = value;
		stateVariables = vars ? vars : {};		
		core = new ScriptContainerCore(ScriptContainerAPI, stateVariables, source);
		bindAll(); 
	};  
	
	this.getState = function() { return {src:source, vars: stateVariables}; } 
	this.setState = function(state)
	{
		this.getSource(state.src, state.vars);		
	};
}


function Selector(objects)
{
	this.val = function(value)
	{
		if(undefined != value)
		{
			return this;
		}
		else
		{
		}
	};
}


function doStuff(X)
{
	//alert(arguments[0]);
	if(null) alert("asdasd");
}



</script>
</head>
<canvas id="canvas" style="width:500px; height:500px"></canvas><br>
<input type="button" onclick="doStuff('asd', '222')" value="go">
<body>
</body>
</html>