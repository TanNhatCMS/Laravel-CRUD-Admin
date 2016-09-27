let $S = require('scriptjs');
class Summernote {

	constructor() {
		$S(window.ASSET_DIR + '/vendor/backpack/summernote/summernote.min.js', this.initFields);
	}

	initFields() {
		$('.summernote').summernote();
	}

}
module.exports = Summernote;