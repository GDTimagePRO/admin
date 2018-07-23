		var TI = new function()
		{
			this.BIND_ON_LOAD = true;

			this.navTo = function(url)
			{
				_system.invokeWhenReady(function() {
					$("#stateJSON").val(_system.getStateJSON());
					$("#activeDesignIndex").val(System.ACTIVE_DESIGN_INDEX);
					$("#destURL").val(url);
					$("#navForm").submit();
				});
			};
			function ImageSelectDialog()
			{
				var selectedFile = null;
				var tabsCreated = false;
				var me = this;
				var tabContainerId = "dialog_user_select_image_tabs";
				var dialogContainerId = "dialog_user_select_image";
				this.selectedImage = -1;

				this.highlighSelected = function()
				{
					var tabContainer = $( "#" + tabContainerId );
					tabContainer.find(".image_cell_selected").attr("class", "image_cell");
					tabContainer.find("[imageId=\"" + this.selectedImage + "\"]").attr("class", "image_cell_selected");
				};

				this.setSelected = function(id)
				{
					this.selectedImage = id;
					this.highlighSelected();
				};

				this.show = function(selectedImageId, onSelect)
				{
					selectedFile = null;
					if(!tabsCreated)
					{
						$( "#" + tabContainerId ).tabs({
							load: function() { me.highlighSelected(); }
						});
						tabsCreated = true;
					}

					this.setSelected(selectedImageId);
					$( "#" + dialogContainerId ).dialog({
						resizable: false,
						height:600,
						width:800,
						modal: true,
						buttons: {
							"Ok": function() {
								if(me.selectedImage < 0)
								{
									alert("Please select an image and try again.");
									return;
								}
								$( this ).dialog( "close" );
								onSelect(me.selectedImage, selectedFile);
							},
							"Cancel": function() {
								me.selectedImage = -1;
								$( this ).dialog( "close" );
							}
						},
						"Cancel": function() {
							$( this ).dialog( "close" );
						}
					});
				};
			};

			function ImageSelectOrUploadDialog()
			{
				var selectedFile = null;
				var tabsCreated = false;
				var me = this;
				var tabContainerId = "dialog_select_image_tabs";
				var dialogContainerId = "dialog_select_image";
				this.selectedImage = -1;

				this.highlighSelected = function()
				{
					var tabContainer = $( "#" + tabContainerId );
					tabContainer.find(".image_cell_selected").attr("class", "image_cell");
					tabContainer.find("[imageId=\"" + this.selectedImage + "\"]").attr("class", "image_cell_selected");
				};

				this.selectFile = function(file)
				{
					selectedFile = file;
					if(selectedFile)
					{
						var tabContainer = $( "#" + tabContainerId );
						tabContainer.find("#dialog_select_image_filename").html(
							"File Name: "+selectedFile.name+"<br />" +
							"File Size: "+(selectedFile.size/1024).toFixed(2)+"KB"
						);
					}
				};

				this.uploadFile = function()
				{
					if(selectedFile)
					{
						_system.uploadImageFiles(
								selectedFile,
								1, //ImageDB::CATEGORY_USER_UPLOADED

								function(id)
								{
									selectedFile = null;
									me.setSelected(id);

									var tabContainer = $( "#" + tabContainerId );
									tabContainer.tabs( "load" , tabContainer.tabs( "option", "selected" ) );
								},

								function(value)
								{
									//$("#uploadFileProgressBar").progressbar( "value" , Math.floor(value * 100.0));
								},
								TI.colorModel
							);
					}
					else
					{
						alert("Please select a file to upload.");
					}
				};

				this.setSelected = function(id)
				{
					this.selectedImage = id;
					this.highlighSelected();
				};

				this.show = function(selectedImageId, onSelect)
				{
					selectedFile = null;
					if(!tabsCreated)
					{
						$( "#" + tabContainerId ).tabs({
							load: function() { me.highlighSelected(); }
						});
						tabsCreated = true;
					}

					this.setSelected(selectedImageId);
					$( "#" + dialogContainerId ).dialog({
						resizable: false,
						height:600,
						width:800,
						modal: true,
						buttons: {
							"Ok": function() {
								if(me.selectedImage < 0)
								{
									alert("Please select an image and try again.");
									return;
								}
								$( this ).dialog( "close" );
								onSelect(me.selectedImage, selectedFile);
							},
							"Cancel": function() {
								me.selectedImage = -1;
								$( this ).dialog( "close" );
							}
						},
						"Cancel": function() {
							$( this ).dialog( "close" );
						}
					});
				};
			};

			function ImageUploadDialog()
			{
				var me = this;
				var dialogContainerId = "dialog_upload_image";
				this.selectedImage = -1;

				this.highlighSelected = function() {};
				this.setSelected = function(id) {};
				this.selectFile = function(file) { };

				this.uploadFile = function(onSelect, dlg)
				{
					var dialogContrainer = $( "#" + dialogContainerId );
					var fileInput = dialogContrainer.find( "#file" )[0];

					$("#save_progress_bar").progressbar({
						value: false,
						max: 1,
						complete: function() {
							$("#save_progress_bar").progressbar("destroy");
						}});

					var selectedFile = fileInput.files[0];
    				if(!selectedFile)
    				{
    					alert("Please select a file to upload.");
    					return;
    				}

    				var fileName = fileInput.value;
    				var fileType = fileName.substring(fileName.length-4).toLowerCase();
    				var fileTypeLong = fileName.substring(fileName.length-5).toLowerCase();
    				if((fileType != ".png") && (fileType != ".jpg") && (fileTypeLong != ".jpeg"))
    				{
    					alert("The selected file must be a PNG or a JPG.");
    					return;
    				}

    				$("#overlay").css("visibility","visible");
    				$("#overlay_text").text("Uploading image.");


					_system.uploadImageFiles(
						selectedFile,
						1, //ImageDB::CATEGORY_USER_UPLOADED
						function(error, id)
						{
							if (error) {
								var message = "There was an error processing your image.<br>Please call customer service for assistance.";
								if (error == 'Failed to process image') {
									message = "There was an error processing your image.<br> This may be caused by an invalid color model.<br>Please call customer service for assistance.<br>";
								} else if (error == 'Image exceeds maximum width' || error == 'Image exceeds maximum height') {
									message = "Image exceeds 4000 x 4000 pixel maximum.<br>Please resize your image or call customer<br>service for assistance.<br>";
								} else if (error == 'The uploaded file exceeds the post_max_size directive in php.ini' ||
								error == 'The uploaded file exceeds the upload_max_filesize directive in php.ini' ||
								errpr == 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form') {
									message = "The file size of the image you tried to upload is to large.<br>Please make the file size smaller or contact customer support.<br>";
								}
								$('<div title="Upload Error">' +
											message +
											'</div>').dialog({
											height:180,
											width:300,
											modal: true,
											dialogClass: "no-close",
											buttons: [{
												text: "Close",
												click: function() {
													$( this ).dialog( "close" );
												}
											}]
										});
								$("#overlay").css("visibility","hidden");
							}
							else
							{
								if(TI.colorModel == "1_BIT")
								{
									id = ResourceId.setParams(id,[ResourceId.PARAM_MONOCHROME]);
								}

								$.ajax({
									url: TI.serviceResourceOp + '?params=' + encodeURIComponent($.toJSON({opName:'info',srcId:id})),
									type: "GET",
									dataType: "text",
									success: function(info)
									{
										info = jQuery.parseJSON(info);
										if((info.width < 950) || (info.height < 950))
										{
											$('<div title="Low Resolution Warning">' +
												"The image you have selected is only " + info.width + " by " + info.height + " pixels.<br>" +
												"Images smaller than 950 by 950 pixels may appear blurry or pixelated in print.<br><br>" +
												'Are you sure that you wish to continue ?</div>').dialog({

												height:200,
												width:360,
												modal: true,
												dialogClass: "no-close",
												buttons: [{
													text: "Yes",
													click: function() {
														if(onSelect)onSelect(id, selectedFile);
														dlg.dialog( "close" );
														$( this ).dialog( "close" );
													}
												},{
													text: "No",
													click: function() {
														$( this ).dialog( "close" );
													}
												}]
											});
											$("#overlay").css("visibility","hidden");
										}
										else
										{
											if(onSelect)onSelect(id, selectedFile);
											dlg.dialog( "close" );
											$("#overlay").css("visibility","hidden");
										}
									}
								});
							}
						},

						function(value) { $("#save_progress_bar").progressbar("value", value) },
						TI.colorModel
					);

				};

				this.show = function(selectedImageId, onSelect)
				{
					var dialogContrainer = $( "#" + dialogContainerId );
					var fileInput = dialogContrainer.find( "#file" );
					fileInput.replaceWith( control = fileInput.clone( true ) );
					var onCancel = function() { $( this ).dialog( "close" ); };

					dialogContrainer.dialog({
						resizable: false,
						height:200,
						width:330,
						modal: true,
						buttons: {
							"Ok": function()
							{
								me.uploadFile(onSelect, $(this));
							},
							"Cancel": onCancel
						},
						"Cancel": onCancel,
					});


				};
			};

			this.simpleImageSelectDialog = new ImageUploadDialog();
			this.normalImageSelectDialog = new ImageSelectOrUploadDialog();
			this.userImageSelectDialog = new ImageSelectDialog(); //user_select
			this.imageSelectDialog = function() { TI.imageSelectDialog = TI.simpleMode ? this.simpleImageSelectDialog : this.normalImageSelectDialog };

			this.templateSelectDialog = new function()
			{
				var tabsCreated = false;
				var me = this;
				var tabContainerId = "dialog_select_template_tabs";
				var dialogContainerId = "dialog_select_template";

				this.onOk = null;
				this.selectedImage = -1;
				this.highlighSelected = function()
				{
					var tabContainer = $( "#" + tabContainerId );
					tabContainer.find(".image_cell_selected").attr("class", "image_cell");
					tabContainer.find("[templateId=\"" + this.selectedImage + "\"]").attr("class", "image_cell_selected");
				};

				this.setSelected = function(id, doOk)
				{
					this.selectedImage = id;
					this.highlighSelected();
					if(doOk) this.onOk.apply($( "#" + dialogContainerId ));
				};

				this.getSelected = function()
				{
					return this.selectedImage;
				};

				this.show = function(selectedTemplateId, onSelect, forceSelect)
				{
					if(!tabsCreated)
					{
						$( "#" + tabContainerId ).tabs({
							load: function() { me.highlighSelected(); }
						});
						tabsCreated = true;
					}

					this.setSelected(selectedTemplateId);

					if(forceSelect)
					{
						this.onOk = function() {
							if(me.selectedImage < 0)
							{
								alert("Please select an image and try again.");
								return;
							}
							$( this ).dialog( "close" );
							onSelect(me.selectedImage);
						};

						$( "#" + dialogContainerId ).dialog({
							resizable: false,
							height:600,
							width:800,
							modal: true,
							closeOnEscape: false,
							open: function(event, ui) { $(".ui-dialog-titlebar-close", $(this).parent()).hide(); },
							buttons: {
								"Ok": this.onOk,
							},
							"Cancel": function() {}
						});
					}
					else
					{
						this.onOk = function() {
							if(me.selectedImage < 0)
							{
								alert("Please select an image and try again.");
								return;
							}
							$( this ).dialog( "close" );
							onSelect(me.selectedImage);
						};

						$( "#" + dialogContainerId ).dialog({
							resizable: false,
							height:600,
							width:800,
							modal: true,
							open: function(event, ui) { $(".ui-dialog-titlebar-close", $(this).parent()).show(); },
							buttons: {
								"Ok": this.onOk,
								"Cancel": function() {
									me.selectedImage = -1;
									$( this ).dialog( "close" );
								}
							},
							"Cancel": function() {
								$( this ).dialog( "close" );
							}
						});
					}
				};
			};
		};

		function initZoomSlider()
		{
			var canvas = $("#canvas");
			var width = canvas.width();
			var height = canvas.height();

			var viewWidth = _system.pageViewWidth;
			if(!viewWidth) viewWidth = _system.getPageWidth();

			var viewHeight = _system.pageViewHeight;
			if(!viewHeight) viewHeight = _system.getPageHeight();

			var defaultScale = Math.min(width /  viewWidth, height / viewHeight);
			_system.scene.scale =  defaultScale;

			$("#zoom").slider({
				max: 150,
				min: -20,
				value: 0,
				slide: function(event, ui) {
					_system.scene.scale =  defaultScale  + ($(this).slider("value") / 100.0);
					_system.scene.redraw();
				},
				change: function(event, ui) {
					_system.scene.scale =  defaultScale  + ($(this).slider("value") / 100.0);
					_system.scene.redraw();
				}
			});
		}

		$(function() {

			if(!TI.BIND_ON_LOAD) return;

			TI.imageSelectDialog();

			var canvas = $("#canvas");
			var width = canvas.width();
			var height = canvas.height();

			canvas = canvas[0];
			canvas.width = width;
			canvas.height = height;

			_system.onInit("canvas","uiPanel","", width, height);
			_system.scene.colorModel = TI.colorModel;

			_system.scene.setRenderRequestEnabled(false);
			_system.scene.redraw();

			if(TI.initStateJSON != '')
			{
				_system.setState(jQuery.parseJSON(TI.initStateJSON));
			}

			if(TI.defaultValueJSON)
			{
				var defaultValues = jQuery.parseJSON(TI.defaultValueJSON);
				var errorCount = 0;

				var findField = function(id)
				{
					id = $.trim(id.toLowerCase());
					for(var i=0; i<_system.elements.length; i++)
					{
						var ele = _system.elements[i];
						//(ele.getUIControlGroup().title == id)
						if((ele.className == "TextElement") && ($.trim(ele.id.toLowerCase()) == id))
						{
							return ele;
						}
					}
					return null;
				};

				for(var key in defaultValues)
				{
					var ele = findField(key);
					if(ele == null)
					{
						var msg = "Missing field : \"" + key + "\"";
						console.log(msg);
						errorCount++;
						//alert(msg);
						//throw msg;
					}
					if(ele)ele.setText(defaultValues[key]);
				}

				if(errorCount > 0) console.log("");

				for(var i=0; i<_system.elements.length; i++)
				{
					var ele = _system.elements[i];
					var group = ele.getUIControlGroup();
					if(	(ele.className == "TextElement") &&
						(group.params.element.displayOptions.visibility == 0) &&
						(ele.id) )
					{
						var id = $.trim(ele.id.toLowerCase());
						var found = false;
						for(var key in defaultValues)
						{
							if($.trim(key.toLowerCase()) == id)
							{
								found = true;
								break;
							}
						}

						if(!found)
						{
							var msg = "Missing data for field : \"" + ele.id + "\"";
							console.log(msg);
							errorCount++;

						}
					}
				}

				if(errorCount == 0)
				{
					setTimeout( function(){
						TI.onNextButton();
					},1);
				}
				else
				{
					//alert("Errors: See log");
					console.log("");
					console.log("All Fields -----------------");
					for(var key in defaultValues)
					{
						console.log( "     " + key);
					}
					console.log("");
				}
			}


			_system.clearStateHistory();
			_system.saveState(true);

			if(TI.simpleMode && (_system.elements.length == 0))
			{
				setTimeout(function(){
					TI.templateSelectDialog.show( -1, function(id) {
							TI.onTemplateSelectDefaultHandler(id, true);
					}, true);
				}, 0);
			}

			if(_system.ui)_system.ui.onDeleteClick = function(name, element)
			{
				$( "#dialog_delete_element_name" ).text(name);
				$( "#dialog_delete_element" ).dialog({
					resizable: false,
					height:200,
					width:350,
					modal: true,
					buttons: {
						"Yes": function() {
							_system.removeElement(element);
							$( this ).dialog( "close" );
						},
						"No": function() {
							$( this ).dialog( "close" );
						}
					}
				});

			};

			if(!TI.simpleMode || (_system.elements.length != 0))
			{

				if(TI.productConfigJSON)
				{
					try { TI.productConfigJSON = jQuery.parseJSON(TI.productConfigJSON); }
					catch(e) { TI.productConfigJSON = null; };
				}

				if(TI.productConfigJSON)
				{
					if(TI.productConfigJSON.overlay)
					{
						var params = TI.productConfigJSON.overlay;
						var overlayLayer = _system.scene.getLayer(Scene.LAYER_OVERLAY);
						overlayLayer.clear();

						var width = _system.getPageWidth()
						var height = _system.getPageHeight();
						var image = { descriptor: ImageSrc.toDescriptor(new ImageSrc(ImageSrc.TYPE_ID, params[0])) };

						_system.pageViewWidth = width * params[3];
						_system.pageViewHeight = height * params[4];

						var imageDrawable = new ImageDrawable(image,
							-(width / 2.0) + width * params[1],
							-(height / 2.0) + width * params[2],
							_system.pageViewWidth,
							_system.pageViewHeight
						);
						imageDrawable.type = ImageDrawable.TYPE_CENTER;
						overlayLayer.add(imageDrawable);
					}
				}
			}

			initZoomSlider();

			_system.scene.setRenderRequestEnabled(true);

			TI.onTemplateSelectDefaultHandler = function(id, isDesignInit) {
				$.get("design_part/template_dialog_service.php?jsonTemplateId=" + id).done(function(response) {

					var oldColors = _system.scene.colors;
					if (typeof _config !== "undefined") {
						_config = jQuery.parseJSON(response).config.misc;
					}
					var savedValues = {};
					for(var i=0; i<_system.elements.length; i++)
					{
						var ele = _system.elements[i];
						if((ele.className == "TextElement") && (ele.id != ""))
						{
							savedValues[ele.id] = {
								className: ele.className,
								text: ele.getText()
							};

						}
						else if((ele.className == "ImageElement") && (ele.id != ""))
						{
							savedValues[ele.id] = {
								className: ele.className,
								src: ele.getLoadedImageSrc(),
								position: ele.getPosition(),
								angle:ele.getAngle()
							};
						}
					}

					if(isDesignInit)
					{
						_system.setState(jQuery.parseJSON(response).design);
						_system.clearStateHistory();
						_system.saveState();
					}
					else
					{
						_system.saveState();
						_system.setState(jQuery.parseJSON(response).design);
					}

					for(var i=0; i<_system.elements.length; i++)
					{
						var ele = _system.elements[i];
						if((ele.id != "") && (ele.id in savedValues))
						{
							var values = savedValues[ele.id];
							if(values.className == ele.className)
							{
								if(ele.className == "TextElement")
								{
									ele.setText(values.text);
								}
								else if(ele.className == "ImageElement")
								{
									ele.setAngle(values.angle);
									ele.setPosition(
										values.position.x1,
										values.position.y1,
										values.position.x2,
										values.position.y2
									);
									ele.loadImage(values.src, false);

									var uiControlGroup = ele.getUIControlGroup();
									uiControlGroup.updateControl("centerX");
							        uiControlGroup.updateControl("centerY");
							        uiControlGroup.updateControl("width");
							        uiControlGroup.updateControl("height");
							        uiControlGroup.updateControl("size");
							        uiControlGroup.updateControl("angle");
								}
							}
						}
					}

					for(var color in oldColors)
					{
						_system.scene.colors[color] = oldColors[color];
					}

					var inkColor = _system.scene.colors.ink;
					_system.changeInkColour(inkColor.name, inkColor.value);

					if(TI.productConfigJSON)
					{
						try { TI.productConfigJSON = jQuery.parseJSON(TI.productConfigJSON); }
						catch(e) { TI.productConfigJSON = null; };
					}

					if(TI.productConfigJSON)
					{
						if(TI.productConfigJSON.overlay)
						{
							var params = TI.productConfigJSON.overlay;
							var overlayLayer = _system.scene.getLayer(Scene.LAYER_OVERLAY);
							overlayLayer.clear();

							var width = _system.getPageWidth()
							var height = _system.getPageHeight();
							var image = { descriptor: ImageSrc.toDescriptor(new ImageSrc(ImageSrc.TYPE_ID, params[0])) };
							console.log("IMAGER"+image);

							_system.pageViewWidth = width * params[3];
							_system.pageViewHeight = height * params[4];

							var imageDrawable = new ImageDrawable(image,
								-(width / 2.0) + width * params[1],
								-(height / 2.0) + width * params[2],
								_system.pageViewWidth,
								_system.pageViewHeight
							);
							imageDrawable.type = ImageDrawable.TYPE_CENTER;
							overlayLayer.add(imageDrawable);
						}
					}

					initZoomSlider();
				});
			};

			$("#template").button().click(function() {
				TI.templateSelectDialog.show( -1, TI.onTemplateSelectDefaultHandler, false);
			});

			$("#undo").button().click(function() {
				var inkColor = _system.scene.colors.ink;
				_system.undo();
				_system.changeInkColour(inkColor.name, inkColor.value);
			});

			$("#redo").button().click(function() {
				var inkColor = _system.scene.colors.ink;
				_system.redo();
				_system.changeInkColour(inkColor.name, inkColor.value);
			});


			var activeAddElementDialog = null;

			$("#addElement").button().click(function() {
				activeAddElementDialog = $( "#dialog_add_element" ).dialog({
					resizable: false,
					height:320,
					width:350,
					modal: true,
					buttons: {
						"Cancel": function() {
							$( this ).dialog( "close" );
						}
					}
				});
			} );

			$("#addTextLine").button().click( function() {

				activeAddElementDialog.dialog( "close" );

				var ele = new TextElement();
				var offsetX = _system.getPageWidth() / 2;
				ele.setEditAllowMove(true);
				ele.setPosition(-offsetX, 0, offsetX, 0);
				ele.setType(TextElement.TYPE_LINE);
				ele.setText("");
				ele.getUIControlGroup().showMore = true;

				_system.addElement(ele);
				_system.setSelected(ele);
				_system.scene.redraw();

			});

			$("#addTextCircle").button().click(function() {

				activeAddElementDialog.dialog( "close" );

				var ele = new TextElement();
				ele.setType(TextElement.TYPE_CIRCLE);
				ele.setEditAllowMove(true);
				ele.setPosition(-100, -100, 100, 100, Math.PI*3/2);
				ele.setText("");
				ele.getUIControlGroup().showMore = true;

				_system.addElement(ele);
				_system.setSelected(ele);
				_system.scene.redraw();

			});

			$("#addTextEllipse").button().click(function() {

				activeAddElementDialog.dialog( "close" );

				var ele = new TextElement();
				ele.setType(TextElement.TYPE_ELLIPSE);
				ele.setEditAllowMove(true);
				ele.setPosition(-110, -80, 110, 80, Math.PI*3/2);
				ele.setText("");
				ele.getUIControlGroup().showMore = true;

				_system.addElement(ele);
				_system.setSelected(ele);
				_system.scene.redraw();

			});

			$("#addBorderRectangle").button().button().click(function() {

				activeAddElementDialog.dialog( "close" );

				var ele = new BorderElement();
				ele.setType(BorderElement.TYPE_BOX);
				ele.setEditAllowMove(true);

				var offsetX = _system.getPageWidth() / 2;
				var offsetY = _system.getPageHeight() / 2;
				ele.setPosition(
					-offsetX, -offsetY,
					offsetX, offsetY
				);
				ele.getUIControlGroup().showMore = true;

				_system.addElement(ele);
				_system.setSelected(ele);
				_system.scene.redraw();

			});


			$("#addBorderCircle").button().button().click(function() {

				activeAddElementDialog.dialog( "close" );

				var ele = new BorderElement();
				ele.setType(BorderElement.TYPE_CIRCLE);
				ele.setEditAllowMove(true);

				var offsetX = _system.getPageWidth() / 2;
				var offsetY = _system.getPageHeight() / 2;
				if(offsetX < offsetY) { offsetY = offsetX; }
				else { offsetX = offsetY; }

				ele.setPosition(
					-offsetX, -offsetY,
					offsetX, offsetY
				);
				ele.getUIControlGroup().showMore = true;

				_system.addElement(ele);
				_system.setSelected(ele);
				_system.scene.redraw();

			});


			$("#addBorderEllipse").button().button().click(function() {

				activeAddElementDialog.dialog( "close" );

				var ele = new BorderElement();
				ele.setType(BorderElement.TYPE_ELLIPSE);
				ele.setEditAllowMove(true);

				var offsetX = _system.getPageWidth() / 2;
				var offsetY = _system.getPageHeight() / 2;

				ele.setPosition(
					-offsetX, -offsetY,
					offsetX, offsetY
				);
				ele.getUIControlGroup().showMore = true;

				_system.addElement(ele);
				_system.setSelected(ele);
				_system.scene.redraw();

			});


			$("#addImageElement").button().click(function() {

				activeAddElementDialog.dialog( "close" );

				TI.imageSelectDialog.show(-1,function(id) {

					var ele = new ImageElement();
					ele.setEditAllowMove(true);
					ele.setMaintainAspectRatio(true);
					ele.setPosition(-60, -60, 60, 60);

					ele.setImageId(id);

					ele.getUIControlGroup().showMore = true;

					_system.addElement(ele);
					_system.setSelected(ele);
					_system.scene.redraw();

				});
			});

			$("#addLineElement").button().click(function() {

				activeAddElementDialog.dialog( "close" );

				var ele = new LineElement();
				ele.setEditAllowMove(true);
				ele.setPosition(-100, 0, 100, 0);
				ele.getUIControlGroup().showMore = true;

				_system.addElement(ele);
				_system.setSelected(ele);
				_system.scene.redraw();

			});

			TI.onPreviousButton = function() {

				if(System.ACTIVE_DESIGN_INDEX == 0)
				{
					$( "#dialog_confirm_previous" ).dialog({
						resizable: false,
						height:200,
						width:350,
						modal: true,
						buttons: {
							"Yes": function() {
								$( this ).dialog( "close" );
								if (TI.nav_prev || 0 !== TI.nav_prev.length) {
									TI.navTo(TI.nav_prev);
								} else {
									window.close();
								}
							},
							"No": function() {
								$( this ).dialog( "close" );
							}
						}
					});
				}
				else
				{
					TI.navTo(TI.nav_prev);
				}
			};

			$("#previousButton").button().click(TI.onPreviousButton);

			$("#saveProgressBar").progressbar();

			TI.savePreview = function(onDone) {

				$.support.cors = true;
				_system.invokeWhenReady(function() {

					var previewScaleWidth = TI.outputImageWidth_preview / _system.getPageWidth();
					var previewScaleHeight = TI.outputImageHeight_preview / _system.getPageHeight();
					var previewScale = Math.min(previewScaleWidth,previewScaleHeight);

					var imageType = "web";
	        		if(_system.scene.colorModel == '1_BIT')
	        		{
	        			imageType = "web_" + _system.scene.colors.ink.value;
	        		}

					var previewImageQuery = _system.scene.getRenderServiceQuery(
						TI.outputImageWidth_preview,
						TI.outputImageHeight_preview,
						imageType,
						_system.scene.colors.ink,
						previewScale,
						Scene.DISPLAY_GROUP_CONTENT,
						TI.designImageId,
						TI.outputImageWidth_preview,
						TI.outputImageHeight_preview,
						"null"
					);

					var retryCount = 0;
					var sendPreviewRenderRequest = null;
					sendPreviewRenderRequest = function()
					{
						var didRespond = false;
						$.get(previewImageQuery).done(function(){

							didRespond = true;
							onDone(true);

						}).fail(function(){

							didRespond = true;
							if(retryCount++ < 8)
							{
								setTimeout(function() {
									sendPreviewRenderRequest();
								}, 100);
							}
							else onDone(false);

						}).always(function(){

							if(!didRespond) onDone(true);

						});
					};
					sendPreviewRenderRequest();
				});
			};

			TI.onNextButton = function() {

				//$("#save_progress_bar").progressbar( "value" , 0);
				$("#overlay").css("visibility","visible");
				$("#overlay_text").text("Your design is being saved, please stand by.");

				TI.savePreview(function(successful) {
					if(successful)
					{
						TI.navTo(TI.nav_next);
					}
					else
					{
						alert("Server error.");
						$("#overlay").css("visibility","hidden");
					}
				});
			};

			$("#nextButton").button().click(TI.onNextButton);
		});
