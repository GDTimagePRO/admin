var _prototypeElement = new function()
{
	this.id = "";
	
	this.isReady = function(){ return true; };	
	
	this.getWidth = function()
	{
		var position = this.getPosition();
		if(position.x1 < position.x2) { return position.x2 - position.x1; }
		else { return position.x1 - position.x2; }
	};

	this.setWidth = function(value)
	{
		var position = this.getPosition();
		var offsetX = value / 2;
		var centerX = (position.x1 + position.x2) / 2;
		
		this.setPosition(
				centerX - offsetX, position.y1,
				centerX + offsetX, position.y2
			);
	};
	
	this.getHeight = function()
	{
		var position = this.getPosition();
		if(position.y1 < position.y2) { return position.y2 - position.y1; }
		else { return position.y1 - position.y2; }
	};

	this.setHeight = function(value)
	{
		var position = this.getPosition();
		var offsetY = value / 2;
		var centerY = (position.y1 + position.y2) / 2;
		
		this.setPosition(
				position.x1, centerY - offsetY,
				position.x2, centerY + offsetY
			);
	};
	
	this.getCenterX = function()
	{
		var position = this.getPosition();
		return (position.x1 + position.x2) / 2;
	};

	this.setCenterX = function(value)
	{
		var p = this.getPosition();
		var offsetX = ((p.x1 <= p.x2) ? p.x2 - p.x1 : p.x1 - p.x2) / 2;
		if(p.x1 > p.x2) offsetX *= -1;
		this.setPosition(
			value - offsetX, p.y1,
			value + offsetX, p.y2
		);
	};

	this.getCenterY = function()
	{
		var position = this.getPosition();
		return (position.y1 + position.y2) / 2;
	};

	this.setCenterY = function(value)
	{
		var p = this.getPosition();
		var offsetY = ((p.y1 <= p.y2) ? p.y2 - p.y1 : p.y1 - p.y2) / 2;
		if(p.y1 > p.y2) offsetY *= -1;
		this.setPosition(
			p.x1, value - offsetY,
			p.x2, value + offsetY
		);
	};
	
	this.center = function()
	{
		var offsetX = this.getWidth() / 2;  
		var offsetY = this.getHeight() / 2;
		this.setPosition(
			- offsetX, 
			- offsetY, 
			offsetX, 
			offsetY
		);
	};

	this.getRadius = function()
    {
    	return Math.round(Math.min(this.getWidth(), this.getHeight()) / 2);
    };    
    
	this.setRadius = function(value)
    {
		var position = this.getPosition();
		var centerX = (position.x1 + position.x2) / 2; 
		var centerY = (position.y1 + position.y2) / 2;
		this.setPosition(
				centerX - value, 
				centerY - value, 
				centerX + value, 
				centerY + value 
			);
    };
    
    this.common_init = function()
    {
    	this.scriptContainer = new ScriptContainer(this);
    	this.displayOptions = {
    		tooltip : "",
    		maxSize : 0,
    		visibility : 0
    	};
    };

    this.common_getState = function(state)
    {
    	state.dopt = {
    		tooltip : this.displayOptions.tooltip,
    		maxSize : this.displayOptions.maxSize,
    		visibility : this.displayOptions.visibility,
    	};
    	state.id = this.id;
    	state.script = this.scriptContainer.getState();
    	
    	return state; 
    };
    
    this.common_setState = function(state)
    {
    	if(state.dopt)
    	{
    		this.displayOptions.tooltip = state.dopt.tooltip;
    		this.displayOptions.maxSize = state.dopt.maxSize;
    		this.displayOptions.visibility = state.dopt.visibility;
    	}
    	
		this.id = state.id ? state.id : "";
		this.scriptContainer.setState(state.script);		
    };       
};