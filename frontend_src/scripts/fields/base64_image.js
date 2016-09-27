let $S = require('scriptjs');
class Base64_Image {

	constructor() {
		const _this = this;
		$S(window.ASSET_DIR + 'vendor/backpack/cropper/dist/cropper.min.js', function() {
			_this.initFields();
		});
	}

	initFields() {
		// Loop through all instances of the image field
		$('.form-group.image').each(function(index){
			// Find DOM elements under this form-group element
			var $mainImage = $(this).find('#mainImage');
			var $uploadImage = $(this).find("#uploadImage");
			var $hiddenImage = $(this).find("#hiddenImage");
			var $hiddenFilename = $(this).find("#hiddenFilename");
			var $rotateLeft = $(this).find("#rotateLeft");
			var $rotateRight = $(this).find("#rotateRight");
			var $zoomIn = $(this).find("#zoomIn");
			var $zoomOut = $(this).find("#zoomOut");
			var $reset = $(this).find("#reset");
			var $remove = $(this).find("#remove");
			// Options either global for all image type fields, or use 'data-*' elements for options passed in via the CRUD controller
			var options = {
				viewMode: 2,
				checkOrientation: false,
				autoCropArea: 1,
				responsive: true,
				preview : $(this).attr('data-preview'),
				aspectRatio : $(this).attr('data-aspectRatio')
			};
			var crop = $(this).attr('data-crop');

			// Hide 'Remove' button if there is no image saved
			if (!$mainImage.attr('src')){
				$remove.hide();
			}
			// Initialise hidden form input in case we submit with no change
			$hiddenImage.val($mainImage.attr('src'));


			// Only initialize cropper plugin if crop is set to true
			if(crop){

				$remove.click(function() {
					$mainImage.cropper("destroy");
					$mainImage.attr('src','');
					$hiddenImage.val('');
					if (filename == "true"){
						$hiddenFilename.val('removed');
					}
					$rotateLeft.hide();
					$rotateRight.hide();
					$zoomIn.hide();
					$zoomOut.hide();
					$reset.hide();
					$remove.hide();
				});
			} else {

				$(this).find("#remove").click(function() {
					$mainImage.attr('src','');
					$hiddenImage.val('');
					$hiddenFilename.val('removed');
					$remove.hide();
				});
			}

			//Set hiddenFilename field to 'removed' if image has been removed.
			//Otherwise hiddenFilename will be null if no changes have been made.

			$uploadImage.change(function() {
				var fileReader = new FileReader(),
					files = this.files,
					file;

				if (!files.length) {
					return;
				}
				file = files[0];

				if (/^image\/\w+$/.test(file.type)) {
					$hiddenFilename.val(file.name);
					fileReader.readAsDataURL(file);
					fileReader.onload = function () {
						$uploadImage.val("");
						if(crop){
							$mainImage.cropper(options).cropper("reset", true).cropper("replace", this.result);
							// Override form submit to copy canvas to hidden input before submitting
							$('form').submit(function() {
								var imageURL = $mainImage.cropper('getCroppedCanvas').toDataURL();
								$hiddenImage.val(imageURL);
								return true; // return false to cancel form action
							});
							$rotateLeft.click(function() {
								$mainImage.cropper("rotate", 90);
							});
							$rotateRight.click(function() {
								$mainImage.cropper("rotate", -90);
							});
							$zoomIn.click(function() {
								$mainImage.cropper("zoom", 0.1);
							});
							$zoomOut.click(function() {
								$mainImage.cropper("zoom", -0.1);
							});
							$reset.click(function() {
								$mainImage.cropper("reset");
							});
							$rotateLeft.show();
							$rotateRight.show();
							$zoomIn.show();
							$zoomOut.show();
							$reset.show();
							$remove.show();

						} else {
							$mainImage.attr('src',this.result);
							$hiddenImage.val(this.result);
							$remove.show();
						}
					};
				} else {
					alert("Please choose an image file.");
				}
			});

		});
	}
	
}

module.exports = Base64_Image;