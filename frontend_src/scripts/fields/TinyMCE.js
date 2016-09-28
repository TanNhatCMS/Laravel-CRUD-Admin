let $S = require('scriptjs');
class TinyMCE {

	constructor() {
		$S([
			window.ASSET_DIR + '/vendor/backpack/tinymce/tinymce.min.js',
			window.ASSET_DIR + '/vendor/backpack/tinymce/jquery.tinymce.min.js',
		], this.initFields.bind(this));
	}

	initFields() {
		tinymce.init({
			selector: "textarea.tinymce",
			skin: "dick-light",
			plugins: "image,link,media,anchor",
			file_browser_callback : this.elFinderBrowserCallback
		});
	}

	elFinderBrowserCallback(field_name, url, type, win) {
		tinymce.activeEditor.windowManager.open({
			file: window.TINYMCE_ELFINDER,// use an absolute path!
			title: 'elFinder 2.0',
			width: 900,
			height: 450,
			resizable: 'yes'
		}, {
			setUrl: function (url) {
				win.document.getElementById(field_name).value = url;
			}
		});
		return false;
	}

}
module.exports = TinyMCE;