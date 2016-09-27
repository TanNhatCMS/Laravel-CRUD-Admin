let Address = require('./fields/Address');
let Base64_Image = require('./fields/Base64_Image');
let Browse = require('./fields/Browse');
let ChecklistDependency = require('./fields/ChecklistDependency');
let Ckeditor = require('./fields/Ckeditor');
let IconPicker = require('./fields/IconPicker');
let Image = require('./fields/Image');
let PageOrLink = require('./fields/PageOrLink');
let Select2 = require('./fields/Select2');
let Summernote = require('./fields/Summernote');
let Table = require('./fields/Table');
let TinyMCE = require('./fields/TinyMCE');
let Upload = require('./fields/Upload');
let UploadMultiple = require('./fields/UploadMultiple');
let Wysiwyg = require('./fields/Wysiwyg');

$(document).ready(function() {
	if( $('[data-address]').length ) {
		let addressFields = new Address();
	}

	if ( $('.base64-image').length ) {
		let imageFields = new Base64_Image();
	}

	if ( $('.summernote').length ) {
		let summernote = new Summernote();
	}

	if ( $('[data-file-manager]').length ) {
		let browse = new Browse();
	}

	if ( $('[data-ckeditor]').length ) {
		let ckeditor = new Ckeditor();
	}

	if ( $('[data-iconpicker]').length ) {
		let iconPicker = new IconPicker();
	}
});