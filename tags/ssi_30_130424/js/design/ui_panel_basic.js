function UIControl(group, id, name, type, params)
{
    this.onGet = params.onGet;
    this.onSet = params.onSet;
    this.items = params.items;
    this.params = params;    
    
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
UIControl.TYPE_BUTTON = 3;

function UIControlGroup(params)
{
	this.params = params;
	this.members = [];
	this.template = null;
	this.showMore = false;
	this.lock = false;
	this.uiPannel = null;
	
    this.addControl = function(name, type, params)
    {
        UIControlGroup.idSeed++;
        var id = "uicgguid" + UIControlGroup.idSeed;
        var newControl = new UIControl(this, id, name, type, params);
        this.members.push(newControl);
        return newControl;
    };

    this.updateControl = function(name)
    { 
    	if(this.uiPannel == null) return;
    	
    	for(var i in this.members)
    	{
    		if(this.members[i].getName() == name)
    		{
    			this.uiPannel.resetControlValue(this.members[i]);
    		}
    	}
    };
}
UIControlGroup.idSeed  = 0;


function UIPanel(propContainerId, listContainerId)
{
    var me = this;
    
	var elementTooltips = [];
    var elementButtonBars = [];
    
	var verticalSliderInput = null;
	var verticalSliderInputOldValue = null;
	var verticalSliderTimer = null;
	var verticalSlider = $("#uiPanelSlider").slider({
		orientation: "vertical",
		range: "min",
		min: 0,
		max: 100,
		value: 60,
		slide: function( event, ui ) {
			verticalSliderInput.val( ui.value );			
			verticalSliderInput.data('uiControlInstance').onSet(parseFloat(verticalSliderInput.val()));
		}
	});
	
	var removeTooltips = function(list)
	{
		for(var i in list)
		{
			list[i].destroy();
		}
		list.length = 0;
	}
	
	var makeTooltip = function(selector, text, list)
	{
		selector.qtip({
        	content: text, // Set the tooltip content to the current corner
            position: {
            	corner: {
            		tooltip: "bottomMiddle", // Use the corner...
            		target: "topMiddle" // ...and opposite corner
            	}
            },
            show: {
            	delay: 400,
            	when: { event : "mouseover" } // Don't specify a show event
            },
            hide: {
            	when: { event : "mouseout" }
            },
            style: {
            	background: '#EFF6FE',
            	color: '#362B36',
            	
            	title: {
            		background: '#D0E9F5',
            		color: '#5E99BD'
            	},

            	border: {
            		width: 8,
            		radius: 8,
            		color: '#ADD9ED'
            	},
            	
            	padding: 10, 
            	textAlign: 'center',
            	tip: true,
            }
        });
		list.push(selector.qtip("api"));
	};
	
	verticalSlider.mousedown(function() {
		if (verticalSliderInput != null) {
			verticalSliderInput.focus();
			clearTimeout(verticalSliderTimer);
		}
	});

	var verticalSliderOnFocus = function() {
		if( (verticalSliderInput == null) ||
			($(this).attr("id") != verticalSliderInput.attr("id")))
		{			
			verticalSliderInput = $(this);
			_system.setSelected(verticalSliderInput.data('uiGroupInstance').params.element);
			verticalSliderInputOldValue = verticalSliderInput.val();
		}

		verticalSlider.slider("option", "min", verticalSliderInput.data('minValue'));
		verticalSlider.slider("option", "max", verticalSliderInput.data('maxValue'));
		verticalSlider.slider("option", "value", verticalSliderInput.val());

		var pos = $(this).position();
		pos.left = pos.left + $(this).width() + 10; 
		pos.top -= 36;
		
		verticalSlider.show();
		verticalSlider.css( "top", pos.top + "px");
		verticalSlider.css( "left", pos.left + "px");
		clearTimeout(verticalSliderTimer);
	};
	
	var verticalSliderOnBlur = function() {
		verticalSliderTimer = setTimeout(
			function() 
			{ 
				if((verticalSliderInput != null) && (verticalSliderInputOldValue != verticalSliderInput.val()))
				{
					_system.saveState();
					verticalSliderInput = null;
				}
				verticalSlider.hide(); 
			}, 
			50
		);	
	};
	
	var verticalSliderOnInput = function() {
		var data = verticalSliderInput.val();
		
		if (data.length > 0) 
		{
			if (parseInt(data) >= 0 && parseInt(data) <= 100) 
			{
				verticalSlider.slider("option", "value", data);
			}
			else
			{
				if (parseInt(data) < 0) 
				{
					$("#txtVal").val("0");
					verticalSlider.slider("option", "value", 0);
				}
				if (parseInt(data) > 100) 
				{
					$("#txtVal").val("100");
					verticalSlider.slider("option", "value", 100);
				}
			}
		}	
		else
		{ 
			verticalSlider.slider("option", "value", 0); 
		}  
	};
	
    var groups = [];
    var selectedGroup = null;
    var previousGroup = null;
    
    var resetControlValue = this.resetControlValue = function(control)
    {
        var id = control.getId();
        var value = control.onGet ? control.onGet() : "";
        
        switch(control.getType())
        {
        case UIControl.TYPE_TEXT:
        case UIControl.TYPE_NUMBER:
        {
    		var inpt = $('#' + id);
    		if(inpt.val() != String(value)) inpt.val(value);
    		break;
        }

        case UIControl.TYPE_BUTTON:        	
        	$('#' + id+" span").text(value);
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

    
    var getControlHTML = function(group, index)
    {
    	var control = group.members[index];
        switch(control.getType())
        {
        case UIControl.TYPE_TEXT:
	        return '<input class="control_input" id="' + control.getId() + '" type="text" style="padding-left:2px">';
        case UIControl.TYPE_NUMBER: 
            return '<input class="control_input" id="' + control.getId() + '" type="text">';
            
        case UIControl.TYPE_LIST:
            return '<select class="control_input" id="' + control.getId() + '"></select>';
            
        case UIControl.TYPE_BUTTON:
            return '<button class="control_input" id="' + control.getId() + '"></button>';
        }
        
        return "";        
    };
    
    
    var initControl = function(group, index)
    {
    	var control = group.members[index];
        var id = control.getId();
        
        switch(control.getType())
        {
        case UIControl.TYPE_NUMBER:          
        case UIControl.TYPE_TEXT:
            {
                var ct = control.getType();
                var inp = $('#' + id); 
                if(inp.length == 0) return;
                
                inp.data('uiControlInstance', control);                    
                inp.data('uiGroupInstance', group);                    
                
                if(group.title)
                {
                	inp.attr("placeholder", group.title);
                }                
                
                if(control.getType() == UIControl.TYPE_NUMBER)
                {
                    inp.data('minValue', control.params.minValue ? control.params.minValue : 0);                    
                    inp.data('maxValue', control.params.maxValue ? control.params.maxValue : 100);                    

                    inp.focus(verticalSliderOnFocus);
                    inp.blur(verticalSliderOnBlur);
                    inp.bind('input', verticalSliderOnInput);
                }
                else
                {
                	inp.focus(function() { _system.setSelected($(this).data('uiGroupInstance').params.element); });
                }
                
                inp.bind('change', function(event)
                {
                	_system.saveState();
                });
                
                
                inp.bind('keydown keyup', function(event)
                {
                    if(ct == UIControl.TYPE_NUMBER)
                    {
                        var isNumberKeyCode = 
                            (event.keyCode == 189) || 
                            (event.keyCode == 8) || 
                            (event.keyCode == 46) || 
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
                        var v = parseFloat($(this).val());
                        if(!isNaN(v)) control.onSet(v);
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
                if(inp.length == 0) return;
                
                
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
                        
        case UIControl.TYPE_BUTTON:
	        {
	            var inp = $('#' + id); 
                if(inp.length == 0) return;
                
	            inp.button().click(function()
	            {
	                if(control.onSet)
	                {
	                	 _system.setSelected(group.params.element);
	                	 
	                	if(control.onSet(index, $(this).children('span')))
	                	{	                		
	    					if(control.onGet)
	    					{
    		                	$(this).children('span').text(control.onGet());
        					}
	    					_system.saveState();
	                	}
	                }
	                return true;
	            });       
	        }
	        break;
        }

        resetControlValue(control);
    };    
    
    var buttonBarUpdateTimer = setInterval(function(){
//******************************************************************************************************
//* Disabled in order to hide extra options
//******************************************************************************************************
//    	for(var i in elementButtonBars)
//    	{
//    		elementButtonBars[i].onUpdate();
//    	}
    }, 20);
    
    var ButtonBar = function(group)
    {
    	var targetHeight = 0;
    	var height = 0;
    	var speed = 5;
    	var element = null;
    	
    	this.group = group;
    	
    	this.show = function()
    	{
    		targetHeight = 60;
    		if(targetHeight == height) return;
    		this.create();
    	};
    	
    	this.hide = function()
    	{
        	verticalSlider.hide();
    		targetHeight = 0;    		
    	};

    	this.onUpdate = function()
    	{
    		if(element && (targetHeight != height))
    		{
    			if(height < targetHeight)
    			{
    				height += speed;
    				if(height > targetHeight) height = targetHeight;    				
    			}
    			else
    			{
    				height -= speed;
    				if(height < targetHeight) height = targetHeight;
    			} 
    			element.css("height", height + "px");
    		}
    	};
    	
    	this.create = function()
    	{
    		if(element) return;
    		
    		/**************************************************************************************************
    		 * Added to hide extra options
    		 **************************************************************************************************/
    		return;
    		
    		//TODO: Needs tooltips
    		var iMemberFont = findGroupMember(group, "font");
    		var iMemberSize = findGroupMember(group, "size");
    		var iMemberAngle = -1;
    		
    		var html =	
    			'<div style="overflow:hidden; height:' + height + 'px;">' +				
				'<div style="display:inline-block;width:280px;padding-right:10px;margin-left:7px">' + getControlHTML(group, iMemberFont) + '</div><br />';
    		
    		//'<button id="btnbar_bold" class="button_bar_button">B</button>' +
    		html += '<img id="btnbar_bold" class="button_bar_button" src="' +
				(!(group.params.element.getBold()) ? 'images/bold.png">' : 'images/bold_selected.png">');
				
    		//html += '<button id="btnbar_italic" class="button_bar_button">I</button>';
    		html += '<img id="btnbar_italic" class="button_bar_button" src="' +
				(!(group.params.element.getItalic()) ? 'images/italic.png">' : 'images/italic_selected.png">');
    		
    		if(group.template == UIPanel.TEMPLATE_TEXT_LINE_1)
    		{
    			if (group.params.element.getAlignment() == TextElement.ALIGN_LEFT)
				{
    				html +=
    					'<img id="btnbar_align_left" class="button_bar_button" src="images/align_left_selected.png">' +
	    				'<img id="btnbar_align_center" class="button_bar_button" src="images/align_center.png">' +
	    				'<img id="btnbar_align_right" class="button_bar_button" src="images/align_right.png">';
				}
    			else if (group.params.element.getAlignment() == TextElement.ALIGN_CENTER)
				{
    				html +=
    					'<img id="btnbar_align_left" class="button_bar_button" src="images/align_left.png">' +
	    				'<img id="btnbar_align_center" class="button_bar_button" src="images/align_center_selected.png">' +
	    				'<img id="btnbar_align_right" class="button_bar_button" src="images/align_right.png">';
				}
    			else if (group.params.element.getAlignment() == TextElement.ALIGN_RIGHT)
				{
    				html +=
    					'<img id="btnbar_align_left" class="button_bar_button" src="images/align_left_selected.png">' +
	    				'<img id="btnbar_align_center" class="button_bar_button" src="images/align_center.png">' +
	    				'<img id="btnbar_align_right" class="button_bar_button" src="images/align_right_selected.png">';
				}
    			else
				{
	    			html += 
	    				'<img id="btnbar_align_left" class="button_bar_button" src="images/align_left.png">' +
	    				'<img id="btnbar_align_center" class="button_bar_button" src="images/align_center.png">' +
	    				'<img id="btnbar_align_right" class="button_bar_button" src="images/align_right.png">'
				}
				html +=
					'<div id="text_size" style="width:38px;display:inline-block;vertical-align:top; padding-top:6px;">&nbsp;&nbsp;Size:</div>' +
					'<div style="display:inline-block;width:20px;vertical-align: top;" >' + getControlHTML(group, iMemberSize) + '</div><br>';     			
    		}
    		else
    		{
                iMemberAngle = findGroupMember(group, "angle");
                html += '<img id="btnbar_inverted" class="button_bar_button" src="' +
					(!(group.params.element.getInverted()) ? 'images/invert.png">' : 'images/invert_selected.png">');
                
				html +=
					'<div id="text_size" style="width:38px;display:inline-block;vertical-align:top; padding-top:6px;">&nbsp;&nbsp;Size:</div>' +
					'<div style="display:inline-block;width:20px;vertical-align: top;" >' + getControlHTML(group, iMemberSize) + '</div>' +
					'<div id="text_angle" style="width:57px;display:inline-block;vertical-align:top; padding-top:6px;">&nbsp;&nbsp;&nbsp;&nbsp;Angle:</div>' +
					'<div style="display:inline-block;width:25px;vertical-align: top;" >' + getControlHTML(group, iMemberAngle) + '</div>'; 
    		}
    			//'<button id="btnbar_underline" class="button_bar_button">U</button>' +
    		html += '</div>'
    		
    		element = $(html);
    		element.appendTo("#" + group.updateHTML.bodyId);
    		
    		initControl(group, iMemberFont);
    		initControl(group, iMemberSize);
    		
    		var btnbar_bold = element.find('#btnbar_bold').button();
    		makeTooltip(btnbar_bold, "Bold", elementTooltips);
    		btnbar_bold.click( function() {
    			((group.params.element.getBold()) ? btnbar_bold[0].src='images/bold.png' : btnbar_bold[0].src='images/bold_selected.png');
    			group.params.element.setBold(!group.params.element.getBold());
    			_system.saveState();
    			_system.scene.redraw();
    		});
    		
    		var btnbar_italic = element.find('#btnbar_italic').button();
    		makeTooltip(btnbar_italic, "Italic", elementTooltips);
    		btnbar_italic.click( function() {
    			((group.params.element.getItalic()) ? btnbar_italic[0].src='images/italic.png' : btnbar_italic[0].src='images/italic_selected.png');
    			group.params.element.setItalic(!group.params.element.getItalic());
    			_system.saveState();
    			_system.scene.redraw();
    		});

    		if(group.template == UIPanel.TEMPLATE_TEXT_LINE_1)
    		{
	    		var btnbar_align_left = element.find('#btnbar_align_left').button();
	    		var btnbar_align_center = element.find('#btnbar_align_center').button();
	    		var btnbar_align_right= element.find('#btnbar_align_right').button();
	    		
	    		makeTooltip(btnbar_align_left, "align left", elementTooltips);
	    		btnbar_align_left.click( function() {    		
	    			if(group.params.element.getAlignment() == TextElement.ALIGN_LEFT) return;
	    			btnbar_align_left[0].src='images/align_left_selected.png';
    				btnbar_align_center[0].src='images/align_center.png';
					btnbar_align_right[0].src='images/align_right.png';
	    			group.params.element.setAlignment(TextElement.ALIGN_LEFT);
	    			_system.saveState();
	    			_system.scene.redraw();
	    		});
	
	    		
	    		makeTooltip(btnbar_align_center, "center text", elementTooltips);
	    		btnbar_align_center.click( function() {    		
	    			if(group.params.element.getAlignment() == TextElement.ALIGN_CENTER) return;
	    			btnbar_align_left[0].src='images/align_left.png';
    				btnbar_align_center[0].src='images/align_center_selected.png';
					btnbar_align_right[0].src='images/align_right.png';
	    			group.params.element.setAlignment(TextElement.ALIGN_CENTER);
	    			_system.saveState();
	    			_system.scene.redraw();
	    		});
	
	    		
	    		makeTooltip(btnbar_align_right, "align right", elementTooltips);
	    		btnbar_align_right.click( function() {    		
	    			if(group.params.element.getAlignment() == TextElement.ALIGN_RIGHT) return;
	    			btnbar_align_left[0].src='images/align_left.png';
    				btnbar_align_center[0].src='images/align_center.png';
					btnbar_align_right[0].src='images/align_right_selected.png';
	    			group.params.element.setAlignment(TextElement.ALIGN_RIGHT);
	    			_system.saveState();
	    			_system.scene.redraw();
	    		});
    		}
    		else
    		{
        		initControl(group, iMemberAngle);
    			
        		var btnbar_inverted = element.find('#btnbar_inverted').button();
        		makeTooltip(btnbar_inverted, "flip text", elementTooltips);
        		btnbar_inverted.click( function() {
        			((group.params.element.getInverted()) ? btnbar_inverted[0].src='images/invert.png' : btnbar_inverted[0].src='images/invert_selected.png');
        			group.params.element.setInverted(!group.params.element.getInverted());
        			_system.saveState();
        			_system.scene.redraw();
        		});
    		}
    	};
    	
    	this.destroy = function()
    	{
    		if(element)
    		{
    			element.remove();
    			element = null;
    		}
    	};    	
    };

    var getButtonBar = function(group)
    {
    	for(var i in elementButtonBars)
    	{
    		if(elementButtonBars[i].group === group)
    		{
    			return elementButtonBars[i];
    		}
    	}    	
    };
    
    var findGroupMember = function(group, memberName)
    {
    	for(var i in group.members)
		{
    		if(group.members[i].getName() == memberName) return i;
		}
    	return -1;
    };
    
    
    
    
    var updateHTML = this.updateHTML = function()
    {
    	verticalSlider.hide();
    	removeTooltips(elementTooltips);
    	
    	var html = "";
    	
    	var groupIdBase = propContainerId + "_g";
		var initTodo = [];

		for(var i in groups)
        {
    		var group = groups[i]; 
    		var id = groupIdBase + i;	
    		var iMember;
    		
    		if(group.params.element.displayOptions.visibility == 0)
    		{
	    		switch(group.template)
	    		{
	    		case UIPanel.TEMPLATE_TEXT_LINE_1:
	    		case UIPanel.TEMPLATE_TEXT_CIRCLE_1:
	    		case UIPanel.TEMPLATE_TEXT_ELLIPSE_1:
		    		html += '<div id="' + id + '_body" style="padding-top:8px">';
		    		group.updateHTML = {bodyId:id + "_body"};		    		
	    			
	    			iMember = findGroupMember(group, "text");
	    			initTodo.push({g:group, i:iMember});    			
	    			html += '<div class="control_text_container" style="margin-left:7px">' + getControlHTML(group, iMember) + '</div>';
		    		html += '</div>';
		    		
	    			break;
	    		}
    		}
        }

		for(var i in groups)
        {
    		var group = groups[i]; 
    		var id = groupIdBase + i;	
    		var iMember;
    		
    		if(group.params.element.displayOptions.visibility == 0)
    		{
	    		switch(group.template)
	    		{
	        	case UIPanel.TEMPLATE_IMAGE_1:
	        		
		    		html += '<div id="' + id + '_body" style="padding-top:8px">';
		    		group.updateHTML = {bodyId:id + "_body"};		    		

	        		iMember = findGroupMember(group, "change image");
	    			initTodo.push({g:group, i:iMember});    			
	    	        
	    	        if(group.title)
	    	        {
		    			html += '<div class="control_label">' + htmlEncode(group.title) + '</div>';
	    	        }
	    	        	
	    			html += '<div class="control_inline_box" style="padding-left:8px;">' + getControlHTML(group, iMember) + '</div>';
	    			html += '</div>';

	    			break;
	    		}
	    		
    		}
        }


    	var container = $('#' + propContainerId);
        container.html(html);
        
        var makeTextOnFocusHandler = function(group)
        {
        	return function()
        	{
        		_system.setSelected(group.params.element);
        	};
        }; 
        
        for(var i in initTodo)
        {
        	initControl(initTodo[i].g, initTodo[i].i);
			var element = $("#" + initTodo[i].g.members[initTodo[i].i].getId());

    		switch(initTodo[i].g.template)
    		{
    		case UIPanel.TEMPLATE_TEXT_LINE_1:
    		case UIPanel.TEMPLATE_TEXT_CIRCLE_1:
    		case UIPanel.TEMPLATE_TEXT_ELLIPSE_1:
    			{    				 						
	    	    	elementButtonBars.push(new ButtonBar(initTodo[i].g));
	    	    	element.focus(makeTextOnFocusHandler(initTodo[i].g));
    			}
    		}

        	var tooltipText = initTodo[i].g.params.element.displayOptions.tooltip;
			if(tooltipText) makeTooltip(element, tooltipText, elementTooltips);
        }
    };
    
	this.clear = function()
	{
    	groups = [];
    	selectedGroup = null;
    	previousGroup = null;
    	updateHTML();
    };

    this.selectGroup = function(group)
    {
        if(selectedGroup === group) return;
        
        previousGroup = selectedGroup;
        selectedGroup = group;

		var bbar = getButtonBar(selectedGroup);
		if(bbar) bbar.show();
		
    	for(var i in elementButtonBars)
    	{
    		if(elementButtonBars[i] !== bbar)
    		{
    			elementButtonBars[i].hide();
    		}
    	}        
    };
    
    this.addGroup = function(group, suppressUpdate)
    {
    	group.uiPannel = this;
        groups.push(group);
    	if(!suppressUpdate) updateHTML();
    }
    
    this.removeGroup = function(group)
    {
    	for(i in groups)
    	{
    		if(groups[i] === group)
    		{
    			groups[i].uiPannel = null;
    			groups.splice(i,1);
		    	updateHTML();
				return true;
    		}
    	}
    	return false;
    }
}

UIPanel.TEMPLATE_TEXT_LINE_1 = "TextLine_1";
UIPanel.TEMPLATE_TEXT_CIRCLE_1 = "TextCircle_1";
UIPanel.TEMPLATE_TEXT_ELLIPSE_1 = "TextEllipse_1";
UIPanel.TEMPLATE_BORDER_CIRCLE_1 = "BorderCircle_1";
UIPanel.TEMPLATE_BORDER_ELLIPSE_1 = "BorderEllipse_1";
UIPanel.TEMPLATE_BORDER_RECTANGLE_1 = "BorderRectangle_1";
UIPanel.TEMPLATE_IMAGE_1 = "Image_1";
UIPanel.TEMPLATE_LINE_1 = "Line_1";


