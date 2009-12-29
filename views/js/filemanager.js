/**
 * @author crp
 */
$(document).ready(function(){
	
	$('#file-container').fileTree({ 
			root: baseData, 
			script: "/filemanager/Browser/fileData"
		}, 
		function(file) {
	       
		   $("#file-url").html(file.replace(basePath,baseUrl) );
		   $("#file-download").html("<a href=''>" + file.replace(baseData,'') +"</a>" );
		   $("#file-delete").html("<a href=''>" + file.replace(baseData,'') +"</a>" );
    });
	
});