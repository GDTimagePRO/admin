//TODO: this stuff needs better update notification callback support
//TODO: add support for group selection
//TODO: Make this control keep a reference to a selector containing its container and only call find on that selected instead of searching the entire DOM
//TODO: Make the state save if the changed input still does not have focus 100 ms after it loses it in order to account for the slider. Remember to mark the input as dirty.
//TODO: The arrows and the scroll wheel should also allow the user the increase / decrease the value of a numeric input
//TODO: Clicking on the title should select the element.
//TODO: The title should have some sort of a marker to indicate which element is selected.
//TODO: When a element is selected it would be nice if we scroll it into view (this is very very low priority)


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

    var introMessageDestructor = null;

    var makeIntroMessage = function()
    {
        var container = $('#' + propContainerId);
        container.html(
            '<div id="' + propContainerId + '_a1" style="float:right;width:40px;"></div>' +
            '<div id="' + propContainerId + '_a2" style="float:right;position:relative;top:160px;left:320px"></div>'
        );

        var anchor = container.find('#' + propContainerId + '_a1');
        anchor.qtip({
            content: 'You can use the "Add Element" button to add text, borders, and images to your design.',
            position: { corner: { tooltip: "topRight", target: "bottomLeft" } },
            show: { delay: 400, when: false, ready: true },
            hide: false,
            style: {
                title: { background: '#D0E9F5', color: '#5E99BD' },
                border: { width: 8, radius: 8, color: '#ADD9ED' },

                background: '#EFF6FE',
                color: '#362B36',
                padding: 10,
                textAlign: 'center',
                tip: true,
            }
        });

        var introMessage = anchor.qtip("api");
        introMessage.onFocus = function()
        {
            $('.qtip').css('z-index','100');
        };

        var anchor2 = container.find('#' + propContainerId + '_a2');
        anchor2.qtip({
            content: "Once you have added one or more element to your design, you can click here to change which one is selected. You can also drag and drop the selected element to change it's position and size.",
            position: { corner: { tooltip: "bottomRight", target: "topLeft" } },
            show: { delay: 400, when: false, ready: true },
            hide: false,
            style: {
                title: { background: '#D0E9F5', color: '#5E99BD' },
                border: { width: 8, radius: 8, color: '#ADD9ED' },

                background: '#EFF6FE',
                color: '#362B36',
                padding: 10,
                textAlign: 'center',
                tip: true,
            }
        });

        canvasMessage = anchor2.qtip("api");
        canvasMessage.onFocus = function()
        {
            $('.qtip').css('z-index','100');
        };

        introMessageDestructor = function()
        {
            introMessage.destroy();
            canvasMessage.destroy();
        };

    };

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
                //name: 'cream' // Style it according to the preset 'cream' style

                //classes: { tooltip: 'qtip-blue' }
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




    var templates = {};
    var tooltips = {
        "X"                        : "Horizontal start position relative to center of design canvass.",
        "Y"                        : "Vertical start position relative to center of design canvass.",
        "Width"                    : "Width of image element relative to original.",
        "Height"                : "Height of image element relative to original image.",
        "Text"                    : "Text input field.",
        "textBold"                : "Bold text element feature On/Off.",
        "textItalic"            : "Italicize text element feature On/Off.",
        "textAlignment"            : "Horizontal text element alignment relative to center of canvass.",
        "font"                     : "Font type selection. ",
        "fontSize"                : "Font point size of element.",
        "fontShrinkToFit"        : "Autofit text to space available.",
        "textAngle"                : "Start point in degrees.",
        "textInverted"            : "Flip or invert text orientation.",
        "radius"                : "Element radius size selector.",

        "borderType"            : "Select border style.",

        "imageChange"            : "Replace current image with new image.",
    };

    templates[UIPanel.TEMPLATE_TEXT_LINE_1] = ['<div class="control_full_line_box"><div class="control_label" tooltip="Text">Text:</div><div class="control_text_container">#text#</div></div>' +
                                               '<div class="control_inline_box"><div class="control_label" tooltip="font">Font:</div><div class="control_long_list_container">#font#</div></div>' +
                                               '<div class="control_inline_box"><div class="control_label" tooltip="fontSize">Size:</div><div class="control_number_container">#size#</div></div>' +
                                               '<div class="control_inline_box"><div class="control_label" tooltip="textAngle">Angle:</div><div class="control_number_container">#angle#</div></div>'
                                               ,
                                               '<div class="control_inline_box"><div class="control_label" tooltip="textBold">Bold:</div><div class="control_checkbox_container">#bold#</div></div>' +
                                               '<div class="control_inline_box"><div class="control_label" tooltip="textItalic">Italic:</div><div class="control_checkbox_container">#italic#</div></div>' +
                                               '<div class="control_inline_box"><div class="control_label" tooltip="textAlignment">Alignment:</div><div class="control_checkbox_container">#alignment#</div></div>' +
                                               '<div class="control_inline_box"><div class="control_label" tooltip="textVAlignment">V Alignment:</div><div class="control_checkbox_container">#valignment#</div></div>' +
                                               '<div class="control_inline_box"><div class="control_label" tooltip="fontShrinkToFit">Shrink-to-fit:</div><div class="control_checkbox_container">#scale to fit#</div></div>' +
                                               '<div class="control_inline_box"><div class="control_label" tooltip="X">Horizontal:</div><div class="control_number_container">#centerX#</div></div>' +
                                               '<div class="control_inline_box"><div class="control_label" tooltip="Y">Vertical:</div><div class="control_number_container">#centerY#</div></div>' +
                                               '<div class="control_inline_box"><div class="control_label" tooltip="textTracking">Tracking:</div><div class="control_number_container">#srcScale#</div></div>' +
                                               '<div class="control_inline_box"><div class="control_label" tooltip="textFormat">Format:</div><div class="control_checkbox_container">#textFormat#</div></div>' +
                                               '<div class="control_inline_box"><div class="control_label" tooltip="textLength">Length:</div><div class="control_number_container">#length#</div></div>' +
                                               '<div class="control_inline_box"><div class="control_label" tooltip="fontMinSize">Min Size:</div><div class="control_number_container">#min size#</div></div>' +
                                               '<div class="control_inline_box"><div class="control_label" tooltip="colorFG">Color:</div><div class="control_long_list_container">#colorFG#</div></div>' +
                                               '<div class="control_inline_box"><div class="control_label" tooltip="visible">Visible:</div><div class="control_long_list_container">#visible#</div></div>' +
                                               '<div class="control_inline_box"><div class="control_label" tooltip="specchar">Special Characters:</div><div class="control_list_container"><spen>Left:</span>#specCharLeft#</div><div class="control_list_container"><span>Right:</span>#specCharRight#</div></div>'
                                               ];

    templates[UIPanel.TEMPLATE_TEXT_CIRCLE_1] = ['<div class="control_full_line_box"><div class="control_label" tooltip="Text">Text:</div><div class="control_text_container">#text#</div></div>' +
                                                 '<div class="control_inline_box"><div class="control_label" tooltip="font">Font:</div><div class="control_long_list_container">#font#</div></div>' +
                                                 '<div class="control_inline_box"><div class="control_label" tooltip="fontSize">Size:</div><div class="control_number_container">#size#</div></div>' +
                                                 '<div class="control_inline_box"><div class="control_label" tooltip="radius">Radius:</div><div class="control_number_container">#radius#</div></div>'
                                                 ,
                                                 '<div class="control_inline_box"><div class="control_label" tooltip="textBold">Bold:</div><div class="control_checkbox_container">#bold#</div></div>' +
                                                 '<div class="control_inline_box"><div class="control_label" tooltip="textItalic">Italic:</div><div class="control_checkbox_container">#italic#</div></div>' +
                                                 '<div class="control_inline_box"><div class="control_label" tooltip="textAlignment">Alignment:</div><div class="control_checkbox_container">#alignment#</div></div>' +
                                                 '<div class="control_inline_box"><div class="control_label" tooltip="textVAlignment">V Alignment:</div><div class="control_checkbox_container">#valignment#</div></div>' +
                                                 '<div class="control_inline_box"><div class="control_label" tooltip="textAngle">Angle:</div><div class="control_number_container">#angle#</div></div>' +
                                                 '<div class="control_inline_box"><div class="control_label" tooltip="textFanAngle">Fan Angle:</div><div class="control_number_container">#fan angle#</div></div>' +
                                                 '<div class="control_inline_box"><div class="control_label" tooltip="textInverted">Inverted:</div><div class="control_checkbox_container">#inverted#</div></div>' +
                                                 '<div class="control_inline_box"><div class="control_label" tooltip="fontShrinkToFit">Shrink-to-fit:</div><div class="control_checkbox_container"style="margin-right:2px";>#scale to fit#</div></div>' +
                                                 '<div class="control_inline_box"><div class="control_label" tooltip="textTracking">Tracking:</div><div class="control_number_container">#srcScale#</div></div>' +
                                                 '<div class="control_inline_box"><div class="control_label" tooltip="textFormat">Format:</div><div class="control_checkbox_container">#textFormat#</div></div>' +
                                                 '<div class="control_inline_box"><div class="control_label" tooltip="X">Horizontal:</div><div class="control_number_container">#centerX#</div></div>' +
                                                 '<div class="control_inline_box"><div class="control_label" tooltip="Y">Vertical:</div><div class="control_number_container">#centerY#</div></div>' +
                                                 '<div class="control_inline_box"><div class="control_label" tooltip="fontMinSize">Min Size:</div><div class="control_number_container">#min size#</div></div>' +
                                                 '<div class="control_inline_box"><div class="control_label" tooltip="colorFG">Color:</div><div class="control_long_list_container">#colorFG#</div></div>' +
                                                 '<div class="control_inline_box"><div class="control_label" tooltip="visible">Visible:</div><div class="control_long_list_container">#visible#</div></div>'+
                                                 '<div class="control_inline_box"><div class="control_label" tooltip="specchar">Special Characters:</div><div class="control_list_container"><spen>Left:</span>#specCharLeft#</div><div class="control_list_container"><span>Right:</span>#specCharRight#</div></div>'
                                                 ];

    templates[UIPanel.TEMPLATE_TEXT_ELLIPSE_1] = ['<div class="control_full_line_box"><div class="control_label" tooltip="Text">Text:</div><div class="control_text_container">#text#</div></div>' +
                                                  '<div class="control_inline_box"><div class="control_label" tooltip="font">Font:</div><div class="control_long_list_container">#font#</div></div>' +
                                                  '<div class="control_inline_box"><div class="control_label" tooltip="fontSize">Size:</div><div class="control_number_container">#size#</div></div>' +
                                                  '<div class="control_inline_box"><div class="control_label" tooltip="radius">Radius:</div><div class="control_number_container">#radius#</div></div>'
                                                  ,
                                                  '<div class="control_inline_box"><div class="control_label" tooltip="textBold">Bold:</div><div class="control_checkbox_container">#bold#</div></div>' +
                                                  '<div class="control_inline_box"><div class="control_label" tooltip="textItalic">Italic:</div><div class="control_checkbox_container">#italic#</div></div>' +
                                                  '<div class="control_inline_box"><div class="control_label" tooltip="textAlignment">Alignment:</div><div class="control_checkbox_container">#alignment#</div></div>' +
                                                  '<div class="control_inline_box"><div class="control_label" tooltip="textVAlignment">V Alignment:</div><div class="control_checkbox_container">#valignment#</div></div>' +
                                                  '<div class="control_inline_box"><div class="control_label" tooltip="textAngle">Angle:</div><div class="control_number_container">#angle#</div></div>' +
                                                  '<div class="control_inline_box"><div class="control_label" tooltip="textFanAngle">Fan Angle:</div><div class="control_number_container">#fan angle#</div></div>' +
                                                  '<div class="control_inline_box"><div class="control_label" tooltip="textInverted">Inverted:</div><div class="control_checkbox_container">#inverted#</div></div>' +
                                                  '<div class="control_inline_box"><div class="control_label" tooltip="fontShrinkToFit">Shrink-to-fit:</div><div class="control_checkbox_container">#scale to fit#</div></div><br>' +
                                                  '<div class="control_inline_box"><div class="control_label" tooltip="X">Horizontal:</div><div class="control_number_container">#centerX#</div></div>' +
                                                  '<div class="control_inline_box"><div class="control_label" tooltip="Y">Vertical:</div><div class="control_number_container" style="margin-right:80px";>#centerY#</div></div>' +
                                                  '<div class="control_inline_box"><div class="control_label" tooltip="textTracking">Tracking:</div><div class="control_number_container">#srcScale#</div></div>' +
                                                  '<div class="control_inline_box"><div class="control_label" tooltip="textFormat">Format:</div><div class="control_checkbox_container">#textFormat#</div></div>' +
                                                  '<div class="control_inline_box" style="margin-left:27px";><div class="control_label" tooltip="Width">Width:</div><div class="control_number_container"style="margin-right:6px";>#width#</div></div>' +
                                                  '<div class="control_inline_box"><div class="control_label" tooltip="Height">Height:</div><div class="control_number_container">#height#</div></div>' +
                                                  '<div class="control_inline_box"><div class="control_label" tooltip="fontMinSize">Min Size:</div><div class="control_number_container">#min size#</div></div>' +
                                                  '<div class="control_inline_box"><div class="control_label" tooltip="colorFG">Color:</div><div class="control_long_list_container">#colorFG#</div></div>' +
                                                  '<div class="control_inline_box"><div class="control_label" tooltip="visible">Visible:</div><div class="control_long_list_container">#visible#</div></div>' +
                                                  '<div class="control_inline_box"><div class="control_label" tooltip="specchar">Special Characters:</div><div class="control_list_container"><spen>Left:</span>#specCharLeft#</div><div class="control_list_container"><span>Right:</span>#specCharRight#</div></div>'
                                                  ];


    templates[UIPanel.TEMPLATE_BORDER_RECTANGLE_1] = ['<div class="control_inline_box"><div class="control_label" tooltip="borderType">Type:</div><div class="control_list_container">#pattern#</div></div>' +
                                                       '<div class="control_inline_box"><div class="control_label" tooltip="borderSize">Size:</div><div class="control_number_container">#size#</div></div>' +
                                                       '<div class="control_inline_box"><div class="control_label" tooltip="borderCornerRadius">Corner radius:</div><div class="control_number_container">#edge radius#</div></div>'
                                                       ,
                                                       '<div class="control_inline_box"><div class="control_label" tooltip="X">Horizontal:</div><div class="control_number_container">#centerX#</div></div>' +
                                                       '<div class="control_inline_box"><div class="control_label" tooltip="Y">Vertical:</div><div class="control_number_container" style="margin-right:80px";>#centerY#</div></div>' +
                                                       '<div class="control_inline_box" style="margin-left:27px";><div class="control_label" tooltip="Width">width:</div><div class="control_number_container" style="margin-right:8px";>#width#</div></div>' +
                                                       '<div class="control_inline_box"><div class="control_label" tooltip="Height">height:</div><div class="control_number_container">#height#</div></div>' +
                                                       '<div class="control_inline_box"><div class="control_label" tooltip="colorFG">color:</div><div class="control_long_list_container">#colorFG#</div></div>' +
                                                       '<div class="control_inline_box"><div class="control_label" tooltip="visible">Visible:</div><div class="control_long_list_container">#visible#</div></div>'
                                                       ];


    templates[UIPanel.TEMPLATE_BORDER_CIRCLE_1] = ['<div class="control_inline_box"><div class="control_label" tooltip="borderType">Type:</div><div class="control_list_container">#pattern#</div></div>' +
                                                   '<div class="control_inline_box"><div class="control_label" tooltip="borderSize">Size:</div><div class="control_number_container">#size#</div></div>' +
                                                   '<div class="control_inline_box"><div class="control_label" tooltip="radius">Radius:</div><div class="control_number_container">#radius#</div></div>'
                                                   ,
                                                   '<div class="control_inline_box"><div class="control_label" tooltip="X">Horizontal:</div><div class="control_number_container">#centerX#</div></div>' +
                                                   '<div class="control_inline_box"><div class="control_label" tooltip="Y">Vertical:</div><div class="control_number_container">#centerY#</div></div>' +
                                                   '<div class="control_inline_box"><div class="control_label" tooltip="colorFG">Color:</div><div class="control_long_list_container">#colorFG#</div></div>' +
                                                   '<div class="control_inline_box"><div class="control_label" tooltip="visible">Visible:</div><div class="control_long_list_container">#visible#</div></div>'
                                                   ];

    templates[UIPanel.TEMPLATE_BORDER_ELLIPSE_1] = ['<div class="control_inline_box"><div class="control_label" tooltip="borderType">Type:</div><div class="control_list_container">#pattern#</div></div>' +
                                                    '<div class="control_inline_box"><div class="control_label" tooltip="borderSize">Size:</div><div class="control_number_container" style="margin-right:70px";>#size#</div></div>'
                                                    ,
                                                    '<div class="control_inline_box"><div class="control_label" tooltip="X">Horizontal:</div><div class="control_number_container">#centerX#</div></div>' +
                                                    '<div class="control_inline_box"><div class="control_label" tooltip="Y">Vertical:</div><div class="control_number_container" style="margin-right:80px";>#centerY#</div></div>' +
                                                    '<div class="control_inline_box" style="margin-left:27px";><div class="control_label" tooltip="Width">Width:</div><div class="control_number_container" style="margin-right:6px";>#width#</div></div>' +
                                                    '<div class="control_inline_box"><div class="control_label" tooltip="Height">Height:</div><div class="control_number_container">#height#</div></div>' +
                                                    '<div class="control_inline_box"><div class="control_label" tooltip="colorFG">Color:</div><div class="control_long_list_container">#colorFG#</div></div>' +
                                                    '<div class="control_inline_box"><div class="control_label" tooltip="visible">Visible:</div><div class="control_long_list_container">#visible#</div></div>'
                                                    ];

    templates[UIPanel.TEMPLATE_IMAGE_1] = [ '<div class="control_inline_box"><div class="control_label" tooltip="X">Horizontal:</div><div class="control_number_container">#centerX#</div></div>' +
                                            '<div class="control_inline_box"><div class="control_label" tooltip="Y">Vertical:</div><div class="control_number_container">#centerY#</div></div>' +
                                            '<div class="control_inline_box"><div class="control_label" tooltip="Width">Width:</div><div class="control_number_container">#width#</div></div>' +
                                            '<div class="control_inline_box"><div class="control_label" tooltip="Height">Height:</div><div class="control_number_container">#height#</div></div>' +
                                            '<div class="control_inline_box" style="padding-left:20px;" tooltip="imageChange">#change image#</div>' +
                                            '<div class="control_inline_box"><div class="control_label" tooltip="maintainAspectRatio">Maintain aspect ratio:</div><div class="control_number_container" style="width:50px">#maintainAspectRatio#</div></div>' +
                                            '<div class="control_inline_box"><div class="control_label" tooltip="font">Visibility:</div><div class="control_long_list_container">#visibility#</div></div>'
                                            ,
                                            '<div class="control_inline_box"><div class="control_label" tooltip="visible">Visible:</div><div class="control_long_list_container">#visible#</div></div>' +
                                            '<div class="control_inline_box"><div class="control_label" tooltip="angle">Angle:</div><div class="control_number_container">#angle#</div></div>' +
                                            '<div class="control_inline_box"><div class="control_label" tooltip="size">Size:</div><div class="control_number_container">#size#</div></div>'
                                            ];

    templates[UIPanel.TEMPLATE_LINE_1] = [ '<div class="control_inline_box"><div class="control_label" tooltip="X">Horizontal:</div><div class="control_number_container">#centerX#</div></div>' +
                                           '<div class="control_inline_box"><div class="control_label" tooltip="Y">Vertical:</div><div class="control_number_container">#centerY#</div></div>' +
                                           '<div class="control_inline_box"><div class="control_label" tooltip="lineLength">Length:</div><div class="control_number_container">#length#</div></div><br>' +
                                           '<div class="control_inline_box"><div class="control_label" tooltip="lineType">Type:</div><div class="control_list_container">#pattern#</div></div>' +
                                           '<div class="control_inline_box"><div class="control_label" tooltip="lineSize">Size:</div><div class="control_number_container">#size#</div></div>' +
                                           '<div class="control_inline_box"><div class="control_label" tooltip="lineAngle">Angle:</div><div class="control_number_container">#angle#</div></div>' +
                                           '<div class="control_inline_box" style="padding-left:20px;" tooltip="lineHorizontalBtn">#horizontal#</div>&nbsp;&nbsp;' +
                                           '<div class="control_inline_box" style="padding-left:20px;" tooltip="lineVerticalBtn">#vertical#</div>'
                                           ,
                                           '<div class="control_inline_box"><div class="control_label" tooltip="colorFG">Color:</div><div class="control_long_list_container">#colorFG#</div></div>' +
                                           '<div class="control_inline_box"><div class="control_label" tooltip="visible">Visible:</div><div class="control_long_list_container">#visible#</div></div>'
                                           ];



    var groups = [];
    var selectedGroup = null;
    var previousGroup = null;

    this.onDeleteClick = null;


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
        case UIControl.TYPE_NUMBER:
            return '<input class="control_input" id="' + control.getId() + '" type="text" type="text">';

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


    var buildFromTemplate = function(group, i)
    {
        if(group.template == null) return "";
        var templateHTML = templates[group.template][i];

        for(var i in group.members)
        {
            templateHTML = templateHTML.replace("#" + group.members[i].getName() + "#" , getControlHTML(group, i));
        }

        return templateHTML;
    };

    var updateHTML = this.updateHTML = function()
    {
        verticalSlider.hide();
        removeTooltips(elementTooltips);

        var html = "";
        var groupIdBase = propContainerId + "_g";

        if(groups.length == 0)
        {
            if(introMessageDestructor == null) makeIntroMessage();
            return true;
        }
        else
        {
            if(introMessageDestructor != null)
            {
                introMessageDestructor();
                introMessageDestructor = null;
            }
        }

        for(var i in groups)
        {
            var group = groups[i];
            var params = group.params;
            var id = groupIdBase + i;

            html +=
                '<div id="' + id + '" class="control_group">'+
                '<div id="' + id + '_h"  class="control_group_header' + ((selectedGroup === group) ? "_selected" : "") + '">' +
                '<div class="control_group_title">' +
                htmlEncode(group.title ? group.title : params.type) +
                '</div>' +
                '<button id="' + id + '_d" class="control_group_delete_button">X</button>';

            html += '<img id="' + id + '_l" class="control_group_lock_button" src="' +
                    ((!group.params.element.getEditAllowMove()) ? 'images/lock.png">' : 'images/unlock.png">');

            if(group.template && (templates[group.template][1] != ""))
            {
                html += '<button id="' + id + '_m" class="control_group_more_button">' +
                    ((group.showMore) ? 'Less ...' : 'More ...') +
                    '</button>';
            }

            html +=
                '</div>'+
                '<div id="' + id + '_sn" class="control_group_section">' +
                buildFromTemplate(group, 0) +
                '</div><div id="' + id + '_sm" class="control_group_section"  style="display:' + (group.showMore ? 'block' : 'none') + '">' +
                buildFromTemplate(group, 1) +
                '</div></div>';
        }

        //.control_label
        var container = $('#' + propContainerId);
        container.html(html);//.control_label

        container.find('div[tooltip]').each(function(index, domEle){
            var tooltip = $(this).attr("tooltip");
            if(tooltip)
            {
                var tooltipText = tooltips[tooltip];
                makeTooltip($(this), tooltipText ? tooltipText : 'Tooltip "' + tooltip + '" does not exist', elementTooltips);
                $(this).attr("style","cursor:help");
            }
            return true;
        });


        var showExtraPropertiesDlg = function(group)
        {
            var html =
                '<div title="Element Options">' +
                '<table style="margin-left:auto; margin-right:auto; margin-top:15px;">' +
                '<tr><td><div tooltip="exo_01">Id :</div></td><td><input id="exo_id" type="text" size="40"></td></tr>' +
                '<tr><td><div tooltip="exo_02">Description :</div></td><td><input id="exo_description" type="text" style="width:400px;"></td></tr>' +
                '<tr><td style="vertical-align:text-top; padding-top:7px;"><div tooltip="exo_03">Tooltip text :</div></td><td><textarea id="exo_tooltip" style="width:400px;"></textarea></td></tr>' +
                '<tr><td><div tooltip="exo_04">Max size :</div></div></td><td><input id="exo_max_size" type="text" size="10"></td></tr>' +
                '<tr><td><div tooltip="exo_05">Visibility :</div></td><td><select id="exo_visibility">'+
                    '<option value="0">Always visible</option>'+
                    '<option value="1">Hide on templates</option>'+
                '</td></tr>' +
                '<tr><td><div tooltip="exo_07">Element Config:</div></div></td><td><input id="exo_element_config" type="text"></td></tr>' +
                '<tr><td style="vertical-align:text-top; padding-top:7px;"><div tooltip="exo_06">Script :</div></td><td><textarea id="exo_script" style="width:400px;height:190px"></textarea></td></tr>' +
                '</table>' +
                '</div>';

            var dlg = $(html);
            var dopt = group.params.element.displayOptions;
            dlg.find('#exo_id').val(group.params.element.id);
            dlg.find('#exo_description').val(group.title);
            dlg.find('#exo_tooltip').val(dopt.tooltip);
            dlg.find('#exo_max_size').val(dopt.maxSize);
            dlg.find('#exo_visibility').val(dopt.visibility);
            dlg.find('#exo_script').val(group.params.element.scriptContainer.getSource());
            dlg.find('#exo_element_config').val($.toJSON(group.params.element.config));


            var tooltipList = [];
            dlg.find('div[tooltip]').each(function(index, domEle){
                var tooltip = $(this).attr("tooltip");
                if(tooltip)
                {
                    var tooltipText = tooltips[tooltip];
                    makeTooltip($(this), tooltipText ? tooltipText : 'Tooltip "' + tooltip + '" does not exist', tooltipList);
                    $(this).attr("style","cursor:help");
                }
                return true;
            });

            dlg.appendTo(document.body);

            dlg.dialog({
                resizable: false,
                height:500,
                width:600,
                modal: true,
                buttons: {
                    "Ok": function() {
                        var maxSize = dlg.find('#exo_max_size').val();

                        if(!$.isNumeric(maxSize))
                        {
                            alert("Max size must be a numeric value.");
                            return;
                        }

                        group.params.element.id = dlg.find('#exo_id').val();
                        group.title = dlg.find('#exo_description').val();
                        dopt.tooltip = dlg.find('#exo_tooltip').val();
                        dopt.maxSize = parseInt(maxSize);
                        if(dopt.maxSize < 0) dopt.maxSize = 0;
                        dopt.visibility = parseInt(dlg.find('#exo_visibility').val());
                        group.params.element.scriptContainer.setSource(dlg.find('#exo_script').val());
                        group.params.element.config = jQuery.parseJSON(dlg.find('#exo_element_config').val());

                        _system.saveState();
                        me.updateHTML();
                        $( this ).dialog( "close" );
                    },
                    "Cancel": function() {
                        $( this ).dialog( "close" );
                    }
                },
                close: function() {
                    $(dlg).remove();
                    removeTooltips(tooltipList);
                },
            });
        };

        var createOnTitleDblClickHandler = function(i)
        {
            return function()
            {
                showExtraPropertiesDlg(groups[i]);

//                var newValue = prompt('Change element title', group.title);
//                if (newValue !== null)
//                {
//                    group.title = newValue;
//                    _system.saveState();
//                    me.updateHTML();
//                }
                //_system.setSelected(group.params.element);
            };
        };


        var createOnMoreHandler = function(i)
        {
            var group = groups[i];
            return function()
            {
                var id = propContainerId + "_g" + i + '_sm';
                if(group.showMore)
                {
                    _system.setSelected(group.params.element);
                    $(this).find('span').text('More ...');
                    $('#' + id).css('display','none');
                    group.showMore = false;
                }
                else
                {
                    _system.setSelected(group.params.element);
                    $(this).find('span').text('Less ...');
                    $('#' + id).css('display','block');
                    group.showMore = true;
                }
                _system.saveState();
            };
        };

        var createOnDeleteHandler = function(i)
        {
            var group = groups[i];
            return function()
            {
                if(me.onDeleteClick)
                {
                    me.onDeleteClick(
                        group.title ? group.title : group.params.type,
                        group.params.element
                    );
                }
            };
        };

        var createOnLockHandler = function(i)
        {
            var group = groups[i];
            var id = groupIdBase + i;

            return function()
            {
                if(group.params.element.getEditAllowMove())
                {
                    _system.setSelected(group.params.element);
                    $("#" + id + "_l").attr( 'src', 'images/lock.png' );
                    group.params.element.setEditAllowMove(!group.params.element.getEditAllowMove());
                }
                else
                {
                    _system.setSelected(group.params.element);
                    $("#" + id + "_l").attr( 'src', 'images/unlock.png' );
                    group.params.element.setEditAllowMove(!group.params.element.getEditAllowMove());
                }
                _system.saveState();
            };
        };

        for(var i in groups)
        {
            var group = groups[i];
            var id = groupIdBase + i;


            $("#" + id + "_h").dblclick(createOnTitleDblClickHandler(i));


            var moreButton = $("#" + id + "_m");
            if(moreButton.length > 0)
            {
                makeTooltip(moreButton, "More / less editing features", elementTooltips);
                moreButton.button().click(createOnMoreHandler(i));
            }
            var deleteButton = $("#" + id + "_d");
            makeTooltip(deleteButton,"Delete element", elementTooltips);
            deleteButton.button().click(createOnDeleteHandler(i));

            var lockButton = $("#" + id + "_l");
            makeTooltip(lockButton,"Lock / unlock element position and properties", elementTooltips);
            lockButton.button().click(createOnLockHandler(i));

            for(var ii in group.members)
            {
                initControl(group, ii);
            }
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

        var groupIdBase = propContainerId + "_g";

        for(var i in groups)
        {
            var id = groupIdBase + i;
            var newGroup = groups[i];

            if (selectedGroup === newGroup)
            {
                //document.getElementById(id + "_sn").setAttribute('style', 'background-color:#3dabe3' );
                document.getElementById(id + "_h").setAttribute('class', 'control_group_header_selected' );
            }
            else if (previousGroup === newGroup)
            {
                //document.getElementById(id + "_sn").setAttribute('style', 'background-color:#3dabe3' );
                document.getElementById(id + "_h").setAttribute('class', 'control_group_header' );
            }
        }
        //TODO: test
    };

    this.addGroup = function(group, suppressUpdate)
    {
        group.uiPannel = this;
        groups.push(group);
        if(!suppressUpdate) updateHTML();
    };

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
    };
}

UIPanel.TEMPLATE_TEXT_LINE_1 = "TextLine_1";
UIPanel.TEMPLATE_TEXT_CIRCLE_1 = "TextCircle_1";
UIPanel.TEMPLATE_TEXT_ELLIPSE_1 = "TextEllipse_1";
UIPanel.TEMPLATE_BORDER_CIRCLE_1 = "BorderCircle_1";
UIPanel.TEMPLATE_BORDER_ELLIPSE_1 = "BorderEllipse_1";
UIPanel.TEMPLATE_BORDER_RECTANGLE_1 = "BorderRectangle_1";
UIPanel.TEMPLATE_IMAGE_1 = "Image_1";
UIPanel.TEMPLATE_LINE_1 = "Line_1";