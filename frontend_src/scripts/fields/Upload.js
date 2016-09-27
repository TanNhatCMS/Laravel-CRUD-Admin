class Upload {

	constructor() {
		// TODO
		$("#{{ $field['name'] }}_file_clear_button").click(function(e) {
			e.preventDefault();
			$(this).parent().addClass('hidden');

			// TODO
			var input = $("#{{ $field['name'] }}_file_input");
			input.removeClass('hidden');
			input.attr("value", "").replaceWith(input.clone(true));
			// add a hidden input with the same name, so that the setXAttribute method is triggered
			$("<input type='hidden' name='{{ $field['name'] }}' value=''>").insertAfter("#{{ $field['name'] }}_file_input");
		});

		// TODO
		$("#{{ $field['name'] }}_file_input").change(function() {
			console.log($(this).val());
			// remove the hidden input, so that the setXAttribute method is no longer triggered
			$(this).next("input[type=hidden]").remove();
		});
	}

}
module.exports = Upload;