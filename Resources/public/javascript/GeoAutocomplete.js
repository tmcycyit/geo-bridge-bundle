'use strict';

angular.module("GeoAutocomplete",['YitAutocomplete','Geo'])
    .run([function(){
        var element = angular.element('div[ng-app="AGM"]');
        if(angular.isDefined(element) && element) {
            angular.bootstrap(element[0],['AGM']);
        }
    }]);