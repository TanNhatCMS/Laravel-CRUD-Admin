class UploadMultiple {

	constructor() {
		$(".file-clear-button").click(function(e) {
			e.preventDefault();
			var container = $(this).parent().parent();
			var parent = $(this).parent();
			// remove the filename and button
			parent.remove();
			// if the file container is empty, remove it
			if ($.trim(container.html())=='') {
				container.remove();
			}
			// $("<input type='hidden' name='clear_{{ $field['name'] }}[]' value='"+$(this).data('filename')+"'>").insertAfter("#{{ $field['name'] }}_file_input");
			// TODO
		});

		// $("#{{ $field['name'] }}_file_input").change(function() {
		$("file_input").change(function() { // TODO
			console.log($(this).val());
			// remove the hidden input, so that the setXAttribute method is no longer triggered
			$(this).next("input[type=hidden]").remove();
		});
	}

}
module.exports = UploadMultiple;