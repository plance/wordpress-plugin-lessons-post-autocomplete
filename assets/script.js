jQuery(document).ready(function($)
{
	jQuery(".post-autocomplete-field").autocomplete({
		source: function(request, response) {
			jQuery(".post-autocomplete-field").addClass('post-autocomplete-field__loader');
			
			$.post(WpPostAutocomplete.ajax, {
				term: request.term,
				action: WpPostAutocomplete.action,
				security: WpPostAutocomplete.security,
			}, function(data)
			{
				if(data.success == false)
				{
					alert(data.data.message);
					return false;
				}

				response($.map(data.data, function(value) {
					return  {
						value: value.title,
						label: value.title,
					};
				}));
			}, 'json');
		},
		open: function()
		{
			jQuery(".post-autocomplete-field").removeClass('post-autocomplete-field__loader');
		},
		select: function(e, ui)
		{
			jQuery(".post-autocomplete-field").val(ui.item.value);
			jQuery('.wrap-post-autocomplete')[0].submit();
		},
		minLength: 3,
		appendTo: '.wrap-post-autocomplete',
	});
});
