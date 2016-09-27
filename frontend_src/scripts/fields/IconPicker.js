let $S = require('scriptjs');

class IconPicker {

	// TODO find a good way to load the different icon sets

	constructor() {
		$S([
			window.ASSET_DIR + 'vendor/backpack/bootstrap-iconpicker/bootstrap-iconpicker/js/bootstrap-iconpicker.min.js'
		], this.initFields);

	}

	initFields() {
		$('button[role=iconpicker]').on('change', function(e) {
			$(this).siblings('input[type=hidden]').val(e.icon);
		});
	}

}

module.exports = IconPicker;