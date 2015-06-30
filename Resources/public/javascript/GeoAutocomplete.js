'use strict';

angular.module("GeoAutocomplete",['YitAutocomplete','Geo'])
    .run([function(){
        var element = angular.element('div[ng-app="AGM"]');
        if(angular.isDefined(element) && element && element.length) {
            angular.bootstrap(element[0],['AGM']);
        }
    }]);