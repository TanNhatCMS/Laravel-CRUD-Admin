let $S = require('scriptjs');
class Select2_Multiple {

	constructor() {
		$S([
			window.ASSET_DIR + 'vendor/backpack/select2/select2.js'
		], this.initFields);
	}

	initFields() {
		// trigger select2 for each untriggered select2 box
		$('.select2').each(function (i, obj) {
			if (!$(obj).data("select2")) {
				$(obj).select2();
			}
		});
	}

}
module.exports = Select2_Multiple;