(function(){
	var app = angular.module('tbk-security-directives', []);    

	app.directive("headerNavigation", function() {
		return {
			restrict: "E",
			templateUrl: "templates/header-navigation.html"
		};
	});

	app.directive("footerBar", function() {
		return {
			restrict: "E",
			templateUrl: "templates/footer-bar.html"
		};
	});
})();