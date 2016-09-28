class UploadMultiple {

	constructor() {
		$("[data-file-clear]").click(function(e) {
			e.preventDefault();
			var container = $(this).parent().parent();
			var parent = $(this).parent();
			// remove the filename and button
			parent.remove();
			// if the file container is empty, remove it
			if ($.trim(container.html())=='') {
				container.remove();
			}

			const name = $(this).attr('data-file-clear');
			const id = '#' + name + '_file_input';

			$("<input type='hidden' name='clear_' + name + '[]' value='"+$(this).data('filename')+"'>").insertAfter(id);
		});

		$("[data-file-input-multiple]").change(function() {
			console.log($(this).val());
			// remove the hidden input, so that the setXAttribute method is no longer triggered
			$(this).next("input[type=hidden]").remove();
		});
	}

}
module.exports = UploadMultiple;