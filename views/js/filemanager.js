/**
 * @author crp
 */
$(document).ready(function(){
	
	$('#file-container').fileTree({ 
			root: '/',
			script: "/filemanager/Browser/fileData",
			multiFolder: false,
			expandEasing: 'easeOutBounce', 
			collapseEasing: 'easeOutBounce'
		}, 
		function(file) {
	       
		   $("#file-url").html( urlData + file);
		   
		   $("#file-download").html("<a href=''>" + file +"</a>" );
		   $("#file-delete").html("<a href=''>" + file +"</a>" );
    	}, 
		function(dir) {
	       
		    $("#dir-uri").html(dir);
    	}
	);
	
});