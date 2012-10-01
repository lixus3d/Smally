
if(window.Smally===undefined) window.Smally = {};

$(document).ready(function(){
	Smally.DefaultValue();
	if(Smally.ajaxReady){
		Smally.ajaxReady.push('Smally.DefaultValue');
	}
});

Smally.DefaultValue = function(){
	$('[data-form-defaultValue]').each(function(){
		var el = $(this);
		var defaultValue = el.attr('data-form-defaultValue');
		if(el.val() == defaultValue){
			el.addClass('jsDefaultValue');
		}
		el.blur(function(){
			window.setTimeout(function(){
				if(el.val() == ''){
					el.val(defaultValue);
					el.addClass('jsDefaultValue');
				}
			},150);
		}).focus(function(){
			if(el.val() != ''){
				if(el.val() == defaultValue){
					el.val('');
				}
				if(el.hasClass('jsDefaultValue')){
					el.removeClass('jsDefaultValue');
				}
			}
		}).trigger('blur');
	});
};
