<?php echo $this->Html->css('summernote.css');?>
<style type='text/css'>
	.header
	{
		display	: none;
	}
	
	.drag-over
	{
		opacity		: 0.5;
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
			parent.setURL(file_url, file_name);
		}
		
		$('.drop-container').on("dragenter", function(e){
			e.stopPropagation();
			e.preventDefault();
		});
		
		$('.drop-container').on("dragover", function(event)
		{
			event.stopPropagation();
			event.preventDefault();
			$('.drop-container').addClass('drag-over');
		});
		
		$('.drop-container').on("dragleave", function(event)
		{
			event.stopPropagation();
			event.preventDefault();
			$('.drop-container').removeClass('drag-over');
		});
		
		$('.drop-container').on("drop", function(event)
		{
			event.stopPropagation();
			event.preventDefault();
			$('.drop-container').removeClass('drag-over');
			var files = event.originalEvent.dataTransfer.files;
			$("#TaskFile")[0].files = files;
			
			if($("#TaskFile")[0].files.length==0)
			{
				alert('このブラウザはファイルのドロップをサポートしておりません。');
				return;
			}
			
			$('form').submit();
		});
	});
</script>
<div class="panel panel-default">
	<div class="panel-heading">
		<span data-localize="upload">ファイルのアップロード</span>
	</div>
	<div class="panel-body">
		<div class="form-group">
			<h4>アップロード可能なファイル形式</h4>
			<?php echo $upload_extensions;?>
		</div>

		<div class="form-group">
			<h4>アップロード可能なファイルサイズ</h4>
			最大 : <?php echo $this->Number->toReadableSize($upload_maxsize) ;?>バイト
		</div>

		<div class="form-group">
			<?php echo $this->Form->create('Task', ['type'=>'file', 'enctype' => 'multipart/form-data']); ?>
				<div class="drop-container alert alert-warning">
					<p>ここにファイルをドロップするか、ファイルを選択後、アップロードボタンをクリックしてください。</p>
					<p>ファイルが複数ある場合には、ZIP形式で圧縮してアップロードを行ってください。</p>
					<input type="file" name="data[Task][file]" multiple="multiple" id="TaskFile" class="form-control">
				</div>
				<button type="submit" id="btnUpload" class="btn btn-primary"><span data-localize="upload">アップロード</span></button>
				<button class="btn" onclick="parent.closeDialog();"><span data-localize="close"> 閉じる </span></button>
			<?php echo $this->Form->end(); ?>
		</div>
	</div>
</div>
