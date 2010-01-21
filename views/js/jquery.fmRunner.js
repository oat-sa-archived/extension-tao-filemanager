/**
 * JQuery Plugin Adapter for the FmRunner class
 * @author Bertrand Chevrier <bertrand.chevrier@tudor.lu>
 * @see fmRunner.js
 */

//include the fmRunner class if not done previously
try { FmRunner; } catch(e){
	$("script").each(function(){
		source = $(this).attr('src');
		if(/jquery\.fmRunner\.js$/.test(source)){
			document.write("<script type='text/javascript' src='"+source.replace('jquery.fmRunner.js', 'fmRunner.js')+"'></script>");
			return;
		}
	})
}

/**
 * JQuery plugin to bind the fmRunner with any node.
 * The runner is bound to the click event
 * @param {Object} options the list of options usually used with the window.open function (width,height, menubar, toolbar, etc.)
 * @example $("#myId").fmload() 
 * @example $("#myId").fmload({width: '1024px', height: '768px'});
 */
jQuery.fn.fmload = function (options) {
        return this.each(function () {
                jQuery(this).click(function(){
                        FmRunner.load(options);
                });
        });
};