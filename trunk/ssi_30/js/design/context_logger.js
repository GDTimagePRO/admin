function ContextLogger(ctx, width, height)
{
	this.log = [{
		n:"init",
		p:[width, height]
	}];
	var _loggedParams = {};
	var _params = {};
	var _dictionary = {};
	var me = this;
	
	function log(id, value)
	{
		if(value instanceof Array)
		{
			me.log.push({n:_dictionary[id], p:value});
		}
		else
		{
			me.log.push({n:_dictionary[id], p:[value]});
		}
	}
	
	function methodCall(logged, name, args)
	{
		var argArray = Array.prototype.slice.call(args);

		for(p in _params)
		{
			if(ctx[p] != me[p])
			{
				ctx[p] = me[p];					
				if(_loggedParams[p]) log(p, me[p]);
			}
		}
			
		if(logged) log(name,argArray);
		return ctx[name].apply(ctx, argArray);
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
	
	function addParam(logged, name, id)
	{
		if (typeof id === "undefined") id = name; 
		if (typeof value === "undefined") value = null; 		
		if(collisionTest(name, id)) return;		
		_dictionary[name] = id;		
		_params[name] = true;
		if(logged) _loggedParams[name] = true;
		me[name] = ctx[name];
	}
	
	function addMethod(logged, name, id, funct)
	{
		if (typeof id === "undefined") id = name; 
		if(collisionTest(name, id)) return;		
		_dictionary[name] = id;
		
		if(funct)
		{
			me[name] = function(){
				return funct(logged, name, arguments);
			};			
		}
		else
		{
			me[name] = function(){
				return methodCall(logged, name, arguments);
			};
		}
	}	
	
	//Colors, Styles, and Shadows	
	addParam(true, "fillStyle");
	addParam(true, "strokeStyle");
	addParam(false, "shadowColor");
	addParam(false, "shadowBlur");
	addParam(false, "shadowOffsetX");
	addParam(false, "shadowOffsetY");

	addMethod(false, "createLinearGradient");
	addMethod(false, "createPattern");
	addMethod(false, "createRadialGradient");
	addMethod(false, "addColorStop");
	
	//Line Styles
	addParam(false, "lineCap");
	addParam(false, "lineJoin");
	addParam(true, "lineWidth");
	addParam(false, "miterLimit");
	
	//Rectangles
	addMethod(true, "rect");
	addMethod(true, "fillRect");
	addMethod(true, "strokeRect");
	addMethod(true, "clearRect");
	
	//Paths
	addMethod(true, "fill","f");
	addMethod(true, "stroke","s");
	addMethod(true, "beginPath","bp");
	addMethod(true, "moveTo","m");
	addMethod(true, "closePath","cp");
	addMethod(true, "lineTo","l");
	addMethod(true, "clip");
	addMethod(true, "quadraticCurveTo");
	addMethod(true, "bezierCurveTo");
	addMethod(true, "arc");
	addMethod(true, "arcTo");
	addMethod(false, "isPointInPath");
	
	//Transformations
	addMethod(true, "scale");
	addMethod(true, "rotate","tr");
	addMethod(true, "translate","tl");
	addMethod(true, "transform","tf");
	addMethod(true, "setTransform");
	
	//Text
	addParam(true, "font");
	addParam(true, "textAlign");
	addParam(true, "textBaseline");

	addMethod(true, "fillText","ft");
	addMethod(true, "strokeText","st");
	addMethod(false, "measureText","mt");
	
	//Image Drawing
	addMethod(true, "drawImage");
	
	//Pixel Manipulation
	addParam(false, "width");
	addParam(false, "height");
	addParam(false, "data");

	addMethod(false, "createImageData");
	addMethod(false, "getImageData");
	addMethod(false, "putImageData");
	
	//Compositing
	addParam(true, "globalAlpha");
	addParam(true, "globalCompositeOperation");
	
	//Other
	addMethod(true, "save", "sv");
	addMethod(true, "restore", "r", 
		function(logged, name, arguments) 
		{
			var result = methodCall(logged, name, arguments); 
			for(p in _params)
			{
				me[p] = ctx[p]; 
			}			
			return result;
		}
	);
	addMethod(false, "createEvent");
	addMethod(false, "getContext");
	addMethod(false, "toDataURL");
	
	this.processLog = function()
	{
		//TODO:
		//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		// POP SHOULD RESTORE PROPERTIES
		//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		//	Images need to be loaded ahead of time
		//	calls to line width need to be moved to before begin path
		//	Remove redundant font changes caused by dynamic font sizes
		//	Redundant saves and restores
		//	Merge paths one object ?
		var drawImage = _dictionary["drawImage"];
		var deleteRow = false;
		
		for(var i=0; i<this.log.length; )
		{
			deleteRow = false;
			var item = this.log[i]; 
			if(item.n === drawImage)
			{
				item.p[0] = item.p[0].descriptor;
			}
			
			if(deleteRow)
			{
				this.log.splice(i,1);
			} else i++;
		}
	};
}