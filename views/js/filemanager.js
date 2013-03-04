
/**
 * @param {FmRunner} runner instance
 */
function selectUrl(runner){
	runner.urlData = $("#file-url").text();
	runner.mediaData = $("#file-url").data('media');
	window.top.opener.$(window.top.opener.document).trigger('fmSelect');
	window.close();
}

function goToRoot(event){
	window.location = root_url + "filemanager/Browser/index";
	event.preventDefault();
}

function newFolder(event){
	var parentDir = $("#dir-uri").text();
	var newDir = prompt(__("Enter a name for the new directory."));
	if(newDir){
		var openFolder = parentDir;
		$.ajax({
			url: root_url + "filemanager/Browser/addFolder",
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
	event.preventDefault();
}

function download(event){
	window.location = root_url + 'filemanager/Browser/download?file='+encodeURIComponent($("#file-uri").text());
	event.preventDefault();
}

function removeFile(event){
	if(confirm(__('Please confirm file deletion.'))){
		$.ajax({
			url: root_url + "filemanager/Browser/delete",
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
	event.preventDefault();
}
function removeFolder(event){
	if(confirm(__("Please confirm directory deletion.\nBe careful, it will remove the entire content of the directory!"))){
		$.ajax({
			url: root_url + "filemanager/Browser/delete",
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
	event.preventDefault();
}

/**
 * Highlight a given file/directory in the tree.
 * If no file parameter is given, this function will
 * only unhighlight the currently highlighted item.
 * 
 * @param {String} The file/directory to be hilighted.
 */
function highlight(file){
	//remove present hilights.
	$(".jqueryFileTree a").removeClass('active');
	
	if (typeof(file) != 'undefined'){
		$(".jqueryFileTree").find('a[rel="' + file + '"]')
							.addClass('active');	
	}
}

function initFileTree(toOpen){
	//debugger;
	if(!toOpen) toOpen = openFolder;

	$('#file-container').fileTree({
			root: '/',
			open: toOpen,
			script: root_url + "filemanager/Browser/fileData",
			folderEvent: 'click',
			multiFolder: false,
			expandEasing: 'easeOutBounce'
		},

		/**
		 * by clikcing on a file in the file tree
		 * @param {String} file the file path
		 */
		function(file) {
			highlight(file);
			
			$("#file-preview").html("<img src='"+baseUrl+"views/img/throbber.gif'  alt='loading' />");
			$.post(root_url + "filemanager/Browser/getInfo", {file: file}, function(response){
				if(response.type){
					$("#file-url").data('media',{
						type : response.type,
						width: response.width || '',
						height: response.height || ''
					});

					//enable the selection only once the data are received
					$("a.link.select").click(function(event){
						var runner = window.top.opener.fmRunner.single;
						selectUrl(runner);
						//runner is defined locally
						event.preventDefault();
					});
					
					//actions' images
					if ($("a.link.select, a.link.download, a.link.delete").hasClass('disabled')){
						$("a.link.select, a.link.download, a.link.delete").removeClass('disabled');
					} 

					//actions' links
					$("a.link.download").bind('click', download);
					$("a.link.delete").unbind('click', removeFolder)
									  .unbind('click', removeFile)
									  .bind('click', removeFile);

					//url box
					$("#file-url").html( urlData + file.replace(/^\//, ''));
					$("#file-uri").html( file );
					
					if (typeof(response.dir) != 'undefined'){
						$("#dir-uri").html(response.dir);
					}
					

					$("#file-preview").load(root_url + "filemanager/Browser/preview",{file: file});
				}
			}, "json");
		},

		/**
		 * by clikcing on a dir in the file tree
		 * @param {String} dir the directory path
		 */
		function(dir) {
			highlight();
			$('#file-url, #file-uri').empty();
			
			//disable buttons that have no effects on a directory.
			$("a.link.select, a.link.download").toggleClass("disabled", true);
			//enable buttons that have effects on a directory.
			$("a.link.new-dir, a.link.root, a.link.delete").toggleClass("disabled", false);

			//events.
			$("a.link.select, a.link.download, a.link.delete, a.link.root").off('click')
															  .on('click', function(e){ e.preventDefault(); });
			$("a.link.delete").bind('click', removeFolder);
			$("a.link.root").bind('click', goToRoot);

			//set current dir
			$("#dir-uri").html(dir);
			$("#media_folder").val(dir);
			$("#file-preview").html('');
		}
	);
}

$(document).ready(function(){

	initFileTree();
	$("a.link.disabled").live('click', function(event){ event.preventDefault(); });
	$("a.link.new-dir").click(newFolder);

	$("#media_file").change(function(){
		$("#media_name").val(this.value.replace(/\\/g,'/').replace( /.*\//, ''));
	});
});