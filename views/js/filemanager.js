
/**
 * @param {FmRunner} runner instance
 */
function selectUrl(runner){
	runner.urlData = $("#file-url").text();
	runner.mediaData = $("#file-url").data('media');
	window.top.opener.$(window.top.opener.document).trigger('fmSelect');
	window.close();
}

function goToRoot(){
	window.location = root_url + "/filemanager/Browser/index";
}
function newFolder(){
	
	var parentDir = $("#dir-uri").text();
	var newDir = prompt("Enter the of the new directory inside " + $("#dir-uri").text());
	if(newDir){
		var openFolder = parentDir;
		$.ajax({
			url: root_url + "/filemanager/Browser/addFolder",
			type: "POST",
			data: {
				parent: parentDir,
				folder: newDir
			},
			dataType: 'json',
			success: function(response){
				if(response.added){
					initFileTree(parentDir.replace(/\/$/, ''));
				}
			}
		});
	}
}
function copyUrl(){
	$.copy( $("#file-url").text() );
}
function hasFlash(){
	if($.browser.msie){
		var hasFlash = false; 
		try {   
			var fo = new ActiveXObject('ShockwaveFlash.ShockwaveFlash');   
			if(fo) hasFlash = true; 
		}
		catch(e){   
			if(navigator.mimeTypes ["application/x-shockwave-flash"] != undefined) hasFlash = true; 
		} 
		return hasFlash;
	}
	else{
		if(navigator.plugins != null && navigator.plugins.length > 0){
			for(i in navigator.plugins){
				if(/(Shockwave|Flash)/i.test(navigator.plugins[i]['name'])){
					return true;
				}
			}
		}
	}
	return false;
}

function download(){
	window.location = root_url + '/filemanager/Browser/download?file='+encodeURIComponent($("#file-uri").text());
}
function removeFile(){
	if(confirm('Please confirm file deletion')){
		$.ajax({
			url: root_url + "/filemanager/Browser/delete",
			type: "POST",
			data: {
				file: $("#file-uri").text()
			},
			dataType: 'json',
			success: function(response){
				if(response.deleted){
					initFileTree();
				}
			}
		});
	}
}
function removeFolder(){
	if(confirm("Please confirm folder deletion.\nBe carefull, it will remove all the folder content!")){
		$.ajax({
			url: root_url + "/filemanager/Browser/delete",
			type: "POST",
			data: {
				folder: $("#dir-uri").text()
			},
			dataType: 'json',
			success: function(response){
				if(response.deleted){
					initFileTree();
				}
			}
		});
	}
}

function initFileTree(toOpen){
	if(!toOpen){
		toOpen = openFolder;
	}
	$('#file-container').fileTree({ 
			root: '/',
			open: toOpen, 
			script: root_url + "/filemanager/Browser/fileData",
			folderEvent: 'click',
			multiFolder: false,
			expandEasing: 'easeOutBounce'
		}, 
		
		/**
		 * by clikcing on a file in the file tree
		 * @param {String} file the file path
		 */
		function(file) {
			
		   $("#file-preview").html("<img src='"+baseUrl+"/views/img/throbber.gif'  alt='loading' />");
		   $.post(root_url + "/filemanager/Browser/getInfo", {file: file}, function(response){
			   if(response.type){
				   $("#file-url").data('media',{
					   type : response.type,
					   width: response.width || '',
					   height: response.height || ''
				   });
				   
				   //enable the selection only once the data are received
				   $("a.select-link").click(function(){
					   var runner = window.top.opener.fmRunner.single;
					   selectUrl(runner);				//runner is defined locally
				   });
				   //actions images
				   $("a.select-link img, a.copy-url-link img, a.download-link img, a.delete-link img").each(function(){
						if(/_disabled\.png$/.test(this.src)){
							this.src = this.src.replace('_disabled.png', '.png');
						}
					});
					
				    //actions links
					$("a.copy-url-link").bind('click', copyUrl);
					$("a.download-link").bind('click', download);
					$("a.delete-link").each(function(){
						$(this).unbind('click', removeFolder);
						$(this).bind('click', removeFile);
					});
				   
				   //url box
				   $("#file-url").html( urlData + file.replace(/^\//, ''));
				   $("#file-uri").html( file );
				  
				   $("#file-preview").load(root_url + "/filemanager/Browser/preview",{file: file});
			   }
		   }, "json");
		   
    	}, 
		
		/**
		 * by clikcing on a dir in the file tree
		 * @param {String} dir the directory path
		 */
		function(dir) {
	     
		  //actions images
		   $("a.select-link img, a.copy-url-link img , a.download-link img").each(function(){
				if(!/disabled\.png$/.test(this.src)){
					this.src = this.src.replace('.png', '_disabled.png');
				}
			});
			$("a.delete-link img").each(function(){
				if (/disabled\.png$/.test(this.src)) {
					this.src = this.src.replace('_disabled.png', '.png');
				}
			});
			
			//actions links
			$("a.select-link").each(function(){
				$(this).unbind('click', selectUrl);
			});
			$("a.copy-url-link").each(function(){
				$(this).unbind('click', copyUrl);
			});
			$("a.download-link").each(function(){
				$(this).unbind('click', download);
			});
			$("a.delete-link").each(function(){
				$(this).unbind('click', removeFile);
				$(this).bind('click', removeFolder);
			});
			
			//set current dir
		    $("#dir-uri").html(dir);
			$("#media_folder").val(dir);
			 $("#file-preview").html('');
    	}
	);
}

$(document).ready(function(){
	
	if(!hasFlash()){
		$("a.copy-url-link").hide();
	}
	
	initFileTree();
	$("a.root-link").click(goToRoot);
	$("a.new-dir-link").click(newFolder);
	
	$("#media_file").change(function(){
		$("#media_name").val(this.value.replace(/\\/g,'/').replace( /.*\//, ''));
	});
});