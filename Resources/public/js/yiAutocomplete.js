'use strict'

angular.module("YitAutocomplete",['angucomplete']).constant("YitGeoValue",{showButton: false,ngModel: ''})
        .config(["$httpProvider","YitGeoValue",function($httpProvider,YitGeoValue){
            $httpProvider.interceptors.push(function(){
                return {
                    response: function(config){
                        if((!angular.isDefined(config.data.data) && config.data.status === 204) || config.data === "null"){
                            YitGeoValue.showButton = true;
                        }
                        else {
                            YitGeoValue.showButton = false;
                        }
                        return config;
                    },
                    request: function(config){
                        if(config.url.indexOf(".html") === -1){
                            config.url = "/app_dev.php"+config.url;
                        }
                        var model = config.url;
                        YitGeoValue.ngModel = model.split("/");
                        return config;
                    }
                }
            })
        }]).directive("yiAutocomplete",["$http","YitGeoValue",function($http,YitGeoValue){
            return {
                restrict: "A",
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