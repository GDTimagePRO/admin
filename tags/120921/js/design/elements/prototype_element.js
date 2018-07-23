var _prototypeElement = new function()
{
	this.getWidth = function()
	{
		var position = this.getPosition();
		if(position.x1 < position.x2) { return position.x2 - position.x1; }
		else { return position.x1 - position.x2; }
	};
	
	this.getHeight = function()
	{
		var position = this.getPosition();
		if(position.y1 < position.y2) { return position.y2 - position.y1; }
		else { return position.y1 - position.y2; }
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
	}
}