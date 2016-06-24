jQuery(function($){
	var tbk_toolbox = {
		ajax: function(action,id,confirmed){
			var me = this,
				config = {
					action: 'tbk_toolbox_ajax',
					subaction: action,
					module: id
				}
			;
			
			if (typeof confirmed != 'undefined'){
				config.confirmed = confirmed;
			}
			$.post(ajaxurl,config,
				function(result){
					try{
						result = $.parseJSON(result);
						if (typeof result.markup != 'undefined'){
							$('#'+id).parents('li').html(result.markup);
							$('#'+id).addClass('working');						
						}
						if (result.success && typeof result.call_again != 'undefined' && result.call_again !== false){
							switch(result.call_again){
							case true:
								setTimeout(function(){
									me.ajax(action,id);
								},1000);
								break;
							case 'confirm':
							default: 
								break;
							}
						}
						else{
							$('#'+id).removeClass('working');						
						}
					}
					catch(e){
						$('#'+id).removeClass('working');
						console.log(e);
						console.log(result);
					}
				}
			);
		},
		adjustRowNumbers: function($wrapper){
			var index = 0;
			$wrapper.find('fieldset').not('.fieldset_template').each(function(){
				var old_index = $(this).attr('class').match(/fieldset([0-9]+)/)[1];
				if (old_index != index){
					$(this).attr('class','fieldset'+index);
					var html = $(this).html();
					// replace [old_index] with [index] (for the input name attributes)
					html = html.replace(new RegExp('\\\['+old_index+'\\\]','g'),'['+index+']')
					
					// replace (id|for) attributes appropriately (so id=)
					html = html.replace(new RegExp('(id|for)(=[\'\"])([^\'\"]+--)'+old_index+'(--[^\'\"]+[\'\"])','g'),'$1$2$3'+index+'$4');
					$(this).html(html);
				}
				index++;
			});
		}
	}
	$('#toolbox-grid')
		.on('click','label',function(){
			var $input = $('#'+$(this).attr('for'));
			if ($input.hasClass('working')){
				// Do nothing while we're working
				return false;
			}
			$input.toggleClass('working');
			
			if (!$input.is(':checked')){
				// Not checked - we're going to AJAX enable the module
				tbk_toolbox.ajax('enable',$input.attr('id'));
			}
			else{
				// Checked - we're going to AJAX unenable the module
				tbk_toolbox.ajax('disable',$input.attr('id'));
			}
			return false;
		})
		.on('click','.module-confirm a',function(){
			var $input = $(this).parents('li').find('input.enable-module'),
				action = $(this).parents('.module-confirm').attr('id').match(/^module-confirm-(.*)$/)[1];
			if ($(this).hasClass('yes')){
				tbk_toolbox.ajax(action,$input.attr('id'),true);
			}
			else{
				tbk_toolbox.ajax(action,$input.attr('id'),false);
			}
			return false;
		})
	;
	
	$('body')
		.on('click','.tbk_toolbox-settings-form .delete-row',function(){
			if (confirm('Are you sure you want to do this?')){
				var $wrapper = $(this).parents('.hasmany-wrapper');
				$(this).parents('fieldset').remove();
				tbk_toolbox.adjustRowNumbers($wrapper);
			}
		})
		.on('click','.tbk_toolbox-settings-form .add-row',function(){
			var $wrapper = $(this).parents('.hasmany-wrapper'),
				count = $wrapper.find('fieldset').not('.fieldset_template').length,
				$template = $wrapper.find('.fieldset_template'),
				$new_row = $template.clone().removeClass('fieldset_template').addClass('fieldset'+count)
			;
			
			$new_row
				.html($new_row.html().replace(/__row__/g,count).replace(/_templates\[([^\]]+)\]/g,'$1'))
				.insertBefore($template);
		})
		.on('click','.hasmany-wrapper .field-wrapper.first label',function(){
			$(this).parents('fieldset').toggleClass('collapsed');
		})
	;
	
	$('#toolbox-grid').find('.hasmany-wrapper').sortable({
		axis: 'y',
		items: 'fieldset:not(.fieldset_template)',
		start: function(){
			$(this).data('collapsed',$(this).find('fieldset.collapsed'));
			$(this).find('fieldset').not('.fieldset_template').addClass('collapsed');
		},
		stop: function(){
			tbk_toolbox.adjustRowNumbers($(this));
			$(this).data('collapsed').addClass('collapsed');
			$(this).data('collapsed','');
			//$(this).find('fieldset').removeClass('collapsed');
		}
	})
});