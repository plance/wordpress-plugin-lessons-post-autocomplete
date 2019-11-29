jQuery(document).ready(function($)
{
	jQuery(".post-autocomplete__field").autocomplete({
		source: function(request, response) {
			jQuery(".post-autocomplete__field").addClass('post-autocomplete__field-loader');
			
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
			jQuery(".post-autocomplete__field").removeClass('post-autocomplete__field-loader');
		},
		select: function(e, ui)
		{
			jQuery(".post-autocomplete__field").val(ui.item.value);
			jQuery('.post-autocomplete-form')[0].submit();
		},
		minLength: 3,
		appendTo: '.post-autocomplete-form',
	});
});
