<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?=__('Media Manager')?></title>
	<link rel="shortcut icon" href="<?=BASE_WWW?>img/favicon.ico" type="image/x-icon" />
	
	<?=tao_helpers_Scriptloader::render()?>
	
	<script type='text/javascript'>

		var root_url	= "<?=ROOT_URL?>";
		var baseUrl 	= "<?=BASE_URL?>";
		var basePath 	= "<?=BASE_PATH?>";
		var baseData 	= "<?=BASE_DATA?>";
		var urlData 	= "<?=URL_DATA?>";

		var openFolder	= '/';
		
		<?if(get_data("openFolder")):?>
			openFolder	= "<?=get_data('openFolder')?>";
		<?endif?>

		var runner = window.top.opener.fmRunner.single;
		runner.urlData = urlData;
		runner.mediaData = {};
		
	</script>
</head>
<body>
	<div id="header" class="ui-widget-header ui-corner-all"><?=__('Media Manager')?></div>
	<div id="main-container">
	    <div id="file-browser">
		  <div id="file-container-title" class="ui-state-default ui-corner-top" ><?=__('File Browser')?></div>
		  <div id="file-container"></div>
		</div>
		
		<div id="file-data-container">
			<?if(get_data('error')):?>
				<div class="ui-widget ui-corner-all ui-state-error error-message">
						<?=urldecode(get_data('error'))?>
				</div>
			<?endif?>
			<div class="ui-state-highlight ui-corner-all">
				<strong><?=__('Current directory')?></strong>:
				<span id="dir-uri" class="data-container">/</span>
			</div>
			<div class="ui-state-highlight ui-corner-all">
				<strong><?=__('URL')?></strong>:
				<span id="file-url" class="data-container" ></span>
				<span id="file-uri" style="display:none;"></span>
			</div>
			<div class="ui-state-highlight ui-corner-all" style="min-height:150px;">
				<strong><?=__('Preview')?></strong>
				<div id="file-preview" style="text-align:center;"></div>
			</div>
			<div class="ui-state-highlight ui-corner-all">
				<strong><?=__('Actions')?></strong>
				<table id="actions">
					<tbody>
						<tr>
							<td><a class="select-link" 		href="#"><img src="<?=BASE_WWW?>img/select_disabled.png" /></a></td>
							<td><a class="root-link" 		href="#"><img src="<?=BASE_WWW?>img/root.png" /></a></td>
							<td><a class="new-dir-link" 	href="#"><img src="<?=BASE_WWW?>img/folder-new.png" /></a></td>
							<td><div style="position: relative"><a href="#" class="copy-url-link" style="display: inline-block"><img src="<?=BASE_WWW?>img/copy_disabled.png"/></a></div></td>
							<td><a class="download-link" 	href="#"><img src="<?=BASE_WWW?>img/download_disabled.png" /></a></td>
							<td><a class="delete-link" 		href="#"><img src="<?=BASE_WWW?>img/delete_disabled.png" /></a></td>
						</tr>
						<tr>
							<td><a class="select-link" 		href="#"><?=__('Select')?></a></td>
							<td><a class="root-link" 		href="#"><?=__('Root')?></a></td>
							<td><a class="new-dir-link" 	href="#"><?=__('New directory')?></a></td>
							<td><div style="position: relative"><a class="copy-url-link" href="#"><?=__('Copy url')?></a></div></td>
							<td><a class="download-link" 	href="#"><?=__('Download')?></a></td>
							<td><a class="delete-link" 		href="#"><?=__('Delete')?></a></td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="ui-widget-content ui-corner-all">
				<strong><?=__('File upload')?></strong><br /><br />
				<form enctype='multipart/form-data' action="<?=ROOT_URL?>/filemanager/Browser/fileUpload" method="post">
					<input id="media_folder" type="hidden" name="media_folder" value="/" />
					<input type="hidden" name="MAX_FILE_SIZE" value="<?=get_data('upload_limit')?>" />
					<span><?=__('Max filesize')?> <?=round(get_data('upload_limit')/1048576, 1)?><?=__(' MB')?></span><br /><br />
					<span class="form-label"><?=__('File')?></span><input id="media_file" type="file" name="media_file" /><br />
					<span class="form-label"><?=__('Name')?></span><input id="media_name" type="text" name="media_name" /><br />
					<input type="submit" value="<?=__('Upload')?>" />
				</form>
			</div>
		</div>
	</div>
</body>
</html>