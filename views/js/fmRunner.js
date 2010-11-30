/**
 * FmRunner Class 
 * Enable you to run the filemanager from an other extension
 * @example FmRunner.load({width: '1024', height: '768'})
 */
FmRunner = function() {
	
	//save the instance
	instance = this.constructor;
	
	//this part is loaded only the first call
	if(instance.single == undefined){
		instance.single = this;
	
		instance.element = null;
		instance.single.window = null; 
		instance.single.defaultOpt = {
			'width' 	: '800px',
			'height'	: '650px',
			'menubar'	: 'no',
			'resizable'	: 'yes',
			'status'	: 'no',
			'toolbar'	: 'no',
			'dependent' : 'yes'
		};
		
		instance.single.load = function(options, callback){
			if(options.elt){
				instance.element = options.elt;
			}
			if(instance.single.window != null){
				//close previous window
				 instance.single.window.close();
			}
			params = '';
			for (i in instance.single.defaultOpt){
				params += i + '=';
				(options[i]) ? params += options[i] :  params += instance.single.defaultOpt[i];
				params += ',';
			}
			for (i in options) {
				if(!instance.single.defaultOpt[i]){
					params += i + '=' + options[i] + ',';
				}
			}
			instance.single.window = window.open('/filemanager/Browser/index', 'filemanager', params);
			instance.single.window.focus();
			
			instance.single.window.onunload = function(){
				if(instance.single.window.urlData && callback != null && callback != undefined){
					callback(instance.element, instance.single.window.urlData);
				}
			};
			
			return instance.single.window;
		};
	}		
	else {
		//return singleton if already initialized
		return instance.single;
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
