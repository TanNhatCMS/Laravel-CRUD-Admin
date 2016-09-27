let $S = require('scriptjs');

class Ckeditor {

	constructor() {

		const _this = this;

		$S([
			window.ASSET_DIR + 'vendor/backpack/ckeditor/ckeditor.js',
		], function() {
			$S([window.ASSET_DIR + 'vendor/backpack/ckeditor/adapters/jquery.js'], _this.initFields);
		});

	}

	initFields() {
		$('[data-ckeditor]' ).ckeditor({
			"filebrowserBrowseUrl": window.CKEDITOR_FILEBROWSE_URL,
			"extraPlugins" : 'oembed,widget'
		});
	}

}

module.exports = Ckeditor;