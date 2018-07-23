$.fn.simpleImagePicker = function(options) {
	var defaults = {
		images: [],
		showEffect: '',
		hideEffect: '',
		onChangeImage: false,
		width:25,
		height:25
	};

	var opts = $.extend(defaults, options);

	return this.each(function() {
		var txt = $(this);

		var markup = '';

		var prefix = txt.attr('id').replace(/-/g, '') + '_';

		for(var i = 0; i < opts.images.length; i++){
			var item = opts.images[i];

			var breakLine = ' ';

			if (i > 0 && breakLine && $.browser && $.browser.msie && $.browser.version <= 7) {
				breakLine = '';
				markup += '<li style="float: none; clear: both; overflow: hidden; background-color: #fff; display: block; height: 1px; line-height: 1px; font-size: 1px; margin-bottom: -2px;"></li>';
			}

			markup += '<li id="' + prefix + 'image-' + i + '" class="image-box" style="' + breakLine + 'clear: both; width: ' + opts.width + 'px; height: ' + opts.height + 'px; background-image:url(\'' + item + '\')"></li>';
		}

		var box = $('<div id="' + prefix + 'image-picker" class="image-picker" style="position: absolute; left: 0px; top: 0px;"><ul>' + markup + '</ul><div style="clear: both;"></div></div>');
		$('body').append(box);
		box.hide();
		
		box.find('li.image-box').click(function() {
			if (txt.is('input')) {
				txt.val(opts.images[this.id.substr(this.id.indexOf('-') + 1)]);
				txt.blur();
			}
			if ($.isFunction(defaults.onChangeImage)) {
				defaults.onChangeImage.call(txt, opts.images[this.id.substr(this.id.indexOf('-') + 1)]);
			}
			hideBox(box);
		});

		$('body').live('click', function() {
			hideBox(box);
		});

		box.click(function(event) {
			event.stopPropagation();
		});

		var positionAndShowBox = function(box) {
			var pos = txt.offset();
			var left = pos.left + txt.outerWidth() - box.outerWidth();
			if (left < pos.left) left = pos.left;
			box.css({ left: left, top: (pos.top + txt.outerHeight()) });
			showBox(box);
		};

		txt.click(function(event) {
			event.stopPropagation();
			if (!txt.is('input')) {
				positionAndShowBox(box);
			}
		});

		txt.focus(function() {
			positionAndShowBox(box);
		});

		function hideBox(box) {
			if(box == null) return; 
			if (opts.hideEffect == 'fade')
				box.fadeOut();
			else if (opts.hideEffect == 'slide')
				box.slideUp();
			else
				box.hide();
			
			if($.fn.simpleImagePicker.selected !== box)
			{
				hideBox($.fn.simpleImagePicker.selected);
			}
			$.fn.simpleImagePicker.selected = null;
		}

		function showBox(box) {
			
			if($.fn.simpleImagePicker.selected !== box)
			{
				hideBox($.fn.simpleImagePicker.selected);
			}
			if($.fn.simpleColorPicker.selected !== box)
			{
				hideBox($.fn.simpleColorPicker.selected);
			}

			if (opts.showEffect == 'fade')
				box.fadeIn();
			else if (opts.showEffect == 'slide')
				box.slideDown();
			else
				box.show();
			
			$.fn.simpleImagePicker.selected = box;
		}
	});
};
$.fn.simpleImagePicker.selected = null;
