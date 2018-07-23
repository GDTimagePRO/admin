function ScriptContainerAPISelector(objects)
{
	this.isEmpty = function() { return objects.length == 0; };
		
	this.val = function()
	{
		if(arguments.length > 0)
		{
			for(var i=0; i<objects.length; i++)
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
			for(var i=0; i<objects.length; i++)
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
		
		for(var i=0; i<_system.elements.length; i++)
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
		try{return eval(name); } catch(e){};
		return;
	};
}

function ScriptContainer(context)
{
	var me = this;
	var source = "";
	var stateVariables = {};
	var bindings = [];
	var contextSelector = new ScriptContainerAPISelector([context]);
	var core = null;

	function bind(methodName)
	{
		var funct = core ? core.__extract(methodName) : false;
		if(funct)
		{
			me[methodName] = function()
			{
				try
				{
					var args = Array.prototype.slice.call(arguments);
					return funct.apply(contextSelector, args);		
				}
				catch(e) 
				{
					alert("Script error " + e.name + ": " + e.message);
				}
			};
		}
	}
	
	function rebindAll()
	{
		for(var i in bindings)
		{
			
			delete me[bindings[i]];
			bind(bindings[i]);
		}
	}

	this.setContext = function(element)
	{
		contextSelector = new ScriptContainerAPISelector([element]);
	};

	this.addBinding = function(methodName)
	{
		bindings.push(methodName);
		bind(methodName);
	};
	
	this.getSource = function() { return source; };  
	this.setSource = function(value, vars)
	{
		source = value;
		stateVariables = vars ? vars : {};		
		core = new ScriptContainerCore(ScriptContainerAPI, stateVariables, source);
		rebindAll(); 
	};  
	
	this.getState = function()
	{
		if(source == "") return null;
		return {src:source, vars: stateVariables};
	};
	this.setState = function(state)
	{
		if(state)
		{
			this.setSource(state.src, state.vars);
		}
		else
		{
			this.setSource("");
		}
	};
}