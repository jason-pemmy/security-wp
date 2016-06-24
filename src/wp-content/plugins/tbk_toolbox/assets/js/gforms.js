(function($) {

	var text_required_msg = "**Required**";
	var phone_required_msg = "**Invalid Phone Format**";
	var email_required_msg = "**Invalid Email**";


	function is_valid_email_address(email_address) {
		var pattern = new RegExp(/^(("[\w-+\s]+")|([\w-+]+(?:\.[\w-+]+)*)|("[\w-+\s]+")([\w-+]+(?:\.[\w-+]+)*))(@((?:[\w-+]+\.)*\w[\w-+]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][\d]\.|1[\d]{2}\.|[\d]{1,2}\.))((25[0-5]|2[0-4][\d]|1[\d]{2}|[\d]{1,2})\.){2}(25[0-5]|2[0-4][\d]|1[\d]{2}|[\d]{1,2})\]?$)/i);
		return pattern.test(email_address)
	}

	function is_valid_phone(phone_number) {
		var pattern = new RegExp(/^\(?([0-9]{3})\)?[-. ]?([0-9]{3})[-. ]?([0-9]{4})$/);
		return pattern.test(phone_number)

	}

	function tbkcp_validate_gform($t) {
		var error = false;

		$t.find('.gfield_contains_required ').each(function() {
			var $label = $(this).find('label');
			var $input = $(this).find('input');

			if ($input.length > 0 && ($input.val() == '' || $input.val() == $input.data('default_value')) || $input.val() == text_required_msg || $input.val() == phone_required_msg || $input.val() == email_required_msg) {
				$label.addClass('validation-error');
				$input.val(text_required_msg);
				if (!$input.hasClass('focus_blur')) {
					$input.addClass('focus_blur')
				}
				$input.addClass('validation-error');
				error = true;

			} else {
				$input.removeClass('validation-error');
				$label.removeClass('validation-error');

			}
			var $textarea = $(this).find('textarea');
			if ($textarea.length > 0 && ($textarea.val() == '' || $textarea.val() == $textarea.data('default_value')) || $textarea.val() == text_required_msg) {
				$textarea.val(text_required_msg);
				$textarea.addClass('focus_blur').addClass('validation-error');
				error = true;
			} else {
				$textarea.removeClass('validation-error');
			}

			var $select = $(this).find('select');

			if ($select.length > 0 && $select.find('option:selected').val() == '') {
				$label.addClass('validation-error');
				$select.addClass('validation-error');
				error = true;

			} else {
				$select.removeClass('validation-error');
				$label.removeClass('validation-error');
			}

			var $checkbox = $(this).find('input[type=checkbox]');
			if ($checkbox.length > 0 && !$checkbox.attr('checked')) {
				$label.addClass('validation-error');
				$checkbox.addClass('validation-error');
				error = true;
			} else {
				$checkbox.removeClass('validation-error');
				$label.removeClass('validation-error');
			}

			$('.focus_blur').on("focus", function() {
				if ($(this).val() == text_required_msg || $(this).val() == email_required_msg || $(this).val() == phone_required_msg) {
					$(this).val('');
				}
			});

		});

		var names = [];

		$('.gfield_contains_required input[type="radio"]').each(function() {
			// Creates an array with the names of all the different radio group.
			names[$(this).attr('name')] = true;
		});

		// Goes through all the names and make sure there's at least one checked.

		for (var name in names) {
			var radio_buttons = $("input[name='" + name + "']");
			var $label = radio_buttons.parent().find('label').addClass('validation-error');
			if (radio_buttons.filter(':checked').length == 0) {
				$label.addClass("validation-error");
				error = true;

			} else {
				$label.removeClass('validation-error');
			}
		}

		$t.find('.gfield_contains_required.email ').each(function() {
			var $label = $(this).find('label')
			if (!is_valid_email_address($(this).find('input').val())) {
				$label.addClass("validation-error")
				$(this).find('input').val(email_required_msg);
				$(this).addClass('focus_blur').addClass('validation-error');
				error = true;
			} else {
				$(this).removeClass('validation-error');
				$label.removeClass("validation-error")
			}

		})

		$t.find('.gfield_contains_required.phone ').each(function() {
			var $label = $(this).find('label');

			if (!is_valid_phone($(this).find('input').val())) {
				$(this).find('input').val(phone_required_msg);
				$(this).addClass('focus_blur').addClass('validation-error');
				error = true;
			} else {
				$(this).removeClass('validation-error');
				$label.removeClass('validation-error');
			}

		})
		return error;
	}

	jQuery.fn.tbkcp_gforms_validation = function() {

		var $t = $(this[0]);
		var args = arguments[0] || {};
		var show_star = args.show_star
		var $star_append = $t.find(args.star_append_selector);
		var $msg_box = $t.find(args.msg_box_selector);
		var $submit_button = $t.find(args.submit_button_selector);
		var msg_text = args.msg_text;

		if (show_star) {
			if ($star_append.length < 1)
				$star_append = $t.find('.gform_body li.gfield_contains_required .ginput_container');

			$star_append.append('<div class ="form_required">*</div>')
		}

		if (!msg_text)
			msg_text = '**There were problems with the submission. Required fields highlighted in red**';

		if ($submit_button.length < 1) {
			$submit_button = $t.find('.gform_button');
			if ($submit_button.length == 0) {
				$submit_button = $t.find('.gform_image_button');
			}
		}

		if ($t.find('#gforms_msg_error').length < 1) {
			$msg_box.append('<div id ="gforms_msg_error"></div>');
		}



		$submit_button.click(function(event) {

			var error = false;
			error = tbkcp_validate_gform($t);
			if (error) {
				$(this).addClass('has_errors');
				event.preventDefault();
				event.stopPropagation();

				$t.find('#gforms_msg_error').html(msg_text);
			} else {
				$(this).removeClass('has_errors');
				$t.find('#gforms_msg_error').html('');
				$t.submit();
			}

		})

	};

	jQuery.fn.tbkcp_gforms_focus_blur = function() {
		var $t = $(this[0]);
		$t.find('input[type=text], textarea').each(function() {
			$(this).data('default_value', $(this).val());

			$(this).focus(function() {
				$(this).removeClass('validation-error');
				if ($(this).val() == $(this).prop("defaultValue")) {
					$(this).val('');
				}
			})
			$(this).blur(function() {
				if ($(this).val() == '') {
					$(this).val($(this).data('default_value'));
				}
			})


		})

	}

	jQuery.fn.tbkcp_gforms_multistep = function() {
		var $t = $(this[0]);
		var $slide;
		var $next_slide;
		var $prev_slide;
		var $first_slide = $t.find('.step:first');
		var $last_slide = $t.find('.step:last');
		var args = arguments[0] || {};
		var num_steps = args.num_steps;
		var $btn_next = $(args.next_btn_selector);
		var $btn_prev = $(args.prev_btn_selector);
		var $submit_button = $t.find('.gform_button');

		if ($submit_button.length == 0) {
			$submit_button = $t.find('.gform_image_button');
		}

		var error = false;
		$t.find('.gform_fields').append($('.step'));
		$btn_next.click(function() {
			$slide = $t.find('.step:visible');
			$next_slide = $slide.next();

			error = tbkcp_validate_gform($slide);
			if (error) {

				$t.find('#gforms_msg_error').html('**There were problems with the submission. Required fields highlighted in red**');
			} else {
				$t.find('#gforms_msg_error').html('');
				if ($next_slide.index() == $last_slide.index()) {
					$btn_next.fadeOut('fast');
					$submit_button.fadeIn('fast');
				}
				$slide.fadeOut(function() {
					$next_slide.fadeIn();

					$btn_prev.fadeIn();
				})
			}


		})

		$btn_prev.click(function() {
			$t.find('#gforms_msg_error').html('');
			$slide = $t.find('.step:visible');
			$prev_slide = $slide.prev();
			//if($prev_slide.index() != $last_slide.index()){
			$submit_button.fadeOut('fast');
			$btn_next.fadeIn('fast');
			//}
			if ($prev_slide.index() == 1) {
				$btn_prev.fadeOut('fast');
			} else {
				$btn_prev.fadeIn();
			}
			$slide.fadeOut(function() {
				$prev_slide.fadeIn();
				//console.log($prev_slide.index());


			})
		})

		$submit_button.click(function() {
			$btn_prev.fadeOut();
			$btn_next.fadeOut();
			$(this).fadeOut();



		})

	}


})(jQuery);

/*! http://mths.be/placeholder v2.0.7 by @mathias */
;
(function(f, h, $) {
	var a = 'placeholder' in h.createElement('input'), d = 'placeholder' in h.createElement('textarea'), i = $.fn, c = $.valHooks, k, j;
	if (a && d) {
		j = i.placeholder = function() {
			return this
		};
		j.input = j.textarea = true
	} else {
		j = i.placeholder = function() {
			var l = this;
			l.filter((a ? 'textarea' : ':input') + '[placeholder]').not('.placeholder').bind({'focus.placeholder': b, 'blur.placeholder': e}).data('placeholder-enabled', true).trigger('blur.placeholder');
			return l
		};
		j.input = a;
		j.textarea = d;
		k = {get: function(m) {
				var l = $(m);
				return l.data('placeholder-enabled') && l.hasClass('placeholder') ? '' : m.value
			}, set: function(m, n) {
				var l = $(m);
				if (!l.data('placeholder-enabled')) {
					return m.value = n
				}
				if (n == '') {
					m.value = n;
					if (m != h.activeElement) {
						e.call(m)
					}
				} else {
					if (l.hasClass('placeholder')) {
						b.call(m, true, n) || (m.value = n)
					} else {
						m.value = n
					}
				}
				return l
			}};
		a || (c.input = k);
		d || (c.textarea = k);
		$(function() {
			$(h).delegate('form', 'submit.placeholder', function() {
				var l = $('.placeholder', this).each(b);
				setTimeout(function() {
					l.each(e)
				}, 10)
			})
		});
		$(f).bind('beforeunload.placeholder', function() {
			$('.placeholder').each(function() {
				this.value = ''
			})
		})
	}
	function g(m) {
		var l = {}, n = /^jQuery\d+$/;
		$.each(m.attributes, function(p, o) {
			if (o.specified && !n.test(o.name)) {
				l[o.name] = o.value
			}
		});
		return l
	}
	function b(m, n) {
		var l = this, o = $(l);
		if (l.value == o.attr('placeholder') && o.hasClass('placeholder')) {
			if (o.data('placeholder-password')) {
				o = o.hide().next().show().attr('id', o.removeAttr('id').data('placeholder-id'));
				if (m === true) {
					return o[0].value = n
				}
				o.focus()
			} else {
				l.value = '';
				o.removeClass('placeholder');
				l == h.activeElement && l.select()
			}
		}
	}
	function e() {
		var q, l = this, p = $(l), m = p, o = this.id;
		if (l.value == '') {
			if (l.type == 'password') {
				if (!p.data('placeholder-textinput')) {
					try {
						q = p.clone().attr({type: 'text'})
					} catch (n) {
						q = $('<input>').attr($.extend(g(this), {type: 'text'}))
					}
					q.removeAttr('name').data({'placeholder-password': true, 'placeholder-id': o}).bind('focus.placeholder', b);
					p.data({'placeholder-textinput': q, 'placeholder-id': o}).before(q)
				}
				p = p.removeAttr('id').hide().prev().attr('id', o).show()
			}
			p.addClass('placeholder');
			p[0].value = p.attr('placeholder')
		} else {
			p.removeClass('placeholder')
		}
	}}
(this, document, jQuery));
