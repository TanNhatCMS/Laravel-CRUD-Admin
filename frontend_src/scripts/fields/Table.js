let $S = require('scriptjs');

class Table {

	constructor() {

		$S([
			'https://cdnjs.cloudflare.com/ajax/libs/angular.js/1.5.8/angular.min.js',
			'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js',
			'https://cdnjs.cloudflare.com/ajax/libs/angular-ui-sortable/0.14.3/sortable.min.js',
		], this.init);

	}

	init() {
		angular.module('backpackTable', ['ui.sortable'], function($interpolateProvider){
			$interpolateProvider.startSymbol('<%');
			$interpolateProvider.endSymbol('%>');
		}).controller('tableController', function($scope){

			$scope.sortableOptions = {
				handle: '.sort-handle'
			};

			$scope.addItem = function(){

				var item = {};

				if( $scope.max > -1 ){
					if( $scope.items.length < $scope.max ){
						$scope.items.push(item);
					} else {
						new PNotify({
							title: $scope.maxErrorTitle,
							text: $scope.maxErrorMessage,
							type: 'error'
						});
					}
				} else {
					$scope.items.push(item);
				}
			};

			$scope.removeItem = function(item){
				var index = $scope.items.indexOf(item);
				$scope.items.splice(index, 1);
			};

			$scope.$watch('items', function(a, b){

				if( $scope.min > -1 ){
					while($scope.items.length < $scope.min){
						$scope.addItem();
					}
				}

				if( typeof $scope.items != 'undefined' && $scope.items.length ){

					if( typeof $scope.field != 'undefined'){
						if( typeof $scope.field == 'string' ){
							$scope.field = $($scope.field);
						}
						$scope.field.val( angular.toJson($scope.items) );
					}
				}
			}, true);

			if( $scope.min > -1 ){
				for(var i = 0; i < $scope.min; i++){
					$scope.addItem();
				}
			}
		});
	}

}

module.exports = Table;