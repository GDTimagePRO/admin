function htmlEncode(text)
{
    return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}

function System(canvasId, propContainerId, listContainerId, width, height)
{
	var system = this;	
	
	this.elements = [];	
	this.scene = new Scene("canvas", width, height);
	this.scene.getLayer(Scene.LAYER_FOREGROUND).clipMask = new CircularClipMask(
		0, 0, Math.min(width/2 , height/2) - 30
	);
	
	$("canvas").mousedown(function(e) {
		var offset = $(this).offset();
		var x = e.pageX - offset.left;
        var y = e.pageY - offset.top;          
        if(system.scene.onPress(x, y) == null)
        {
        	system.clearSelection();
        	system.scene.redraw();
        }
	});
           
	$("canvas").mousemove(function(e) {
		var offset = $(this).offset();
		var x = e.pageX - offset.left;
		var y = e.pageY - offset.top;          
		system.scene.onMove(x, y);
	});
	
	$("canvas").mouseup(function(e) {
        var offset = $(this).offset();
        var x = e.pageX - offset.left;
        var y = e.pageY - offset.top;        
        system.scene.onRelease(x, y);
	});


	this.ui =  new UIPanel(propContainerId,listContainerId);
	
	this.addElement = function(element)
	{
        this.elements.push(element);
        this.ui.addGroup(element.getUIControlGroup());
        element.setScene(this.scene);        
        this.scene.redraw();
        return element;
    };

	this.removeElement = function(element)
	{
		for(i in this.elements)
		{
			if(this.elements[i] === element)
			{
				this.ui.removeGroup(element.getUIControlGroup());
				element.setScene(null);
				this.elements.splice(i,1);
        		this.scene.redraw();
        		return true;
			}
		}
		return false;
	}

    this.clearSelection = function()
    {
        for(var i in this.elements) this.elements[i].setSelected(false);
        this.ui.selectGroup(null);
    };  
    
    this.setSelected = function(element)
    {
        for(var i in this.elements) this.elements[i].setSelected(false);
        element.setSelected(true);
        this.ui.selectGroup(element.getUIControlGroup());
        this.scene.redraw();        
    };  
};

var _system = {
    onInit:function(canvasId, propContainerId, listContainerId, width, height)
    {
        _system = new System(canvasId, propContainerId, listContainerId, width, height); 
    }
};

