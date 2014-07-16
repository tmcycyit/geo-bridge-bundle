'use stricts';

angular.module("Geo")
        .directive("yiInput",function($parse){
    return {
        restrict: 'E',
        require: '?ngModel',
        link: function (scope, element, attrs) {
            if (attrs.ngModel) {
                var val = attrs.value || element.text();
                $parse(attrs.ngModel).assign(scope, val);
            }
        }
    };
});