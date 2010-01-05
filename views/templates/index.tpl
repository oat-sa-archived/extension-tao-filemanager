<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>File Manager</title>
	<link rel="shortcut icon" href="<?=BASE_WWW?>img/favicon.ico" type="image/x-icon" />
	
	<script type='text/javascript' src="<?=BASE_WWW?>js/jquery-1.3.2.min.js"></script>
	<script type='text/javascript' src="<?=BASE_WWW?>js/jquery-ui-1.7.2.custom.min.js"></script>
	<script type='text/javascript' src="<?=BASE_WWW?>js/jquery.easing.1.3.js"></script>
	<script type='text/javascript' src="<?=BASE_WWW?>js/jqueryFileTree/jqueryFileTree.js"></script>
	<script type='text/javascript' src="<?=BASE_WWW?>js/jquery.copy.js"></script>
	
	<link rel="stylesheet" type="text/css" href="<?=BASE_WWW?>css/custom-theme/jquery-ui-1.7.2.custom.css" />
	<link rel="stylesheet" type="text/css" href="<?=BASE_WWW?>js/jqueryFileTree/jqueryFileTree.css" />
	<link rel="stylesheet" type="text/css" href="<?=BASE_WWW?>css/style.css" />
	
	<script type='text/javascript'>
		var baseUrl 	= "<?=BASE_URL?>";
		var basePath 	= "<?=BASE_PATH?>";
		var baseData 	= "<?=BASE_DATA?>";
		var urlData 	= "<?=URL_DATA?>";
	</script>
	<script type='text/javascript' src="<?=BASE_WWW?>js/filemanager.js"></script>
</head>
<body>
	<div id="header" class="ui-widget-header ui-corner-all">File Manager</div>
	<div id="main-container">
		<div id="file-container-title" class="ui-state-default ui-corner-all" >File Browser</div>
		<div id="file-container"></div>
		
		<div id="file-data-container">
			<div class="ui-state-highlight ui-corner-all">
				<strong>Current directory</strong>:
				<span id="dir-uri" class="data-container">/</span>
			</div>
			<div class="ui-state-highlight ui-corner-all">
				<strong>URL</strong>:
				<span id="file-url" class="data-container" ></span>
				<span id="file-uri" style="display:none;"></span>
			</div>
			<div class="ui-state-highlight ui-corner-all" style="height:120px;">
				<strong>Preview</strong>
				<div id="file-preview"></div>
			</div>
			<div class="ui-state-highlight ui-corner-all">
				<strong>Actions</strong>
				<table id="actions">
					<tbody>
						<tr>
							<td><a class="root-link" href="#"><img src="<?=BASE_WWW?>img/root.png" /></a></td>
							<td><a class="new-dir-link" href="#"><img src="<?=BASE_WWW?>img/folder-new.png" /></a></td>
							<td><a class="copy-url-link" href="#"><img src="<?=BASE_WWW?>img/copy_disabled.png" /></a></td>
							<td><a class="download-link" href="#"><img src="<?=BASE_WWW?>img/download_disabled.png" /></a></td>
							<td><a class="delete-link" href="#"><img src="<?=BASE_WWW?>img/delete_disabled.png" /></a></td>
						</tr>
						<tr>
							<td><a class="root-link" href="#">Root</a></td>
							<td><a class="new-dir-link" href="#">New directory</a></td>
							<td><a class="copy-url-link" href="#">Copy url</a></td>
							<td><a class="download-link" href="#">Download</a></td>
							<td><a class="delete-link" href="#">Delete</a></td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="ui-widget-content ui-corner-all">
				<strong>File upload</strong>
				<form enctype='multipart/form-data' action="fileUpload">
					<input type="file" name="media_file" /><br />
					<input type="text" name="media_name" /><input type="submit" value="Upload" />
				</form>
			</div>
		</div>
	</div>
</body>
</html>