let $S = require('scriptjs');
class TinyMCE {

	constructor() {
		$S([
			window.ASSET_DIR + 'vendor/backpack/tinymce/tinymce.min.js',
			window.ASSET_DIR + 'admin/js/vendor/tinymce/jquery.tinymce.min.js',
		], this.initFields);
	}

	initFields() {
		tinymce.init({
			selector: "textarea.tinymce",
			skin: "dick-light",
			plugins: "image,link,media,anchor",
			file_browser_callback : this.elFinderBrowserCallback,
		});
	}

	elFinderBrowserCallback() {
		function elFinderBrowser (field_name, url, type, win) {
			tinymce.activeEditor.windowManager.open({
				// TODO
				//file: '{{ url('admin/elfinder/tinymce4') }}',// use an absolute path!
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

}
module.exports = TinyMCE;