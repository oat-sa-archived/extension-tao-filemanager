<?if(get_data('source')):?>

	<?if(get_data('isImage')):?>
		<img src="<?=get_data('source')?>" width="<?=get_data('width')?>" height="<?=get_data('height')?>" alt='preview' style='border:solid 1px #666666;' />
	<?else:?>
	    <object data="<?=get_data('source')?>" type="<?=get_data('mimeType')?>" width="<?=get_data('width')?>" height="<?=get_data('height')?>" /> 
	<?endif?>

<?else:?>
	No preview available
<?endif?>
