app.controller('Settings', function($scope, $http) {



    $scope.fetch_payment_gateway_settings = function() {
        $http.get($base_url + "/settings/fetch_payment_gateway_settings/")
            .then(function(response) {
                $scope.$payment_gateway_settings = response.data;
            });

    };
    $scope.fetch_payment_gateway_settings();



    $scope.fetch_site_settings = function() {
        $http.get($base_url + "/settings/fetch_site_settings/")
            .then(function(response) {
                $scope.$site_settings = response.data;
            });

    };
    $scope.fetch_site_settings();



    $scope.fetch_commission_settings = function() {
        $http.get($base_url + "/settings/fetch_commission_settings/")
            .then(function(response) {
                $scope.$commission_settings = response.data;
            });

    };
    $scope.fetch_commission_settings();



    $scope.pool_commission = function() {
        $http.get($base_url + "/settings/fetch/pool_commission")
            .then(function(response) {
                $scope.$pool_commission = response.data;
            });

    };
    $scope.pool_commission();



    $scope.leadership_ranks = function() {
        $http.get($base_url + "/settings/fetch/leadership_ranks")
            .then(function(response) {
                $scope.$leadership_ranks = response.data;
            });

    };
    $scope.leadership_ranks();





    $scope.rules_settings = function() {
        $http.get($base_url + "/settings/fetch/rules_settings")
            .then(function(response) {
                $scope.$rules_settings = response.data;
            });

    };
    $scope.rules_settings();


    $scope.live_chat_installation = function() {
        $http.get($base_url + "/settings/fetch/live_chat_installation")
            .then(function(response) {
                $scope.$live_chat_installation = response.data;
            });

    };
    $scope.live_chat_installation();





});









app.filter('replace', [function() {

    return function(input, from, to) {

        if (input === undefined) {
            return;
        }

        var regex = new RegExp(from, 'g');
        return input.replace(regex, to);

    };


}]);




app.directive("contenteditable", function() {
    return {
        restrict: "A",
        require: "ngModel",
        link: function(scope, element, attrs, ngModel) {

            function read() {
                ngModel.$setViewValue(element.html());
            }

            ngModel.$render = function() {
                element.html(ngModel.$viewValue || "");
            };

            element.bind("blur keyup change", function() {
                scope.$apply(read);
            });
        }
    };
});