class TipFactory {
    data = 'ola';
    $scope;
    form = {
        event_date: new Date,
        event_source_id: '4',
        bookmaker_id: '4',
        category_id: '1',
        paper_id: '',
        no_of_events: '',
        no_of_keys: '',
        days_of_operations: '',
        pricing: ''
    };


    constructor($scope) {
        this.$scope = $scope;

        this.fetch_data();
    }

    get formdata() {

        return this.data;
    }

    get events() {

        return this.data.events;
    }

    fetch_data() {
        let $url = `${$base_url}/factory/factory`;

        $.ajax({
            type: "POST",
            url: $url,
            cache: false,
            contentType: false,
            processData: false,
            data: {},
            success: (data) => {
                this.data = data.factory_data;
                this.$scope.$apply();
            },
            error: function(data) {
                //alert("fail"+data);
            }

        });
    }

    create_tips() {
        let $url = `${$base_url}/factory/create_tips`;
        $("#page_preloader").css('display', 'block');

        try {
            $.ajax({
                type: "POST",
                url: $url,
                cache: false,
                contentType: false,
                processData: false,
                data: JSON.stringify(this.form),
                success: (data) => {
                    // this.fetch_data();
                    $("#page_preloader").css('display', 'none');
                    notify();
                },
                error: function(data) {
                    //alert("fail"+data);
                },
                finally: (data) => {
                    $("#page_preloader").css('display', 'none');
                }

            });
        } catch (error) {

        }
    }


}

class Event {
    data = 'ola';
    $scope;
    form = {
        event_date: new Date,
        fetcher_id: '4',
        category_id: '1',
        what_to_fetch: 'soccer'
    };


    constructor($scope) {
        this.$scope = $scope;

        this.fetch_data();
    }

    get formdata() {

        return this.data;
    }

    get events() {

        return this.data.events;
    }

    fetch_data() {
        let $url = `${$base_url}/factory/fetch_event_data`;

        $.ajax({
            type: "POST",
            url: $url,
            cache: false,
            contentType: false,
            processData: false,
            data: {},
            success: (data) => {
                this.data = data.events_data;
                this.$scope.$apply();
            },
            error: function(data) {
                //alert("fail"+data);
            }

        });
    }

    fetch_event() {
        let $url = `${$base_url}/factory/fetch_event`;
        $("#page_preloader").css('display', 'block');

        $.ajax({
            type: "POST",
            url: $url,
            cache: false,
            contentType: false,
            processData: false,
            data: JSON.stringify(this.form),
            success: (data) => {
                console.log(this.data);
                notify();
                this.fetch_data();
                $("#page_preloader").css('display', 'none');
            },
            error: function(data) {
                //alert("fail"+data);
            },
            finally: (data) => {
                $("#page_preloader").css('display', 'none');
            }

        });
    }


}




class Factory {
    event;
    tips_factory;
    constructor($scope) {
        this.event = new Event($scope);
        this.tips_factory = new TipFactory($scope);
        this.$scope = $scope;
    }



}




app.controller('FactoryController', function($scope, $http, $sce) {
    $scope.$factory = new Factory($scope);

    $scope.isToday = function(evnt_date) {
        let today = new Date;
        let event_date = new Date(evnt_date);
        return today.getDate() == event_date.getDate();
    };
});

app.directive("compileHtml", function($parse, $sce, $compile) {
    return {
        restrict: "A",
        link: function(scope, element, attributes) {

            var expression = $sce.parseAsHtml(attributes.compileHtml);

            var getResult = function() {
                return expression(scope);
            };

            scope.$watch(getResult, function(newValue) {
                var linker = $compile(newValue);
                element.append(linker(scope));
            });
        }
    }
});