class Upload {

	constructor() {
		$("[data-file-clear]").click(function(e) {
			e.preventDefault();
			$(this).parent().addClass('hidden');
			
			const name = $(this).attr('data-file-clear');
			const id = '#' + name + '_file_input';
			const input = $(id);
			input.removeClass('hidden');
			input.attr("value", "").replaceWith(input.clone(true));
			// add a hidden input with the same name, so that the setXAttribute method is triggered
			$("<input type='hidden' name='" + name + "' value=''>").insertAfter(id);
		});

		$("[data-file-input]").change(function() {
			console.log($(this).val());
			// remove the hidden input, so that the setXAttribute method is no longer triggered
			$(this).next("input[type=hidden]").remove();
		});
	}

}
module.exports = Upload;