<?php echo $this->Html->css('summernote.css');?>
<style type='text/css'>
	.header
	{
		display	: none;
	}
</style>
<?php $this->end(); ?>

<script>
	$(document).ready(function()
	{
		var mode = '<?php echo $mode?>';
		var file_url = '<?php echo $file_url?>';
		var file_name = '<?php echo $file_name?>';

		if(mode=='complete')
		{
			$('#btnUpload').hide();
			opener.setURL(file_url, file_name);
			window.close();
		}
	});
</script>
<div class="panel panel-default">
	<div class="panel-heading">
		<span data-localize="upload">ファイルのアップロード</span>
	</div>
	<div class="panel-body">
		<div class="alert alert-warning">アップロードするファイルを指定して、アップロードボタンをクリックしてください。<br>ファイルが複数ある場合には、ZIP形式で圧縮してアップロードを行ってください。</div>
		<div class="form-group">
			<h4>アップロード可能拡張子</h4>
			<?php echo $upload_extensions;?>
		</div>

		<div class="form-group">
			<h4>アップロード可能ファイルサイズ</h4>
			最大 : <?php echo $this->Number->toReadableSize($upload_maxsize) ;?>
		</div>

		<div class="form-group">
			<?php echo $this->Form->create('Task', array('type'=>'file', 'enctype' => 'multipart/form-data')); ?>
				<input type="file" name="data[Task][file]" multiple="multiple" id="TaskFile" class="form-control">
				<br>
				<button type="submit" id="btnUpload" class="btn btn-primary"><span data-localize="upload">アップロード</span></button>
				<button class="btn" onclick="window.close();"><span data-localize="close"> 閉じる </span></button>
			<?php echo $this->Form->end(); ?>
		</div>
	</div>
</div>
