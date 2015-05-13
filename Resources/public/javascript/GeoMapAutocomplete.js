'use strict';

angular.module("GeoMapAutocomplete",['YitAutocomplete','Geo','YitMap','AGM'])
    .run([function(){
        var element = angular.element('div[ng-app="AGM"]');
        if(angular.isDefined(element) && element) {
            angular.bootstrap(element[0],['AGM']);
        }
    }]);