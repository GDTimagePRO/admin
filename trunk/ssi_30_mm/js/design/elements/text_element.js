//TODO: Make the hit text just use a collision box so me it does not have size worth of paddint at the sides

function TextElement()
{
    this.common_init();
    this.className = "TextElement";

    this.scriptContainer.addBinding("onFilter");
    this.scriptContainer.addBinding("onSet");
    this.scriptContainer.addBinding("onGet");
    this.scriptContainer.addBinding("onInit");
    this.scriptContainer.addBinding("onCustomUI");


    var me = this;
    var currentScene = null;
    this.drawable = new PatternMapDrawable(null, null, 20, 1.0, null);
    this.highlightA = new PatternMapDrawable(null, new PatternHighlight(0,0), this.drawable.size, 1.0, "$highlight");
    var highlightB = new PatternMapDrawable(null, null, this.drawable.size, 1.0, "$highlight");
    var isSelected = false;

    var text = "";
    var font = "fonts/verdana.ttf";
    var alignment = TextElement.ALIGN_CENTRE;
    var scaleToFit = false;
    var inverted = false;
    var bold = false;
    var italic = false;
    var fanAngle = Math.PI * 2;
    var srcScale = 1.0;
    var minSize = 15.0;
    var verticalAlignment = 1;
    var specLeft = '';
    var specRight = '';

    var textFormat = TextElement.FORMAT_NONE;


    var textType = TextElement.TYPE_ELLIPSE;
    this.widget = new RectangleWidget(0, 0, 0, 0, 0, null, null);
    this.widget.angle = 0;
    this.autoResize = false;

    this.widget.visible = false;
    this.widget.setAngleVisible(true);
    this.highlightA.visible = false;
    highlightB.visible = false;

    this.highlightA.displayGroup = Scene.DISPLAY_GROUP_UI;
    highlightB.displayGroup = Scene.DISPLAY_GROUP_UI;

    var uiControlGroup = null;
    this.getUIControlGroup = function() { return uiControlGroup; };


    var updateControlTemplate = function()
    {
        var type = me.getType();

        if(type == TextElement.TYPE_LINE)
        {
            me.getUIControlGroup().template = UIPanel.TEMPLATE_TEXT_LINE_1;
        }
        else if(type == TextElement.TYPE_CIRCLE)
        {
            me.getUIControlGroup().template = UIPanel.TEMPLATE_TEXT_CIRCLE_1;
        }
        else if(type == TextElement.TYPE_ELLIPSE)
        {
            me.getUIControlGroup().template = UIPanel.TEMPLATE_TEXT_ELLIPSE_1;
        }
    };


    var oldAngle = 0;
    var oldPosition = null;
    this.widget.onRelease = function(sender)
    {
        var newPosition = me.getPosition();
        if(    (oldPosition == null) ||
            (oldPosition.x1 != newPosition.x1) ||
            (oldPosition.y1 != newPosition.y1) ||
            (oldPosition.x2 != newPosition.x2) ||
            (oldPosition.y2 != newPosition.y2) ||
            (this.angle != oldAngle))
        {
            oldPosition = newPosition;
            oldAngle = this.angle;
            _system.setStateDirty();
        }
    };

    this.widget.onChange = function(sender)
    {
        me.setPosition(
            sender.x1,
            sender.y1,
            sender.x2,
            sender.y2,
            sender.angle,
            true
        );

        uiControlGroup.updateControl("angle");
        uiControlGroup.updateControl("centerX");
        uiControlGroup.updateControl("centerY");
        uiControlGroup.updateControl("width");
        uiControlGroup.updateControl("height");
        uiControlGroup.updateControl("radius");
        uiControlGroup.updateControl("length");
    };

    this.widget.hitTest = function(params)
    {
        if((textType == TextElement.TYPE_ELLIPSE) || (textType == TextElement.TYPE_CIRCLE))
        {
            var lx = params.x - Math.min(this.x1, this.x2);
            var ly = params.y - Math.min(this.y1, this.y2);
            var width = Math.abs(this.x2 - this.x1);
            var height = Math.abs(this.y2 - this.y1);

            if((width > 0) && (height > 0))
            {
                var centerX = width / 2;
                var centerY = height / 2;
                var maxR = Math.min(centerX, centerY);
                var mx = 1;
                var my = 1;

                if(textType != TextElement.TYPE_CIRCLE)
                {
                    if(width > height) mx = height / width;
                    else my = width / height;
                }

                var trueAngle = this.angle % (2*Math.PI);
                var pointAngle = Math.atan2(ly-centerY, lx-centerX);
                if (pointAngle < 0) pointAngle += Math.PI*2;
                var trueFan = fanAngle / 2;
                var poffset = 0;
                var noffset = 0;
                if (trueAngle + trueFan > Math.PI*2) poffset = (trueAngle + trueFan) % (Math.PI*2);
                if (trueAngle - trueFan < 0) noffset = (trueAngle - trueFan) * -1;
                var r = Math.sqrt(Math.pow((lx - centerX) * mx, 2) + Math.pow((ly - centerY) * my, 2));
                if(((r <= maxR + 5) && (r >= maxR - me.drawable.size - 5)) && (((pointAngle >= trueAngle - trueFan + noffset) || (pointAngle <= poffset)) && ((pointAngle <= (trueAngle + trueFan - poffset)) || (pointAngle >= Math.PI*2 - noffset)))) return true;
            }
        }
        else
        {
            var size = me.drawable.size;
            var topLeft = this.getTopLeft();
            if((params.x < topLeft.x - size) || (params.y < topLeft.y - size)) return false;

            var bottomRight = this.getBottomRight();
            if((params.x > bottomRight.x + size) || (params.y > bottomRight.y + size)) return false;
            var w1 = params.x-this.x1;
            var h1 = params.y-this.y1;

            var a = Math.atan2(this.y2-this.y1, this.x2-this.x1) - Math.atan2(h1, w1);
            var h = Math.sqrt(w1*w1 + h1*h1);
            var d = Math.sin(a) * h;

            return (d >=0) && (d < size);
        }

        return false;
    };

    this.widget.onSelect = function()
    {
        _system.setSelected(me);
    };

    this.setTextColor = function(value)
    {
        this.drawable.setFGColor(value);
    };

    this.getAngle = function()
    {
        varAngle = this.widget.angle + Math.PI;
        if(varAngle > Math.PI * 2) varAngle -= Math.PI * 2;
        return varAngle;
    };
    this.setAngle = function(angle)
    {
        this.setPosition(this.widget.x1, this.widget.y1, this.widget.x2, this.widget.y2, angle + Math.PI);
    };

    this.getFGColor = function() { return this.drawable.getFGColor(); };
    this.setFGColor = function(value) { this.drawable.setFGColor(value); };

    this.getVisible = function() { return this.drawable.visible; };
    this.setVisible = function(value) { this.drawable.visible = value; };
  
    this.getSpecLeft = function() { return typeof(this.specLeft) !== 'undefined' ? this.specLeft : ''; };
    this.setSpecLeft = function(value) { this.specLeft = value; };
  
    this.getSpecRight = function() { return typeof(this.specRight) !== 'undefined' ? this.specRight : ''; };
    this.setSpecRight = function(value) { this.specRight = value; };

    this.getFanAngle = function() { return fanAngle; };
    this.setFanAngle = function(angle)
    {
        if(!angle) angle = Math.PfanAngleI * 2;
        if(angle < 0.5) angle =  0.5;
        if(angle > Math.PI * 2) angle = Math.PI * 2;
        fanAngle = angle;
        this.setPosition(this.widget.x1, this.widget.y1, this.widget.x2, this.widget.y2);
    };

    this.getSelected = function(){ return isSelected; };
    this.setSelected = function(value)
    {
        isSelected = value;
        this.widget.visible = value;
        this.highlightA.visible = value;
        highlightB.visible = value;
    };

    this.getEditAllowMove = function() { return this.widget.getEditAllowMove(); };
    this.setEditAllowMove = function(value)
    {
        this.widget.setEditAllowMove(value);
        if(currentScene)currentScene.redraw();
    };

    this.getText = function(){ return text; };
    this.setText = function(newText, newFont, newInverted)
    {
        if(newText !== undefined) {
            newText = newText.replace('‘', "'").replace('’', "'").replace('”', '"').replace('“', '"');
        }

        if(newText !== undefined) text = newText;
        if(newFont) font = newFont;
        if(newInverted !== undefined) inverted = newInverted;

        if (text)
        {
            var formattedText;
            if(textFormat == TextElement.FORMAT_UPPER_CASE)
            {
                formattedText = text.toUpperCase();
            }
            else if(textFormat == TextElement.FORMAT_LOWER_CASE)
            {
                formattedText = text.toLowerCase();
            }
            else
            {
                formattedText = text;
            };

            if(this.scriptContainer.onFilter)
            {
                formattedText = this.scriptContainer.onFilter(formattedText);
            }

            if (this.getSpecLeft() != '') {
            	formattedText = this.getSpecLeft() + formattedText;
            }
          
          	if (this.getSpecRight() != '') {
            	formattedText = formattedText + this.getSpecRight();
            }
            
            this.drawable.pattern = new TextPattern( formattedText, font, scaleToFit, inverted, bold, italic, minSize, alignment, verticalAlignment);
            this.drawable.displayGroup = Scene.DISPLAY_GROUP_ANY;
        }
        else
        {
            var formattedText = uiControlGroup.title ? uiControlGroup.title : "New text ...";
            if (this.getSpecLeft() != '') {
            	formattedText = this.getSpecLeft() + formattedText;
            }
          
          	if (this.getSpecRight() != '') {
            	formattedText = formattedText + this.getSpecRight();
            }
            this.drawable.pattern = new TextPattern(formattedText, font, scaleToFit, inverted, bold, italic, minSize, alignment, verticalAlignment);
            this.drawable.displayGroup = Scene.DISPLAY_GROUP_ANY;
            //this.drawable.displayGroup = Scene.DISPLAY_GROUP_UI;
        }
        highlightB.pattern = this.drawable.pattern;
        uiControlGroup.updateControl("text");
    };

    this.getTextFormat = function(){ return textFormat; };
    this.setTextFormat = function(value)
    {
        value = parseInt(value);
        if(!isNaN(value) && isFinite(value))
        {
            textFormat = value;
            this.setText();
        }
    };


    this.getBold = function(){ return bold; };
    this.setBold = function(newBold)
    {
        bold = newBold;
        this.setText();
    };

    this.getItalic = function(){ return italic; };
    this.setItalic = function(newItalic)
    {
        italic = newItalic;
        this.setText();
    };

    this.getFont = function(){ return font; };
    this.setFont = function(newFont)
    {
        this.setText(text, newFont);
    };

    this.getAlignment = function() { return alignment; };
    this.setAlignment = function(newAlignment)
    {
        alignment = newAlignment;
        this.setText();
    };

    this.getVAlignment = function() { return verticalAlignment; };
    this.setVAlignment = function(value)
    {
        value = parseInt(value);
        if(!isNaN(value) && isFinite(value))
        {
            verticalAlignment = value;
            this.setText();
        }
    };

    this.getScaleToFit = function(){ return scaleToFit; };
    this.setScaleToFit = function(newScaleToFit)
    {
        scaleToFit = newScaleToFit;
        this.setText();
    };

    this.getInverted = function() { return inverted; };
    this.setInverted = function(newInverted)
    {
        this.setText(text, font, newInverted);
    };

    this.getType = function() { return textType; };
    this.setType = function(newType)
    {
        if((newType == TextElement.TYPE_ELLIPSE) || (newType == TextElement.TYPE_CIRCLE))
        {
            this.widget.setAngleVisible(true);

            this.widget.setPointAllowVisible("topMiddle", true);
            this.widget.setPointAllowVisible("topRight", true);
            this.widget.setPointAllowVisible("middleLeft", true);
            this.widget.setPointAllowVisible("middleMiddle", true);
            this.widget.setPointAllowVisible("middleRight", true);
            this.widget.setPointAllowVisible("bottomMiddle", true);
            this.widget.setPointAllowVisible("bottomLeft", true);
            this.widget.setPointAllowVisible("angle", true);
        }
        else
        {
            this.widget.setAngleVisible(false);

            this.widget.setPointAllowVisible("topMiddle", false);
            this.widget.setPointAllowVisible("topRight", false);
            this.widget.setPointAllowVisible("middleLeft", false);
            this.widget.setPointAllowVisible("middleMiddle", false);
            this.widget.setPointAllowVisible("middleRight", false);
            this.widget.setPointAllowVisible("bottomMiddle", false);
            this.widget.setPointAllowVisible("bottomLeft", false);
            this.widget.setPointAllowVisible("angle", false);
        }

        if(textType == newType) return;
        textType = newType;
        updateControlTemplate();

        if(this.drawable.map != null)
        {
            this.setPosition(this.widget.x1, this.widget.y1, this.widget.x2, this.widget.y2);
        }
    };


    this.setPosition = function(x1, y1, x2, y2, angle, fromWidget)
    {
        this.widget.x1 = x1;
        this.widget.y1 = y1;
        this.widget.x2 = x2;
        this.widget.y2 = y2;

        if((textType == TextElement.TYPE_ELLIPSE) || (textType == TextElement.TYPE_CIRCLE))
        {
            if(angle) this.widget.angle = angle;

            var width = Math.abs(x1 - x2);
            var height = Math.abs(y1 - y2);

            if((textType == TextElement.TYPE_CIRCLE) || (width == height))
            {
                this.drawable.map = new CircleMap(
                    (x1 + x2) / 2,
                    (y1 + y2) / 2,
                    this.widget.angle - fanAngle / 2,
                    this.widget.angle + fanAngle / 2,
                    (width < height ? width : height) / 2 - this.drawable.size,
                    0,
                    srcScale
                );
            }
            else
            {
                this.drawable.map = new EllipseMap(
                    Math.min(x1,x2) + this.drawable.size,
                    Math.min(y1,y2) + this.drawable.size,
                    width - this.drawable.size * 2,
                    height - this.drawable.size * 2,
                    this.widget.angle - fanAngle / 2,
                    this.widget.angle + fanAngle / 2,
                    srcScale
                );
            }
        }
        else
        {
            this.drawable.map = new LineMap(
                x1, y1, x2, y2,
                0, srcScale
            );
            if (this.autoResize) {
                var t = this.getText();
                if (!t || t == "") {
                    t = this.getUIControlGroup().title;
                }
                if (t) {
                    var dist = Math.sqrt((x2-x1)*(x2-x1) + (y2-y1)*(y2-y1));
                    var len = t.length;
                    var ctx = _system.scene.canvas.getContext('2d');
                    var fontsize = Math.round(dist / len) + 10  - Math.round(dist / len)%10;
                    var fontPrepend = "";
                    if (this.getBold()) {
                        fontPrepend += "bold ";
                    }
                    if (this.getItalic()) {
                        fontPrepend += "italic ";
                    }
                    ctx.font=fontPrepend + fontsize+"px '"+this.getFont() + "'";
                    while(ctx.measureText(t).width<dist) {
                        fontsize+=1;
                        ctx.font=fontsize+"px '"+this.getFont() + "'";
                    }
                    while(ctx.measureText(t).width>dist){
                        fontsize-=1;
                        ctx.font=fontsize+"px '"+this.getFont() + "'";
                    }
                    this.setSize(fontsize);
                }
            }
        }

        this.highlightA.map = this.drawable.map;
        highlightB.map = this.drawable.map;

        if(!fromWidget)
        {
            oldAngle = angle;
            oldPosition = this.getPosition();
        }
    };

    this.getPosition = function()
    {
        return {
            x1: this.widget.x1,
            y1: this.widget.y1,
            x2: this.widget.x2,
            y2: this.widget.y2,
            angle : this.widget.angle
        };
    };

    this.getSize = function() { return this.drawable.size; };
    this.setSize = function(value)
    {
        value = parseFloat(value);
        if(!isNaN(value) && isFinite(value))
        {
            if(value < 8) value = 8;
            if(value > 9000) value = 9000;
            if(this.drawable.size == value) return;

            this.drawable.size = value;
            this.highlightA.size = this.drawable.size;
            highlightB.size = this.drawable.size;
            this.setPosition(this.widget.x1, this.widget.y1, this.widget.x2, this.widget.y2);
        }
    };

    this.getMinSize = function() { return minSize; };
    this.setMinSize = function(value)
    {
        value = parseFloat(value);
        if(!isNaN(value) && isFinite(value))
        {
            if(value < 8) value = 8;
            if(value > 9000) value = 9000;
            if(minSize == value) return;

            minSize = value;
            this.setText();
        }
    };

    this.getSrcScale = function() { return srcScale; };
    this.setSrcScale = function(value)
    {
        value = parseFloat(value);
        if(!isNaN(value) && isFinite(value))
        {
            srcScale = value;
            if(srcScale < 0.2) srcScale = 0.2;
            this.setPosition(this.widget.x1, this.widget.y1, this.widget.x2, this.widget.y2);
        }
    };



    this.setScene = function(scene)
    {
        if(currentScene)
        {
            currentScene.getLayer(Scene.LAYER_WIDGETS).remove(this.widget);
            //currentScene.getLayer(Scene.LAYER_BACKGROUND).remove(highlightB);
            //currentScene.getLayer(Scene.LAYER_BACKGROUND).remove(this.highlightA);
            currentScene.getLayer(Scene.LAYER_FOREGROUND).remove(this.highlightA);
            currentScene.getLayer(Scene.LAYER_FOREGROUND).remove(this.drawable);
            currentScene = null;
        }

        if(scene)
        {
            scene.getLayer(Scene.LAYER_WIDGETS).add(this.widget);
            //scene.getLayer(Scene.LAYER_BACKGROUND).add(highlightB);
            //scene.getLayer(Scene.LAYER_BACKGROUND).add(this.highlightA);
            scene.getLayer(Scene.LAYER_FOREGROUND).add(this.highlightA);
            scene.getLayer(Scene.LAYER_FOREGROUND).add(this.drawable);

            currentScene = scene;
        }
    };


    this.createUI = function()
    {
        uiControlGroup = new UIControlGroup({
            type:"Text",
            element: this
        });

        uiControlGroup.title = null;

        updateControlTemplate();

        uiControlGroup.addControl(
            "text",
            UIControl.TYPE_TEXT,
            {
                onGet : function()
                {
                    if(me.scriptContainer.onGet)
                    {
                        return me.scriptContainer.onGet();
                    }
                    else return text;

                },
                onSet : function(value)
                {
                    if(me.scriptContainer.onSet)
                    {
                        me.scriptContainer.onSet(value);
                    }
                    else me.setText(value);

                    if(currentScene)currentScene.redraw();
                }
            }
        );

        uiControlGroup.addControl(
            "size",
            UIControl.TYPE_NUMBER,
            {
                minValue : 10,
                maxValue : 200,
                onGet : function() { return me.getSize(); },
                onSet : function(value)
                {
                    me.setSize(value);
                    if(currentScene)currentScene.redraw();
                }
            }
        );

        uiControlGroup.addControl(
            "min size",
            UIControl.TYPE_NUMBER,
            {
                minValue : 10,
                maxValue : 200,
                onGet : function() { return me.getMinSize(); },
                onSet : function(value)
                {
                    me.setMinSize(value);
                    if(currentScene)currentScene.redraw();
                }
            }
        );

        uiControlGroup.addControl(
            "scale to fit",
            UIControl.TYPE_LIST,
            {
                items : [{name:"Yes"}, {name:"No"}],
                onGet : function() { return scaleToFit ? 0 : 1; },
                onSet : function(index, item)
                {
                    me.setScaleToFit(index == 0);
                    if(currentScene)currentScene.redraw();
                }
            }
        );

        uiControlGroup.addControl(
            "inverted",
            UIControl.TYPE_LIST,
            {
                items : [{name:"Yes"}, {name:"No"}],
                onGet : function() { return inverted ? 0 : 1; },
                onSet : function(index, item)
                {
                    me.setInverted(index == 0);
                    if(currentScene)currentScene.redraw();
                }
            }
        );

        uiControlGroup.addControl(
            "bold",
            UIControl.TYPE_LIST,
            {
                items : [{name:"Yes"}, {name:"No"}],
                onGet : function() { return bold ? 0 : 1; },
                onSet : function(index, item)
                {
                    me.setBold(index == 0);
                    if(currentScene)currentScene.redraw();
                }
            }
        );

        uiControlGroup.addControl(
            "italic",
            UIControl.TYPE_LIST,
            {
                items : [{name:"Yes"}, {name:"No"}],
                onGet : function() { return italic ? 0 : 1; },
                onSet : function(index, item)
                {
                    me.setItalic(index == 0);
                    if(currentScene)currentScene.redraw();
                }
            }
        );

        uiControlGroup.addControl(
            "font",
            UIControl.TYPE_LIST,
            {
                items : TextElement.FONTS,
                onGet : function() {
                    var lcFont = font.toLowerCase();
                    for(var i in TextElement.FONTS)
                    {
                        var iFont = TextElement.FONTS[i];
                        var iLegacyId = iFont.legacyId;
                        if((iFont.id.toLowerCase() == lcFont) || (iLegacyId && iLegacyId.toLowerCase() == lcFont)) return i;
                    }
                    alert("Selected font was not found : " + font);
                },
                onSet : function(index, item)
                {
                    me.setFont(item.id);
                    if(currentScene)currentScene.redraw();
                }
            }
        );

        uiControlGroup.addControl(
            "alignment",
            UIControl.TYPE_LIST,
            {
                items : [{name:"Centre"}, {name:"Left"}, {name:"Right"}],
                onGet : function() { return alignment; },
                onSet : function(index, item)
                {
                    me.setAlignment(index);
                    if(currentScene)currentScene.redraw();
                }
            }
        );

        uiControlGroup.addControl(
                "textFormat",
                UIControl.TYPE_LIST,
                {
                    items : [{name:"None"}, {name:"Upper case"}, {name:"Lower case"}],
                    onGet : function() { return me.getTextFormat(); },
                    onSet : function(index, item)
                    {
                        me.setTextFormat(index);
                        if(currentScene)currentScene.redraw();
                    }
                }
            );

        uiControlGroup.addControl(
                "valignment",
                UIControl.TYPE_LIST,
                {
                    items : [{name:"Top"}, {name:"Middle"}, {name:"Bottom"}],
                    onGet : function() { return verticalAlignment + 1; },
                    onSet : function(index, item)
                    {
                        me.setVAlignment(index-1);
                        if(currentScene)currentScene.redraw();
                    }
                }
            );

        uiControlGroup.addControl(
            "shape",
            UIControl.TYPE_LIST,
            {
                items : [{name:"Line"}, {name:"Ellipse"}, {name:"Circle"}],
                onGet : function() { return textType; },
                onSet : function(index, item)
                {
                    me.setType(index);
                    if(currentScene)currentScene.redraw();
                }
            }
        );

        uiControlGroup.addControl(
            "radius",
            UIControl.TYPE_NUMBER,
            {
                minValue : 20,
                maxValue : 200,
                onGet : function() { return me.getRadius(); },
                onSet : function(value)
                {
                    if(value <= me.getSize() + 5) value = me.getSize() + 5;
                    me.setRadius(value);
                    if(currentScene)currentScene.redraw();
                }
            }
        );

        uiControlGroup.addControl(
            "angle",
            UIControl.TYPE_NUMBER,
            {
                minValue : 0,
                maxValue : 360,
                onGet : function()
                {
                    if((textType == TextElement.TYPE_ELLIPSE) || (textType == TextElement.TYPE_CIRCLE))
                    {
                        var a = 360 - Math.round(me.getAngle() * 180/Math.PI) - 180;
                        while(a < 0) a += 360;
                        return a % 360;
                    }
                    else
                    {
                        var p = me.getPosition();
                        var a = Math.atan2(p.y2 - p.y1, p.x2 - p.x1);
                        a = Math.round(a * 180/Math.PI);
                        if(a < 0) a += 360;
                        return a % 360;
                    }
                },
                onSet : function(value)
                {
                    if((textType == TextElement.TYPE_ELLIPSE) || (textType == TextElement.TYPE_CIRCLE))
                    {
                        me.setAngle(-value * Math.PI / 180 + Math.PI);
                        if(currentScene)currentScene.redraw();
                    }
                    else
                    {
                        var a = value * Math.PI / 180;
                        var p = me.getPosition();
                        var cx = (p.x1 +     p.x2) / 2;
                        var cy = (p.y1 + p.y2) / 2;
                        var dx = p.x2 - p.x1;
                        var dy = p.y2 - p.y1;
                        var d = Math.sqrt(dx*dx + dy*dy) / 2;
                        dx = Math.cos(a) * d;
                        dy = Math.sin(a) * d;
                        me.setPosition(
                                cx - dx, cy - dy,
                                cx + dx, cy + dy
                        );
                        if(currentScene)currentScene.redraw();
                    }
                }
            }
        );

        uiControlGroup.addControl(
            "fan angle",
            UIControl.TYPE_NUMBER,
            {
                minValue : 50,
                maxValue : 360,
                onGet : function()
                {
                    var a = Math.round(me.getFanAngle() * 180/Math.PI);
                    if(a < 0) a += 360;
                    return a;
                },
                onSet : function(value)
                {
                    me.setFanAngle((value == 360) ? Math.PI * 2 : (value * Math.PI / 180));
                    if(currentScene)currentScene.redraw();
                }
            }
        );

        uiControlGroup.addControl(
            "width",
            UIControl.TYPE_NUMBER,
            {
                minValue : 40,
                maxValue : 400,
                onGet : function() { return Math.round(me.getWidth()); },
                onSet : function(value)
                {
                    me.setWidth(value);
                    if(currentScene)currentScene.redraw();
                }
            }
        );

        uiControlGroup.addControl(
            "height",
            UIControl.TYPE_NUMBER,
            {
                minValue : 40,
                maxValue : 400,
                onGet : function() { return Math.round(me.getHeight()); },
                onSet : function(value)
                {
                    me.setHeight(value);
                    if(currentScene)currentScene.redraw();
                }
            }
        );

        uiControlGroup.addControl(
            "length",
            UIControl.TYPE_NUMBER,
            {
                minValue : 40,
                maxValue : 400,
                onGet : function()
                {
                    var p = me.getPosition();
                    var dx = p.x2 - p.x1;
                    var dy = p.y2 - p.y1;

                    return Math.round(Math.sqrt(dx*dx + dy*dy));
                },
                onSet : function(value)
                {
                    var p = me.getPosition();
                    var cx = (p.x1 +  p.x2) / 2;
                    var cy = (p.y1 +  p.y2) / 2;
                    var dx = p.x2 - p.x1;
                    var dy = p.y2 - p.y1;
                    var a = Math.atan2(dy, dx);
                    var ox = (value / 2) * Math.cos(a);
                    var oy = (value / 2) * Math.sin(a);

                    me.setPosition(cx - ox, cy - oy, cx + ox, cy + oy);

                    if(currentScene)currentScene.redraw();
                }
            }
        );

        uiControlGroup.addControl(
                "srcScale",
                UIControl.TYPE_NUMBER,
                {
                    minValue : -60,
                    maxValue : 140,
                    onGet : function()
                    {
                        if(srcScale >= 1)
                        {
                            return Math.max(Math.round(srcScale * 40) - 40, 0);
                        }
                        else
                        {
                            return Math.round(-1 / srcScale * 40 + 40);
                        }
                    },
                    onSet : function(value)
                    {
                        value = parseFloat(value);
                        if(!isNaN(value) && isFinite(value))
                        {
                            value = Math.round(value);
                            if(value >= 0)
                            {
                                me.setSrcScale(Math.max(1, value + 40) / 40);
                            }
                            else
                            {
                                me.setSrcScale(-1 / Math.min(-1,((value - 40) / 40)));
                            }
                            if(currentScene)currentScene.redraw();
                        }
                    }
                }
            );


        uiControlGroup.addControl(
            "centerX",
            UIControl.TYPE_NUMBER,
            {
                minValue : -200,
                maxValue : 200,
                onGet : function() { return Math.round(me.getCenterX()); },
                onSet : function(value)
                {
                    me.setCenterX(value);
                    if(currentScene)currentScene.redraw();
                }
            }
        );


        uiControlGroup.addControl(
            "centerY",
            UIControl.TYPE_NUMBER,
            {
                minValue : -175,
                maxValue : 175,
                onGet : function() { return -Math.round(me.getCenterY()); },
                onSet : function(value)
                {
                    me.setCenterY(-value);
                    if(currentScene)currentScene.redraw();
                }
            }
        );

        uiControlGroup.addControl(
            "title",
            UIControl.TYPE_TEXT,
            {
                onGet : function() { return uiControlGroup.title; },
                onSet : function(value)
                {
                    uiControlGroup.title = value;
                    me.setText();
                    if(currentScene)currentScene.redraw();
                }
            }
        );

        uiControlGroup.addControl(
            "colorFG",
            UIControl.TYPE_TEXT,
            {
                onGet : function()
                {
                    return me.getFGColor();
                },
                onSet : function(value)
                {
                    me.setFGColor(value);
                    if(currentScene)currentScene.redraw();
                }
            }
        );

        uiControlGroup.addControl(
            "visible",
            UIControl.TYPE_LIST,
            {
                items : [{name:"Visible"}, {name:"Hidden"}],
                onGet : function() { return me.getVisible() ? 0 : 1; },
                onSet : function(index, item)
                {
                    me.setVisible(index == 0);
                    if(currentScene)currentScene.redraw();
                }
            }
        );
      
        uiControlGroup.addControl(
            "specCharLeft",
            UIControl.TYPE_TEXT,
            {
                onGet : function() { return me.getSpecLeft() },
                onSet : function(value)
                {
                    me.setSpecLeft(value);
                    if(currentScene)currentScene.redraw();
                }
            }
        );
      
        uiControlGroup.addControl(
            "specCharRight",
            UIControl.TYPE_TEXT,
            {
                onGet : function() { return me.getSpecRight() },
                onSet : function(value)
                {
                    me.setSpecRight(value);
                    if(currentScene)currentScene.redraw();
                }
            }
        );
    };

    this.getState = function()
    {
        return this.common_getState({
            className        : this.className,
            editAllowMove    : this.getEditAllowMove(),
            type            : this.getType(),
            text            : this.getText(),
            bold            : this.getBold(),
            italic            : this.getItalic(),
            font            : this.getFont(),
            textFormat        : this.getTextFormat(),
            alignment        : this.getAlignment(),
            valignment        : this.getVAlignment(),
            scaleToFit        : this.getScaleToFit(),
            inverted        : this.getInverted(),
            size            : this.getSize(),
            minSize            : this.getMinSize(),
            angle            : this.getAngle(),
            fanAngle        : this.getFanAngle(),
            specLeft		: this.getSpecLeft(),
            specRight		: this.getSpecRight(),
            position        : this.getPosition(),
            srcScale        : this.getSrcScale(),
            fgColor            : this.getFGColor(),
            visible            : this.getVisible(),

            showMore        : uiControlGroup.showMore,
            title            : uiControlGroup.title
        });
    };

    this.setState = function(state)
    {
        this.common_setState(state);

        this.setEditAllowMove(state.editAllowMove);
        this.setType(state.type);
        this.setText(state.text);
        this.setBold(state.bold);
        this.setItalic(state.italic);
        this.setFont(state.font);
        this.setTextFormat(state.textFormat);
        //if (typeof state.alignment === "undefined");
        this.setAlignment(state.alignment);
        this.setVAlignment(state.valignment);
        this.setScaleToFit(state.scaleToFit);
        this.setInverted(state.inverted);
        this.setSize(state.size);
        this.setMinSize(state.minSize);
        this.setAngle(state.angle);
        this.setFanAngle(state.fanAngle);
        this.setSpecLeft(state.specLeft);
        this.setSpecRight(state.specRight);
        this.setSrcScale(state.srcScale);
        this.setPosition(
            state.position.x1, state.position.y1,
            state.position.x2, state.position.y2
        );
        this.setFGColor(state.fgColor);
        this.setVisible(state.visible === undefined ? true : state.visible);

        uiControlGroup.showMore = state.showMore;
        uiControlGroup.title = state.title;
        oldPosition = this.getPosition();
        oldAngle = this.widget.angle;
        if(this.scriptContainer.onInit)
        {
            this.scriptContainer.onInit();
        }

        this.setText();
    };


    this.createUI();
    this.setText();
    this.setPosition(0,0, 100, 100, 0);
}

TextElement.prototype = _prototypeElement;

TextElement.ALIGN_CENTRE = 0;
TextElement.ALIGN_LEFT = 1;
TextElement.ALIGN_RIGHT = 2;

TextElement.VALIGN_MIDDLE = 0;
TextElement.VALIGN_BOTTOM = 1;
TextElement.VALIGN_TOP = -1;

TextElement.TYPE_LINE = 0;
TextElement.TYPE_ELLIPSE = 1;
TextElement.TYPE_CIRCLE = 2;

TextElement.FORMAT_NONE = 0;
TextElement.FORMAT_UPPER_CASE = 1;
TextElement.FORMAT_LOWER_CASE = 2;

TextElement.FONTS = [];