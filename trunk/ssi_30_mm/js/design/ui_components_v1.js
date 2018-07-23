function UIComponents(appender, canvas, tooltips, changeCallback) {

	var me = this;
	this.appender = appender;
	this.canvas = canvas;
	this.fontStep = 4;
    var textControls = ["fontBold","fontItalic","fontFamily","fontSize","color","flipVertical"];
    var borderControls = ["color", "borderStyle", "borderSize", "borderRadius"];
    var imageControls = ["flipHorizontal", "flipVertical", "filterSepia", "filterMonochrome", "color"];
    this.showMaxLengthAlert = false;

	//On ready preload all fonts and set up css for font dropdown
	var fontCss = '';
	var fontPreload = '';
	var cur_selected;
	for (var font = 0; font < TextElement.FONTS.length; font++) {
		fontCss += '@font-face {' +
						'font-family: "' + TextElement.FONTS[font].id + '";' +
						"src: url('" + System.FONT_SERVICE_URL + "/" + TextElement.FONTS[font].id + "');" +
						'font-weight: normal;' +
						'font-style: normal;' +
					'}' +
					'.select-menu-fonts li:nth-child(' + (font + 1) + ') { font-family: "' + TextElement.FONTS[font].id + '" }';
		fontPreload += '<span style="font-family: \'' + TextElement.FONTS[font].id + '\'"></span>';
	}

	$("head").prepend('<style type="text/css">' + fontCss + '</style>');
	$("body").append('<div class="font_preload" style="opacity: 0">' + fontPreload + '</div>');

	$.widget("custom.iconselectmenu", $.ui.selectmenu, {
      _renderItem: function( ul, item ) {
        var li = $( "<li>");

        if ( item.disabled ) {
          li.addClass( "ui-state-disabled" );
        }

        $( "<span>", {
          style: item.element.attr( "data-style" ),
          "class": 'ui-icon-background ' + item.element.attr( "data-class" )
        })
          .appendTo( li );

        return li.appendTo( ul );
      },
      _setText: function(element, value) {
        if (value && this.items) {
            var item;
            element.text("");
            for (var i = 0; i < this.items.length; i++) {
                if (this.items[i].label == value) {
                    item = this.items[i];
                }
            }
            element.removeClass();
            element.addClass("ui-selectmenu-text ui-menu-background " + item.element.attr("data-class"));
        } else {
            element.html( "&#160;" );
        }
      },
      refresh: function() {
        this._refreshMenu();
		this._setText( this.buttonText, this.items[this._getSelectedItem().val()].label );
		if ( !this.options.width ) {
			this._resizeButton();
		}
      }
    });

	me.canvas.click(function(e) {
		var ele = _system.getSelected();
        me.selectElement(ele);
        e.stopPropagation();
	});

    me.canvas.on('touchstart', function(e) {
		var ele = _system.getSelected();
        me.selectElement(ele);
        e.stopPropagation();
	});

    $('body').on('click', function(e) {
        if ((e.target.nodeName == "DIV" || e.target.nodeName == "BODY") && !$(e.target).hasClass("element-div")) {
            _system.clearSelection();
            me.selectElement(null);
            _system.scene.redraw();
        }
    });

    this.selectElement = function(ele) {
        if (ele == null || ele === true) {
            cur_selected = null;
            if (!TI.simpleMode) {
                me.createComponent(null);
            } else {
                me.simpleSelect(null);
            }
        } else {
            if (!TI.simpleMode) {
                me.createComponent(ele);
            } else {
                me.simpleSelect(ele);
            }
        }
    }

    this.simpleSelect = function(element) {
        for (var i = 0; i < _system.elements.length; i++) {
            var div = $('#element-div-' + i);
            if (div) {
                div.removeClass('selected-div');
                if (element === _system.elements[i]) {
                    div.addClass('selected-div');
                    if (_system.elements[i].className == "TextElement") {
                        $('#modify-text-area-' + i).focus();
                        if ($('#modify-text-area-' + i)[0].setSelectionRange) {
                            var len = $('#modify-text-area-' + i).val().length * 2;
                            $('#modify-text-area-' + i)[0].setSelectionRange(len, len);
                        } else {
                            $('#modify-text-area-' + i).val($('#modify-text-area-' + i).val());
                        }
                    }
                    if (div.offset().top + 72 > $('#component_box_wrapper').offset().top + 390 || div.offset().top - $('#component_box_wrapper').offset().top < 0) {
                        $('#component_box_wrapper').animate({
                            scrollTop: div.offset().top - $('#component_box_wrapper').offset().top
                        }, 500);
                    }
                    if (changeCallback) {
                        changeCallback(element);
                    }
                }
            }
        }
    }

	this.createBorderElement = function(type) {
		var ele = new BorderElement();
		ele.setType(type);
		ele.setEditAllowMove(true);

		if (type == 1) {
			ele.setPosition(-110, -80, 110, 80, Math.PI*3/2);
		} else {
			var offsetX = _system.getPageWidth() / 2;
			var offsetY = _system.getPageHeight() / 2;
			ele.setPosition(
				-offsetX, -offsetY,
				offsetX, offsetY
			);
		}
		ele.getUIControlGroup().showMore = true;
		ele.setBorder(BorderElement.getBorderFromDescriptor({ id: "solid" }));
		if (type == 2) {
			setElementPointNotDiagVisibility(ele);
		}
        ele.config = {};
        ele.config.controls = borderControls;
		setElementMovePointVisible(ele);
		_system.addElement(ele);
		_system.setSelected(ele);
		this.createComponent(ele);
		_system.scene.redraw();
	}

	this.createImageElement = function(type) {
		if (type == 1) {
			TI.normalImageSelectDialog.show(-1,function(id) {
				var ele = new ImageElement();
				ele.id = 'user_select';
				ele.setEditAllowMove(true);
				ele.setMaintainAspectRatio(true);
				ele.setPosition(-40, -40, 40, 40);
				ele.sizeToBox = false;
				ele.setImageId(id);
				ele.source = 1;
				ele.getUIControlGroup().showMore = true;
				ele.widget.rotatePoints = false;
				setElementPointDiagVisibility(ele);
                ele.config = {};
                ele.config.controls = imageControls;
				_system.addElement(ele);
				_system.setSelected(ele);
				me.createComponent(ele);
				_system.scene.redraw();
				_system.saveState();
			});
		} else {
			TI.simpleImageSelectDialog.show(-1 ,function(id) {
				var ele = new ImageElement();
				ele.id = "user_upload";
				ele.setEditAllowMove(true);
				ele.setMaintainAspectRatio(true);
				ele.setPosition(-80, -80, 80, 80);
				ele.source = 0;
				ele.sizeToBox = false;
				ele.setImageId(id);
				ele.widget.rotatePoints = false;
				ele.getUIControlGroup().showMore = true;
				setElementPointDiagVisibility(ele);
                ele.config = {};
                ele.config.controls = imageControls;
				_system.addElement(ele);
				_system.setSelected(ele);
				me.createComponent(ele);
				_system.scene.redraw();
				_system.saveState();
			});
		}
	}

	this.createTextElement = function(type) {
		var ele = new TextElement();
		ele.setType(type);
		ele.setEditAllowMove(true);
		if (type == 0) {
			var offsetX = 100;
			ele.setPosition(-offsetX, 0, offsetX, 0);
		} else if (type == 1) {
			ele.setPosition(-110, -80, 110, 80, Math.PI*3/2);
			ele.setVAlignment(0);
			setElementMovePointVisible(ele);
		} else {
			ele.setPosition(-100, -100, 100, 100, Math.PI*3/2);
			ele.setVAlignment(0);
			setElementPointNotDiagVisibility(ele);
			setElementMovePointVisible(ele);
		}
		ele.getUIControlGroup().showMore = true;
		ele.getUIControlGroup().title = "Enter Text";
		ele.setScaleToFit(false);
		ele.autoResize = false;
        ele.config = {};
        ele.config.controls = textControls;
		_system.addElement(ele);
		_system.setSelected(ele);
		this.createComponent(ele);
		_system.scene.redraw();
	}

	this.createComponent = function(element, num) {
        num = (typeof num !== 'undefined' ? num : 0);
		if (element == null || element === true && !TI.simpleMode) {
            $(appender.children()[0]).children().each(function () {
                    if ($(this).data('qtip')) {
                        $(this).qtip('api').destroy(true);
                    }
            });
            appender.empty();
			$('<p>').attr({'class':'empty-appender'}).html("Add/Delete design elements or click on the canvas item you wish to edit. Click the Help button for more details.").appendTo(appender);
			return;
		}
        if  (element != cur_selected) {
            if (!TI.simpleMode) {
                $(appender.children()[0]).children().each(function () {
                    if ($(this).data('qtip')) {
                        $(this).qtip('api').destroy(true);
                    }
                });
                appender.empty();
            }
            if (element.className == "TextElement") {
                createTextComponents(element, num);
                if (changeCallback) {
                    changeCallback(element);
                }
            } else if (element.className == "BorderElement") {
                createBorderComponents(element, num);
                if (changeCallback) {
                    changeCallback(element);
                }
            } else if (element.className == "ImageElement" /*&& element.id == "user_select"*/) {
                createImageComponents(element, num);
                if (changeCallback) {
                    changeCallback(element);
                }
            } else {
                $('<p>').attr({'class':'empty-appender'}).html("No Valid Element Selected").appendTo(appender);
            }
            cur_selected = element;
		}
	}


	var createTextComponents = function(element, num) {
		var title = '';

		if (element.getType() == TextElement.TYPE_LINE) {
			title = 'Text Line Editor';
		} else if (element.getType() == TextElement.TYPE_CIRCLE) {
			title = 'Text Circle Editor';
		} else {
			title = 'Text Ellipse Editor';
		}

        var div = $('<div>').attr({'class':'element-div', 'id':'element-div-' + num}).appendTo(appender);
        $('#element-div-'+num).click(function (e) {
            _system.setSelected(element);
            me.selectElement(element);
        });
        if (!TI.simpleMode) {
            $('<h1>').attr({'class':'modify-header'}).html(title).appendTo(div);
        }

		if (element.config.controls && element.config.controls.indexOf('fontFamily') > -1) {
			var fontFamily = '';
			$('<select>').attr({'name':'select-font','id':'select-font-' + num,'data-native-menu':'false','class':'select-style'}).appendTo(div);
			for (var font in TextElement.FONTS) {
				if (TextElement.FONTS[font].id != element.getFont() && TextElement.FONTS[font].name != element.getFont()) {
					$('<option />', {value: TextElement.FONTS[font].id, text: TextElement.FONTS[font].name}).appendTo('#select-font-' + num);
				} else {
					fontFamily = TextElement.FONTS[font].id;
					$('<option />', {value: TextElement.FONTS[font].id, text: TextElement.FONTS[font].name, selected: 'selected'}).appendTo('#select-font-' + num);
				}
			}
			$('#select-font-' + num).selectmenu({
			   change: function( event, data ) {
					$('#select-font-' + num + '-button .ui-selectmenu-text').css("font-family", "\'" + data.item.value + "\'");
					element.setFont(data.item.value);
					_system.scene.redraw();
					_system.saveState(true);
				}
			});
			$('#select-font-' + num + '-button .ui-selectmenu-text').css("font-family", "\'" + fontFamily + "\'");
            $('#select-font-' + num + '-menu').addClass('select-menu-fonts');
			me.createToolTip($('#select-font-' + num + '-button'), "textFont");
		}

		if (TI.colorModel == "24_BIT") {
			if (element.config.controls && element.config.controls.indexOf('color') > -1)
			{
				$('<button>').attr({'class':'button-image modify-button', 'id':'color-button-' + num})
				.append($('<img>').attr({'class':'icon', 'src':home_url + '/images/icons/Font Color Selector.png'}))
				.simpleColorPicker({
					colors: _colors,
                    colorsPerLine: 11,
					onChangeColor: function(color) {
						element.setFGColor(color);
						_system.scene.redraw();
						_system.saveState(true);
					}
				})
				.button()
				.appendTo(div);
				me.createToolTip($('#color-button-' + num), "textColor");
			}
		}

		if (element.config.controls && element.config.controls.indexOf('fontSize') > -1) {
			$('<input>').attr({'name':'select-font-size','id':'select-font-size-' + num,'class':'select-element-size'}).appendTo(div);

			$('#select-font-size-' + num).spinner({
				spin: function( event, data ) {
					if (element.getType() == TextElement.TYPE_LINE) {
						var text = element.getText();
						if (!text || text == "") {
							text = element.getUIControlGroup().title;
						}
						if (element.autoResize) {
							var pos = element.getPosition();
							var angle = Math.atan2((pos.y2 - pos.y1), (pos.x2 - pos.x1));
							var ctx = _system.scene.canvas.getContext('2d');
							ctx.font=data.value * me.fontStep + "px '" + element.getFont() + "'";
							var newDist = ctx.measureText(text).width;
							pos.x2 = pos.x1 + Math.round(newDist * Math.cos(angle));
							pos.y2 = pos.y1 + Math.round(newDist * Math.sin(angle));
							element.setPosition(pos.x1, pos.y1, pos.x2, pos.y2, pos.angle);
						}
					}
					element.setSize(data.value * me.fontStep);
					_system.scene.redraw();
				},
				min: 1,
				max: 55
			}).spinner("value", element.getSize() / me.fontStep);

			$('#select-font-size-' + num).change(function(event, data) {
					if (this.value > $('#select-font-size').spinner("option", "max")) this.value = $('#select-font-size').spinner("option", "max");
					if (this.value < $('#select-font-size').spinner("option", "min")) this.value = $('#select-font-size').spinner("option", "min");
					element.setSize(this.value * me.fontStep);
					_system.scene.redraw();
					_system.saveState(true);
				});
            me.createToolTip($('span[class="ui-spinner ui-widget ui-widget-content ui-corner-all"]'), "fontSize");
			$('span[class="ui-spinner ui-widget ui-widget-content ui-corner-all"]').addClass('font-size');
			$('#select-font-size-' + num).css('display', 'none');
		}

		if (element.config.controls && element.config.controls.indexOf('fontBold') > -1) {
			$('<button>').attr({'class':'button-image modify-button', 'id':'bold-button-' + num})
			.click(function() {
				element.setBold(!element.getBold());
				_system.scene.redraw();
				_system.saveState(true);
				if (element.getBold()) {
					$(this).addClass("active-button");
				} else {
					$(this).removeClass("active-button");
				}
			})
			.append($('<img>').attr({'class':'icon', 'src':home_url + '/images/icons/Bold.png'}))
			.button()
			.appendTo(div);
			if (element.getBold()) {
				$('#bold-button-' + num).addClass("active-button");
			}
			me.createToolTip($('#bold-button-' + num), "bold");
		}

		if (element.config.controls && element.config.controls.indexOf('fontItalic') > -1) {
			$('<button>').attr({'class':'button-image modify-button', 'id':'italic-button-' + num})
			.click(function() {
				element.setItalic(!element.getItalic());
				_system.scene.redraw();
				_system.saveState(true);
				if (element.getItalic()) {
					$(this).addClass("active-button");
				} else {
					$(this).removeClass("active-button");
				}
			})
			.append($('<img>').attr({'class':'icon', 'src':home_url + '/images/icons/Italic.png'}))
			.button()
			.appendTo(div);
			if (element.getItalic()) {
				$('#italic-button-' + num).addClass("active-button");
			}
			me.createToolTip($('#italic-button-' + num), "italic");
		}

		var maxLength=5000;
		if (element.displayOptions.maxSize != 0) {
			maxLength = element.displayOptions.maxSize;
		}

		$('<input>').attr({'class':'modify-text-area', 'type':'text', 'id':'modify-text-area-' + num,'maxlength':maxLength.toString(),'placeholder':element.getUIControlGroup().title}).on('input', function(e) {
            element.setText($('#modify-text-area-' + num).val());
            var pos = element.getPosition();
            element.setPosition(pos.x1, pos.y1, pos.x2, pos.y2, pos.angle);
            _system.scene.redraw();
            if (me.showMaxLengthAlert && $('#modify-text-area-' + num).val().length == maxLength) {
                alert('You have reached the maximum number of characters allowed for this element.');
            }
		}).appendTo(div)
		if (element.getText() && element.getText() != "") {
			$("#modify-text-area-" + num).val(element.getText());
		}
		$("#modify-text-area-" + num).change(function() {
			_system.saveState(true);
		});
        if (!TI.simpleMode) {
            $('#modify-text-area-' + num).focus();
            if ($('#modify-text-area-' + num)[0].setSelectionRange) {
                var len = $('#modify-text-area-' + num).val().length * 2;
                $('#modify-text-area-' + num)[0].setSelectionRange(len, len);
            } else {
                $('#modify-text-area-' + num).val($('#modify-text-area-' + num).val());
            }
        }
        if (element.displayOptions.tooltip != "") {
            me.createToolTip($('#modify-text-area-' + num), element.displayOptions.tooltip);
        }
	}

	var createBorderComponents = function(element, num) {
		var title = '';

		if (element.getType() == BorderElement.TYPE_BOX) {
			title = 'Square Border Editor';
		} else if (element.getType() == BorderElement.TYPE_CIRCLE) {
			title = 'Circular Border Editor';
		} else {
			title = 'Elliptical Border Editor';
		}

        var div = $('<div>').attr({'class':'element-div', 'id':'element-div-' + num}).appendTo(appender);
        $('#element-div-'+num).click(function (e) {
            _system.setSelected(element);
            me.selectElement(element);
        });
        if (!TI.simpleMode) {
            $('<h1>').attr({'class':'modify-header'}).html(title).appendTo(div);
        }

		if (element.config.controls && element.config.controls.indexOf('borderStyle') > -1) {
			$('<select>').attr({'name':'select-border-style','id':'select-border-style-' + num,'data-native-menu':'false','class':'select-style'}).appendTo(div);
			for (var style in BorderElement.BORDERS) {
				if (BorderElement.BORDERS[style].id != element.getBorder().id) {
					$('<option data-class="ui-icon-' + BorderElement.BORDERS[style].id + '" value="' + style + '">' + BorderElement.BORDERS[style].name + '</option>').appendTo('#select-border-style-' + num);
				} else {
					$('<option data-class="ui-icon-' + BorderElement.BORDERS[style].id + '" value="' + style + '">' + BorderElement.BORDERS[style].name + '</option>').appendTo('#select-border-style-' + num);
				}
			}
			$('#select-border-style-' + num).iconselectmenu({
			   change: function( event, data ) {
					element.setBorder(BorderElement.BORDERS[data.item.value]);
					_system.scene.redraw();
					_system.saveState(true);
				}
			});
			me.createToolTip($('#select-border-style-' + num + '-button'), "borderStyle");
            $('#select-border-style-' + num).iconselectmenu("refresh");
		}

		if (TI.colorModel == "24_BIT") {
			if (element.config.controls && element.config.controls.indexOf('color') > -1)
			{
				$('<button>').attr({'class':'button-image modify-button', 'id':'color-button-' + num})
				.append($('<img>').attr({'class':'icon', 'src':home_url + '/images/icons/Font Color Selector.png'}))
				.simpleColorPicker({
					colors: _colors,
                    colorsPerLine: 11,
					onChangeColor: function(color) {
						element.setFGColor(color);
						_system.scene.redraw();
						_system.saveState(true);
					}
				})
				.button()
				.appendTo(div);
				me.createToolTip($('#color-button-' + num), "borderColor");
			}
		}

		if (element.config.controls && element.config.controls.indexOf('borderSize') > -1) {
			$('<input>').attr({'name':'select-border-size','id':'select-border-size-' + num,'class':'select-element-size'}).appendTo(div);

			$('#select-border-size-' + num).spinner({
			   spin: function( event, data ) {
					element.setSize(data.value);
					_system.scene.redraw();
				},
				min: 1,
				max: 100
			}).spinner("value", element.getSize());

			$('#select-border-size-' + num).change(function(event, data) {
					if (this.value > $('#select-border-size-' + num).spinner("option", "max")) this.value = $('#select-border-size-' + num).spinner("option", "max");
					if (this.value < $('#select-border-size-' + num).spinner("option", "min")) this.value = $('#select-border-size-' + num).spinner("option", "min");
					element.setSize(this.value);
					_system.scene.redraw();
					_system.saveState(true);
			});
            me.createToolTip($('span[class="ui-spinner ui-widget ui-widget-content ui-corner-all"]'), "borderSize");
			$('span[class="ui-spinner ui-widget ui-widget-content ui-corner-all"]').addClass('border-size');
			$('#select-border-size-' + num).css('display', 'none');
		}

		if (element.getType() == 0) {
			if (element.config.controls && element.config.controls.indexOf('borderRadius') > -1) {
				$('<input>').attr({'name':'select-border-radius','id':'select-border-radius-' + num,'class':'select-element-size'}).appendTo(div);

				$('#select-border-radius-' + num).spinner({
				   spin: function( event, data ) {
						var value = data.value;
						if (value > 0) value += 14;
						element.setEdgeRadius(value);
						_system.scene.redraw();
					},
					min: 0,
					max: 90
				}).spinner("value", element.getEdgeRadius() == 0 ? 0 : element.getEdgeRadius() - 14);

				$('#select-border-radius-' + num).change(function(event, data) {
						if (this.value > $('#select-border-radius-' + num).spinner("option", "max")) this.value = $('#select-border-radius-' + num).spinner("option", "max");
						if (this.value < $('#select-border-radius-' + num).spinner("option", "min")) this.value = $('#select-border-radius-' + num).spinner("option", "min");
						var value = parseInt(this.value);
						if (value > 0) value += 14;
						element.setEdgeRadius(value);
						_system.scene.redraw();
						_system.saveState(true);
				});
                me.createToolTip($('span[class="ui-spinner ui-widget ui-widget-content ui-corner-all"]'), "borderRadius");
				$('span[class="ui-spinner ui-widget ui-widget-content ui-corner-all"]').addClass('border-radius');
				$('#select-border-radius-' + num).css('float', 'right');
				$('#select-border-radius-' + num).css('width', '15px');
			}
		}
	}

	/***********************************************************************************************************************************************************
	************************************************************************************************************************************************************
	************************************************************************************************************************************************************
	************************************************************************************************************************************************************
	************************************************************************************************************************************************************
	*/
	var createImageComponents = function(element, num) {
        var div = $('<div>').attr({'class':'element-div', 'id':'element-div-' + num}).appendTo(appender);
        $('#element-div-'+num).click(function (e) {
            _system.setSelected(element);
            me.selectElement(element);
        });

        if (!TI.simpleMode) {
            $('<h1>').attr({'class':'modify-header'}).html("Image Editor").appendTo(div);
        }

		if((element.config.controls && element.config.controls.indexOf('change') > -1) || element.id != 'user_select' || element.source == 1) {
			var text = "Choose Image";
			if (element.id === 'user_select' ) {
				text = "Library";
			}
			TI.imageSelectDialog = TI.userImageSelectDialog;
			$('<button>').attr({'class':'browse-button highlight-button','id':'browse-button-' + num}).html(text).button().click(function() {
				if (element.id === 'user_select') {
					TI.userImageSelectDialog.show(-1 ,function(id) {
						element.setImageId(id);
						$('#image-src-' + num).html(id);
						_system.saveState();
					});
				} else if( element.source == 1 ) {
					TI.normalImageSelectDialog.show(-1 ,function(id) {
						element.setImageId(id);
						$('#image-src-' + num).html(id);
						_system.saveState();
					});
				} else {
					TI.simpleImageSelectDialog.show(-1 ,function(id, selectedFile) {
						element.setImageId(id);
						$('#image-src-' + num).html(selectedFile.name);
						_system.saveState();
					});
				}
			}).appendTo(div);
			me.createToolTip($('#browse-button-' + num), "imageBrowse");
		}

		if (element.config.controls && element.config.controls.indexOf('aspectRatio') > -1) {
			$('<button>').attr({'class':'aspect-button highlight-button','id':'aspect-button-' + num}).html('Aspect Ratio').button().click(function() {
				element.setMaintainAspectRatio(!element.getMaintainAspectRatio());
				var pos = element.getPosition();
				element.setPosition(pos.x1, pos.y1, pos.x2, pos.y2, pos.angle);
			}).appendTo(div);
			me.createToolTip($('#aspect-button-' + num), "imageAspect");
		}

		if((element.config.controls && element.config.controls.indexOf('size') > -1) || element.id != 'user_select') {
			$('<input>').attr({'name':'select-image-size','id':'select-image-size-' + num,'class':'select-element-size'}).appendTo(div);

			$('#select-image-size-' + num).spinner({
				spin: function( event, data ) {
					element.setSize(data.value);
					_system.scene.redraw();
				},
				min: 10,
				max: 3000,
				step: 5
			}).spinner("value", element.getSize());
			me.createToolTip($('span[class="ui-spinner ui-widget ui-widget-content ui-corner-all"]'), "imageSize");
			$('span[class="ui-spinner ui-widget ui-widget-content ui-corner-all"]').prepend(document.createTextNode('Image Size'));
			$('span[class="ui-spinner ui-widget ui-widget-content ui-corner-all"]').addClass('image-size');
			$('#select-image-size-' + num).css('display', 'none');
		}

		//user_select image, drawn from customer library
		if ( element.id === "user_select" ) {
			if(element.config.controls && element.config.controls.indexOf('color') > -1) {
				var colordiv = $('<div>').attr({'class':'color-div', 'id':'color-div-' + num, 'style':'margin-left: 10px;'}).appendTo(div);
				$('<button>').attr({'class':'button-image modify-button', 'id':'color-button-' + num, 'style':'display: inline-block;vertical-align:top;'})
				.append($('<img>').attr({'class':'icon', 'src':home_url + '/images/icons/Font Color Selector.png'}))
				.simpleColorPicker({
					colors: _colors,
					colorsPerLine: 11,
					onChangeColor: function(color) {
						clearImageParams(element);
						element.color = color.substring(1, 7);
						toggleImageParam(element, ResourceId.PARAM_GRADIENT + color.substring(1, 7) + "FFFFFF");
						$('#color-display-'+num).css('background', '#' + color.substring(1, 7));
						_system.scene.redraw();
						_system.saveState(true);
					}
				})
				.button()
				.appendTo(colordiv);
				me.createToolTip($('#color-button-' + num), "Image Color");
				var colortouse;
				if(element.color) {
					colortouse = element.color;
				} else {
					colortouse = "000000";
				}
				$('<div>').attr({'id':'color-display-'+ num, 'style':'display: inline-block;vertical-align:top;margin-left: 5px;height: 32px; width: 25%;background:#'+colortouse}).appendTo(colordiv);
				$('<div>').attr({'id':'element-description-' + num, 'type':'text', 'class':'control-label', 'style':'margin-left: 20px;display: inline-block;vertical-align:top;font-size: 24px;'}).html(element.getState().title).appendTo(colordiv);
			}
		//user_upload image
		} else if (TI.colorModel == "24_BIT") {
			if (element.config.controls && element.config.controls.indexOf('filterMonochrome') > -1) {
				$('<button>').attr({'class':'mono-button highlight-button','id':'mono-button-' + num}).html('Black & White').button().click(function() {
                    removeImageParam(element, ResourceId.PARAM_LINEAR_TINT + "70421440DA");
					toggleImageParam(element, ResourceId.PARAM_LINEAR_TINT + "FFFFFFFFFF");
					_system.scene.redraw();
					_system.saveState(true);
				}).appendTo(div);
				me.createToolTip($('#mono-button-' + num), "imageMonochrome");
			}

			if (element.config.controls && element.config.controls.indexOf('filterSepia') > -1) {
				$('<button>').attr({'class':'sepia-button highlight-button','id':'sepia-button-' + num}).html('Sepia').button().click(function() {
                    removeImageParam(element, ResourceId.PARAM_LINEAR_TINT + "FFFFFFFFFF");
					toggleImageParam(element, ResourceId.PARAM_LINEAR_TINT + "70421440DA");
					_system.scene.redraw();
					_system.saveState(true);
				}).appendTo(div);
				me.createToolTip($('#sepia-button-' + num), "imageSepia");
			}
		}
		//$('<div>').attr({'id':'element-description-' + num, 'type':'text', 'class':'control-label', 'style':'margin-left: 20px;float: right;vertical-align:top;font-size: 24px;'}).html(element.getState().title).appendTo(div);

		if(element.config.controls && element.config.controls.indexOf('color') == -1) {
			$('<div>').attr({'id':'element-description-' + num, 'type':'text', 'class':'control-label', 'style':'margin-left: 20px;display: inline-block;vertical-align:top;font-size: 24px;'}).html(element.getState().title).appendTo(div);
		}

        if (element.config.controls && element.config.controls.indexOf('fileName') > -1) {
            $('<span>').attr({'name':'image-src', 'id':'image-src-' + num, 'class':'image-src'}).append(element.getImageId()).appendTo(div);
            if (element.displayOptions.tooltip != "") {
                me.createToolTip($('#image-src-' + num), element.displayOptions.tooltip);
            }
        }
	}

	this.refreshComponent = function() {
		this.createComponent(_system.getSelected());
	}

	this.deleteElement = function() {
		_system.removeElement(_system.getSelected());
		var ele = _system.elements[0];
		if (ele) {
			_system.setSelected(ele);
            me.selectElement(ele);
		} else {
            me.selectElement(null);
        }
	}

	this.copyElement = function() {
		var ele;
		var selected = _system.getSelected();
		var offset = 10;
		if (selected.className == "TextElement") {
			ele = new TextElement();
			ele.setType(selected.getType());
			ele.setEditAllowMove(true);
			var position = selected.getPosition();
			ele.setPosition(position.x1 + offset, position.y1 + offset, position.x2 + offset, position.y2 + offset, position.angle);
			ele.getUIControlGroup().showMore = selected.getUIControlGroup().showMore;
			ele.getUIControlGroup().title = selected.getUIControlGroup().title;
			ele.setScaleToFit(selected.getScaleToFit());
			ele.setVAlignment(0);
			ele.setText(selected.getText());
			ele.setFont(selected.getFont());
			ele.setSize(selected.getSize());
			ele.setFGColor(selected.getFGColor());
			ele.setBold(selected.getBold());
			ele.setItalic(selected.getItalic());
            ele.config = {};
            ele.config.controls = textControls;
			if (ele.getType() == 2) {
				setElementPointNotDiagVisibility(ele);
			}
			if (ele.getType() != 0) {
				setElementMovePointVisible(ele);
			}
		} else if (selected.className == "BorderElement") {
			ele = new BorderElement();
			ele.setType(selected.getType());
			ele.setEditAllowMove(true);
			var position = selected.getPosition();
			ele.setPosition(
				position.x1 + offset, position.y1 + offset,
				position.x2 + offset, position.y2 + offset, position.angle
			);
			ele.getUIControlGroup().showMore = true;
			ele.setBorder(selected.getBorder());
			ele.setSize(selected.getSize());
			ele.setEdgeRadius(selected.getEdgeRadius());
			ele.setFGColor(selected.getFGColor());
			if (ele.getType() == 2) {
				setElementPointNotDiagVisibility(ele);
			}
            ele.config = {};
            ele.config.controls = borderControls;
			setElementMovePointVisible(ele);
		} else if (selected.className == "ImageElement") {
			ele = new ImageElement();
            ele.id = selected.id;
			ele.setEditAllowMove(true);
			ele.setMaintainAspectRatio(true);
			var position = selected.getPosition();
			ele.setPosition(position.x1 + offset, position.y1 + offset, position.x2 + offset, position.y2 + offset, position.angle);
			ele.sizeToBox = false;
			ele.widget.rotatePoints = false;
			ele.setImageId(selected.getImageId());
			ele.source = selected.source;
			ele.getUIControlGroup().showMore = true;
            ele.config = {};
            ele.config.controls = imageControls;
			setElementPointDiagVisibility(ele);
		}
		if (ele) {
			_system.addElement(ele);
			_system.setSelected(ele);
			this.createComponent(ele);
			_system.scene.redraw();
		}
	}

	this.moveElementForward = function() {
		var array = _system.scene.getLayer(Scene.LAYER_WIDGETS).drawables
		for(var i = 0; i < array.length; i++)
		{
			if(array[i] === _system.getSelected().widget && i < array.length - 1)
			{
				array.splice(i+1, 0, array.splice(i, 1)[0]);
				break;
			}
		}

		array = _system.scene.getLayer(Scene.LAYER_FOREGROUND).drawables
		for(var i = 0; i < array.length; i++)
		{
			if(array[i] === _system.getSelected().drawable)
			{
				for (var j = i + 1; j < array.length; j++) {
					if (array[j].displayGroup == 1 || array[j].displayGroup == 3) {
						array.splice(j, 0, array.splice(i, 1)[0]);
						break;
					}
				}
				break;
			}
		}
		if (_system.getSelected().highlightA) {
			for(var i = 0; i < array.length; i++)
			{
				if(array[i] === _system.getSelected().highlightA)
				{
					for (var j = i + 1; j < array.length; j++) {
						if (array[j] == _system.getSelected().drawable) {
							if (j != 0) j -= 1;
							array.splice(j, 0, array.splice(i, 1)[0]);
							break;
						}
					}
					break;
				}
			}
		}
		_system.scene.redraw();
		_system.saveState(true);
	}

	this.moveElementBack = function() {
		var index = 0;
		var array = _system.scene.getLayer(Scene.LAYER_WIDGETS).drawables
		for(var i in array)
		{
			if(array[i] === _system.getSelected().widget && i > 0)
			{
				array.splice(i-1, 0, array.splice(i, 1)[0]);
				break;
			}
		}

		array = _system.scene.getLayer(Scene.LAYER_FOREGROUND).drawables
		for(var i = 0; i < array.length; i++)
		{
			if(array[i] === _system.getSelected().drawable)
			{
				for (var j = i - 1; j >= 0; j--) {
					if (array[j].displayGroup == 1 || array[j].displayGroup == 3) {
						array.splice(j, 0, array.splice(i, 1)[0]);
						break;
					}
				}
				break;
			}
		}

		if (_system.getSelected().highlightA) {
			for(var i = 0; i < array.length; i++)
			{
				if(array[i] === _system.getSelected().highlightA)
				{
					for (var j = i - 1; j >= 0; j--) {
						if (array[j] == _system.getSelected().drawable) {
							if (j != 0) j -= 1;
							array.splice(j, 0, array.splice(i, 1)[0]);
							break;
						}
					}
					break;
				}
			}
		}
		_system.scene.redraw();
		_system.saveState(true);
	}

	this.rotateElement = function(angle) {
		_system.getSelected().setAngle(_system.getSelected().getAngle()+angle);
		_system.scene.redraw();
		_system.saveState(true);
	}

	this.setCanvasBackground = function(color) {
		if (findElementById("background_image")) {
			var colorValue = color.trim().toUpperCase();
			if(colorValue.charAt(0) == '#') colorValue = colorValue.substring(1);
			var ridColor = ResourceId.setParams(_config.image_palette_1_rid, [ResourceId.PARAM_GRADIENT + colorValue + 'FFFFFF']);
			findElementById("background_image").setImageId(ridColor);
			_system.saveState(true);
		} else {
            var colorValue = color.trim().toUpperCase();
            if(colorValue.charAt(0) == '#') colorValue = colorValue.substring(1);
			_system.scene.colors.paper.value = colorValue;
			_system.saveState(true);
			_system.scene.redraw();
		}
	}

	this.verticalMirror = function() {
		var ele = _system.getSelected();
		if (ele.className ==  "TextElement") {
			ele.setInverted(!ele.getInverted());
		} else if (ele.className == "ImageElement") {
			toggleImageParam(ele, ResourceId.PARAM_MIRROR_VERTICAL);
		}
		_system.scene.redraw();
		_system.saveState(true);
	}

	this.horizontalMirror = function() {
		var ele = _system.getSelected();
		if (ele.className == "ImageElement") {
			toggleImageParam(ele, ResourceId.PARAM_MIRROR_HORIZONTAL);
		}
		_system.scene.redraw();
		_system.saveState(true);
	}

	this.createToolTip = function(selector, name) {
        var content = name;
		if (tooltips[name] !== undefined) {
            content = tooltips[name];
        }
        selector.qtip({
				content: content, // Set the tooltip content to the current corner
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
					when: { event : "mouseout" },
                    fixed: false
				},
				style: tooltips.style
			});
	}

	this.resetWidgetDots = function() {
		for (var i = 0; i < _system.elements.length; i++) {
			var ele = _system.elements[i];
			if (ele.className == "TextElement") {

				if (ele.getType() == 2) {
					setElementPointNotDiagVisibility(ele);
				}
				if (ele.getType() != 0) {
					setElementMovePointVisible(ele);
				}
			} else if (ele.className == "BorderElement") {
				if (ele.getType() == 2) {
					setElementPointNotDiagVisibility(ele);
				}
				setElementMovePointVisible(ele);
			} else if (ele.className == "ImageElement") {
				ele.widget.rotatePoints = false;
				setElementPointDiagVisibility(ele);
			}
		}
	}


	this.toggleGrid = function() {
		_system.scene.drawGrid = !_system.scene.drawGrid;
		_system.scene.redraw();
		return _system.scene.drawGrid;
	}

	var clearImageParams = function (ele) {
		var path = ele.getImageId();
		params = [];
		var rid = ResourceId.setParams(path, params);
		ele.setImageId(rid);
	}

    var removeImageParam = function (ele, param) {
        var path = ele.getImageId();
		var params = ResourceId.getParams(path);
		if (params == null) {
			params = [];
		}
		var i = params.indexOf(param);
		if (i > -1) {
			params.splice(i, 1);
		}
		var rid = ResourceId.setParams(path, params);
		ele.setImageId(rid);
    }

	var toggleImageParam = function (ele, param) {
		var path = ele.getImageId();
		var params = ResourceId.getParams(path);
		if (params == null) {
			params = [];
		}
		var i = params.indexOf(param);
		if (i > -1) {
			params.splice(i, 1);
		} else {
			params.push(param);
		}
		var rid = ResourceId.setParams(path, params);
		ele.setImageId(rid);
	}

	var findElementById = function(id) {
		for(var i in _system.elements)
		{
			if(_system.elements[i].id == id)
			{
				return _system.elements[i];
			}
		}
		return null;
	}

	var setElementPointDiagVisibility = function(element) {
		var w = element.widget;
		w.setPointAllowVisible("topLeft", false);
		w.setPointAllowVisible("bottomLeft", false);
		w.setPointAllowVisible("topRight", false);
		w.setPointAllowVisible("bottomRight", false);
	}

	var setElementPointNotDiagVisibility = function(element) {
		var w = element.widget;
		w.lockAspect = true;
		w.setPointAllowVisible("topMiddle", false);
		w.setPointAllowVisible("bottomMiddle", false);
		w.setPointAllowVisible("middleLeft", false);
		w.setPointAllowVisible("middleRight", false);
	}

	var setElementMovePointVisible = function(element) {
		var w = element.widget;
		w.setPointAllowVisible("middleMiddle", true);
	}

    this.setInitialSelect = function() {
        if (TI.simpleMode) {
            appender.empty();
            cur_selected = null;
            var select = false;
            for (var i = 0; i < _system.elements.length; i++) {
                var ele = _system.elements[i];
                if (ele && !(ele.className == "ImageElement" && (!ele.id || ele.id == "" || ele.id == "background_image"))) {
                    this.createComponent(ele, i);
                    if (!select) {
                        cur_selected = ele;
                        _system.setSelected(ele);
                        this.simpleSelect(ele);
                        select = true;
                    }
                }
            }
        } else {
            for (var i = 0; i < _system.elements.length; i++) {
                var ele = _system.elements[i];
                if (ele && !(ele.className == "ImageElement" && (!ele.id || ele.id == "" || ele.id == "background_image"))) {
                    _system.setSelected(ele);
                    this.selectElement(ele);
                    break;
                }
            }
        }
    }

    this.setInitialSelect();
}
