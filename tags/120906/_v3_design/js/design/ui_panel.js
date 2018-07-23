//TODO: this stuff needs better update notification callback support

function UIControl(group, id, name, type, params)
{
    this.onGet = params.onGet;
    this.onSet = params.onSet;
    this.items = params.items;
    
    this.getId = function(){ return id; };
    this.getType = function(){ return type; };
    this.getName = function(){ return name; };
    this.getGroup = function() { return group; };
    
    this.update = function(){};
    this.remove = function(){};
}

UIControl.TYPE_TEXT = 0;
UIControl.TYPE_NUMBER = 1;
UIControl.TYPE_LIST = 2;

function UIControlGroup(params)
{
	this.params = params;
	this.members = [];
	
    this.addControl = function(name, type, params)
    {
        UIControlGroup.idSeed++;
        var id = "uicgguid" + UIControlGroup.idSeed;
        var newControl = new UIControl(this, id, name, type, params); 
        this.members.push(newControl);
        return newControl;
    };
}

UIControlGroup.idSeed  = 0;


function UIPanel(propContainerId, listContainerId)
{
    var me = this;
    var idSeed = 0;

    var propTableId = propContainerId + "_b";
    var listTableId = listContainerId + "_b";
    
    var groups = [];
    var selectedGroup = null;
    
    var resetControlValue = function(control)
    {
        var id = control.getId();
        var value = control.onGet ? control.onGet() : "";
        
        switch(control.getType())
        {
        case UIControl.TYPE_TEXT:
        case UIControl.TYPE_NUMBER:
            $('#' + id).val(value);
            break;
            
        case UIControl.TYPE_LIST:
            {
                var select = $('#' + id);
                select.html("");
                if(control.items)
                {
                    for(var i in control.items)
                    {
                        select.append(
                            "<option value='" + i + 
                            ((value == i) ? "' selected>" : "'>") + 
                            htmlEncode(control.items[i].name) + 
                            "</option>"
                        );  
                    }
                }
            }           
            break;
        }       
    };
    
    var clearAllControlHTML = function()
    {
        $('#' + propContainerId).html("<table id='" + propTableId + "'><tbody></tbody></table>");       
    };

    var updateGroupListHTML = function()
    {//$("#theId > div[id=thesearchid]"); 
        var html = "<table>";
        for(var i in groups)
        {
        	var params = groups[i].params;        	
        	if(selectedGroup === groups[i])
        	{
        		html += "<tr style='background-color:#AACCFF'>";        		
        	}
        	else
        	{
				html += "<tr>";
        	}
        	var id = listTableId + i;
        	html += "<td><span id='" + id + "n'>" + htmlEncode(params.type) + "</span></td>";
        	html += "<td><span id='" + id + "s' class='button objectbutton'>Select</span></td>";
        	html += "<td><span id='" + id + "e' class='button objectbutton'>Edit</span></td>";
        	html += "<td><span id='" + id + "d' class='button objectbutton'>Delete</span></td>";        	
        	html += "<td><span class='button objectbutton'>Lock<input id='" + id + "l' type='checkbox'></span></td>";
        	html += "</tr>";
        	
        }       
        html += "</table>";
        $('#' + listContainerId).html(html);
        
       	var doSelect = function(i) { 
       		var group = groups[i];
       		return function()
			{
				_system.setSelected(group.params.element);       			
			}
		};
        
       	var doLock = function(i) { 
       		var group = groups[i];
       		return function()
			{
				group.params.element.setEditAllowMove(!$(this).prop("checked"));				
				_system.saveState();
				//_system.setSelected(group.params.element);	
			}
		};
		
		var doRemove = function(i) {
       		var group = groups[i];
       		return function()
			{
				_system.removeElement(group.params.element);	
			}
			
		}
        
        for(var i in groups)
        {
        	var id = listTableId + i;
        	$("#" + id + "s, #" + id + "n").click(doSelect(i));
        	$("#" + id + "d").click(doRemove(i));
	        $("#" + id + "l")
	        	.change(doLock(i))
	        	.prop("checked", !groups[i].params.element.getEditAllowMove());
        }        
    };

    
    var writeControlHTML = function(group, index)
    {
    	var control = group.members[index];
        var id = control.getId();
        var html = "<tr id='" + id + "_r'>";
        html += "<td>" + htmlEncode(control.getName()) + "</td>";           
        
        switch(control.getType())
        {
        case UIControl.TYPE_TEXT:
        case UIControl.TYPE_NUMBER:
            html += "<td><input id='" + id + "' type='text'></td>";
            break;
            
        case UIControl.TYPE_LIST:
            html += "<td><select id='" + id + "'></select></td>"
            break;
        }
        
        html += "</tr>";        
        
        $('#' + propTableId + ' > tbody:last').append(html);

        switch(control.getType())
        {
        case UIControl.TYPE_TEXT:
        case UIControl.TYPE_NUMBER:
            {
                var ct = control.getType();
                var inp = $('#' + id); 
                
                inp.bind('change', function(event)
                {
                	_system.saveState();
                });
                
                inp.bind('keydown keyup', function(event)
                {
                    if(ct == UIControl.TYPE_NUMBER)
                    {
                        var isNumberKeyCode = 
                            (event.keyCode == 8) || 
                            (event.keyCode == 27) || 
                            (event.keyCode == 13) ||
                            (event.keyCode == 65 && event.ctrlKey === true) ||
                            (event.keyCode >= 35 && event.keyCode <= 39) ||
                            ((!event.shiftKey) && ((event.keyCode >= 48) && (event.keyCode <= 57))) ||                          
                            ((event.keyCode >= 96) && (event.keyCode <= 105));
                        
                        if(!isNumberKeyCode)
                        {
                            event.preventDefault()
                            return false;
                        }
                        control.onSet(parseFloat($(this).val()));
                    }
                    else
                    {
                        control.onSet($(this).val());
                    }
                    return true;
                });
            }
            break;

        case UIControl.TYPE_LIST:
            {
                var inp = $('#' + id); 
                inp.change(function()
                {
                    var val = $(this).val();
                    if(val && control.onSet)
                    {
                        var index = parseInt(val);
                        control.onSet(index, control.items[index]);
                    }
					_system.saveState();
                    return true;    
                });                 
            }
            break;
                        
        }

        //$('#' + propTableId + ' tr:last').after(html);
        
        resetControlValue(control);
    };

	this.clear = function()
	{
    	groups = [];
    	selectedGroup = null;	
        clearAllControlHTML();
    	updateGroupListHTML();	
	}

    this.selectGroup = function(group)
    {        
        if(selectedGroup === group) return;
        clearAllControlHTML();
        
        selectedGroup = group;
        if(selectedGroup)
		{
	        for(var i in group.members)
	        {
	            writeControlHTML(group, i);
	        }			
		}
        updateGroupListHTML();
    };
    
    this.addGroup = function(group)
    {
        groups.push(group);
        updateGroupListHTML();
    }
    
    this.removeGroup = function(group)
    {
    	for(i in groups)
    	{
    		if(groups[i] === group)
    		{
				groups.splice(i,1);
				if(selectedGroup == group)
				{
					selectedGroup = null;	
			        clearAllControlHTML();
				}
		        updateGroupListHTML();
				return true;
    		}
    	}
    	return false;
    }
}
