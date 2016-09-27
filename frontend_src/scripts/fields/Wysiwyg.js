let $S = require('scriptjs');
class Wysiwyg {

	constructor() {
		$S([
			window.ASSET_DIR + 'vendor/backpack/ckeditor/ckeditor.js',
			window.ASSET_DIR + 'vendor/backpack/ckeditor/adapters/jquery.js',
		], this.initFields);
	}

	initFields() {
		$('textarea.ckeditor' ).ckeditor({
			"filebrowserBrowseUrl": window.FILEBROWSE_URL,
			"extraPlugins" : 'oembed,widget'
		});
	}

}
module.exports = Wysiwyg;