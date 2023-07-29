
app.controller('ChartOfAccountController', function($scope, $http) {

		$scope.$charts_of_accounts = [];
		$scope.$array = [4,4,4];
		$scope.$has_opening_bal;


		$scope.fetch_page_content = function () {
			$page = 1;

					$http.get($base_url+'/accounts/fetch_chart_of_accounts/'+$page)
					    .then(function(response) {

						    for(key in response.data ){
						    	$chart_of_account = response.data[key];
						    	$scope.$charts_of_accounts.push($chart_of_account);
						    }

						    console.log($scope.$charts_of_accounts);
					});

			}

		$scope.fetch_page_content();
});