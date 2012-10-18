/**
 * FmRunner Class 
 * Enable you to run the filemanager from an other extension
 * @example FmRunner.load({width: '1024', height: '768'})
 */
FmRunner = function() {
	
	//save the instance
	window.fmRunner = this.constructor;
	
	//this part is loaded only the first call
	if(window.fmRunner.single == undefined){
		window.fmRunner.single = this;
	
		window.fmRunner.single.element = null;
		window.fmRunner.single.window = null; 
		window.fmRunner.single.defaultOpt = {
			'width' 	: '800px',
			'height'	: '650px',
			'menubar'	: 'no',
			'resizable'	: 'yes',
			'status'	: 'no',
			'toolbar'	: 'no',
			'dependent' : 'yes'
		};
		
		window.fmRunner.single.load = function(options, callback){
			if(options.elt){
				window.fmRunner.single.element = options.elt;
			}
			if(window.fmRunner.single.window != null){
				//close previous window
				window.fmRunner.single.window.close();
			}
			params = '';
			for (i in window.fmRunner.single.defaultOpt){
				params += i + '=';
				(options[i]) ? params += options[i] :  params += window.fmRunner.single.defaultOpt[i];
				params += ',';
			}
			for (i in options) {
				if(!window.fmRunner.single.defaultOpt[i]){
					params += i + '=' + options[i] + ',';
				}
			}

			window.fmRunner.single.window = window.open(root_url + 'filemanager/Browser/index', 'filemanager', params);
			window.fmRunner.single.window.focus();
			$(document).bind('fmSelect', function(e){
				e.preventDefault();
				if(window.fmRunner.single.urlData && callback != null && callback != undefined){
					if(window.fmRunner.single.mediaData){
						callback(window.fmRunner.single.element, window.fmRunner.single.urlData, window.fmRunner.single.mediaData);
					}
					else{
						callback(window.fmRunner.single.element, window.fmRunner.single.urlData);
					}
				}
			});
			
			return window.fmRunner.single.window;
		};
	}		
	else {
		//return singleton if already initialized
		return window.fmRunner.single;
	}
};

/**
 * Use this method instead of constructor to use the shared instance (singleton)
 * @param {Object} options the popup options
 * @return {Object} the created window ref
 */
FmRunner.load = function(options, callback){
	if(options == undefined){
		options = {};
	}
	return new FmRunner().load(options, callback); 	//instanciate and load it
};
