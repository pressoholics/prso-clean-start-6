jQuery(function ($) {

	var multilingualPressPointers = MultilingualPressPointersData.pointers;
	var ajaxUrl = MultilingualPressPointersData.ajaxurl;
	var ajaxAction = MultilingualPressPointersData.ajaxAction;

	function initMultilingualPressPointers() {
		$.each(multilingualPressPointers, function (i) {
			showMultilingualpressPointer(i);
			return false;
		});
	}

	setTimeout(initMultilingualPressPointers, 800);

	function showMultilingualpressPointer(id) {
		var pointer = multilingualPressPointers[id];
		var options = $.extend(pointer.options, {
			pointerClass: 'wp-pointer',
			close: function () {
				if (pointer.next) {
					showMultilingualpressPointer(pointer.next);
				}
			},
			buttons: function (event, t) {
				var close = 'Dismiss guide',
					next = 'OK',
					button = $('<a class="close" style="float:left;margin:6px 0 0 15px;" href="#">'
						+ close + '</a>'),
					button2 = $('<a class="button button-primary" href="#">' + next + '</a>'),
					wrapper = $('<div class="" />');

				button.bind('click.pointer', function (e) {
					e.preventDefault();
					$.post( ajaxUrl, {
						pointer: id,
						action: ajaxAction
					});
					t.element.pointer('destroy');
				});

				button2.bind('click.pointer', function (e) {
					e.preventDefault();
					t.element.pointer('close');
				});

				wrapper.append(button);
				wrapper.append(button2);

				return wrapper;
			},
		});
		var thisPointer = $(pointer.target).pointer(options);
		thisPointer.pointer('open');

		if (pointer.nextTrigger) {
			$(pointer.nextTrigger.target).on(pointer.nextTrigger.event, function () {
				setTimeout(function () {
					thisPointer.pointer('close');
				}, 400);
			});
		}
	}
});
