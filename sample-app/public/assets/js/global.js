var logging = true;

function log(data){
	if(logging && window.console){
		console.log(data);
	}
}

var OBJECTS = {};

/**
 * Gitophp GENERIC UTILS
 */
OBJECTS.Utils = function(){

	var u = this;

	u.ajax = function(options,callback){
		options.success = function(response){
			// valid codes
			if(response.code >= 1 || response.code == undefined){
				if($.isFunction(callback)) callback(response) ;
				else log('Action success');
			// error code for demand
			}else if(response.code == 0){
				u.error('Action failed');
			// global error code
			}else if(response.code < 0){
				u.error('Important error');
			}
		};
		$.ajax(options);
	};

	u.error = function(error){
		log(error);
	};
};

OBJECTS.Site = function(){

	var site = this;

	site.init = function(){
		site.u = new OBJECTS.Utils();
	};

	site.init();
};

var Gitophp = new OBJECTS.Site();

hljs.initHighlightingOnLoad();

$(document).ready(function(){

});