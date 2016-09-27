let $S = require('scriptjs');
class Browse {

	constructor() {

		$S([
			window.ASSET_DIR + 'vendor/backpack/colorbox/jquery.colorbox-min.js'
		], this.initFields);
		
	}

	initFields() {
		$(document).on('click','.popup_selector', function (event) {
			event.preventDefault();

			// trigger the reveal modal with elfinder inside
			var triggerUrl = window.ELFINDER_BROWSE_URL_BASE + '/' + $(this).attr('data-inputid');
			$.colorbox({
				href: triggerUrl,
				fastIframe: true,
				iframe: true,
				width: '70%',
				height: '50%'
			});

			// TODO make less hacky
			window.processSelectedFile = function(filePath, requestingField) {
				$('#' + requestingField).val(filePath);
			}

		});

		// $(document).on('click','.clear_elfinder_picker[data-inputid={{ $field['name'] }}-filemanager]',function (event) {
		$(document).on('click','.clear_elfinder_picker', function (event) {
			event.preventDefault();
			var updateID = $(this).attr('data-inputid'); // Btn id clicked
			$("#"+updateID).val("");
		});
	}

}

module.exports = Browse;