//load script dynamic
/*
	jsfile.js  (module, angular)
	====
	module.controller('NewCtrl', ['$scope', function($scope){
		//your code...
	}]
	
	
	usage:  angular.loadScript(jsfile, moduleName)
	====
	angular.loadScript('jsfile.js').then(function(){
		//completed
		
	})
*/

(function(angular, moduleApp){
	if(!angular) return;
	if(!moduleApp) moduleApp='app';
	angular.loadScript=function(src, moduleName){
		if(src){
			if(!moduleName) moduleName=moduleApp;
			var err;
			try{
				var inj=angular.injector(['ng']);
				if(angular.isArray(src)){
					var $q=inj.get('$q');
					var qs=[];
					angular.forEach(src, function(v){
						if(v && angular.isString(v)){
							qs.push(angular.loadScript(v,moduleName)); 
						}
					})
					if(qs.length) return $q.all(qs);
					return false;
				}
				
				var app=angular.module(moduleName);
				if(! angular._dyncache){
					angular._dyncache=inj.get('$cacheFactory')('dynamic-scripts');
				}
				
				if(angular._dyncache.get(src)) {
					return true;
				}
				
				if(inj.has('$http')){
					var $http=inj.get('$http');
					return $http.get(src).success(function(s){
						try{
							angular._dyncache.put(src, s);
							var fn=new Function('module', 'angular', s);
							fn(app, angular);
						}catch(err){
							console.error('dynamic script parser error : ' + src);
						}
					}).error(function(){
						console.error('dynamic script load error : ' + src);
					}).then(function(){return src});
				}
			}catch(err){
				console.error('you can not load dynamic script:', src, '\nbecause module "' + moduleName +'" is not found');
			}
		}
		return false;
	}
})(angular);

