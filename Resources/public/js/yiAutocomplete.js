'use strict'

angular.module("YitAutocomplete",['angucomplete']).directive("yitAutocomplete",["$http",function($http){
            return {
                restrict: "E",
                scope: {
                  select: "=yiSelectedObject"
                },
                compile: function compileFn(){
                    return function linkFn(scope,el,attr){
                        el.hide();
                        el.click(function(e){
                            var index = YitGeoValue.ngModel.length;
                            var address = YitGeoValue.ngModel[index-1];
                            $http.put("/geo/putAddress/"+address).success(function(d){
                                scope.select = d;
                            })
                        });
                        scope.value = YitGeoValue;
                        scope.$watch('value',function(d){
                            if(angular.isDefined(d) && d.showButton === true){
                                el.show();
                            }
                            else {
                                el.hide();
                            }
                        },true);
                    }
                }
            }
        }]);