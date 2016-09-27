let $S = require('scriptjs');
class Address {

	constructor() {
		const _this = this;
		$S('https://cdn.jsdelivr.net/places.js/1/places.min.js', function() {
			_this.initFields();
		});
	}

	initFields() {
		window.AlgoliaPlaces = window.AlgoliaPlaces || {};

		$('[data-address]').each(function(){

			var $this      = $(this),
				$addressConfig = $this.data('address'),
				$field = $('[name="'+$addressConfig.field+'"]'),
				$place = places({
					container: $this[0]
				});

			if( $addressConfig.full ){

				$place.on('change', function(e){
					var result = JSON.parse(JSON.stringify(e.suggestion));
					delete(result.highlight); delete(result.hit); delete(result.hitIndex);
					delete(result.rawAnswer); delete(result.query);
					$field.val( JSON.stringify(result) );
				});

				var existingData = JSON.parse($field.val());
				$this.val(existingData.value);
			}

			window.AlgoliaPlaces[ $addressConfig.field ] = $place;
		});
	}

}

module.exports = Address;