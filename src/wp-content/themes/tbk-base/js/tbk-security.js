(function() {
	var app = angular.module('tbk-security', ['tbk-security-directives','ngMaterial', 'ngMdIcons']);	
	
	app.controller('MainController', ["$http",function($http){
		var site = this;
		console.log("hello***");
	}]);
})();
