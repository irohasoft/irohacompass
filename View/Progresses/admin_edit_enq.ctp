<?php echo $this->element('admin_menu');?>
<div class="progresses form">
<?php echo $this->Html->css('summernote.css');?>
<?php $this->start('css-embedded'); ?>
<style type='text/css'>
	#ProgressOptionList
	{
	    width: 200px;
	}
	
	#ProgressOptionList option
	{
		border-top:    2px double #ccc;
		border-right:  2px double #aaa;
		border-bottom: 2px double #aaa;
		border-left:   2px double #ccc;
		/*
		background-color: #fff;
		font-family: Verdana, Geneva, sans-serif;
		*/
		color: #444455;
		width: 160px;
		margin:6px;
		padding: 5px;
	}
	
	input[name="data[Progress][image]"]
	{
		display:inline-block;
		width:85%;
		margin-right:10px;
	}
</style>
<?php $this->end(); ?>
<?php $this->start('script-embedded'); ?>
<?php echo $this->Html->script('summernote.min.js');?>
<?php echo $this->Html->script('lang/summernote-ja-JP.js');?>
<script>
	$(document).ready(function()
	{
		init();
	});

	function add_option()
	{
		txt	= document.all("option");
		opt	= document.all("data[Progress][option_list]").options;
		
		if(txt.value=="")
		{
			alert("選択肢を入力してください");
			return false;
		}
		
		if(txt.value.length > 50)
		{
			alert("選択肢は50文字以内で入力してください");
			return false;
		}
		
		if(opt.length==10)
		{
			alert("選択肢の数が最大値を超えています");
			return false;
		}
		
		opt[opt.length] = new Option( txt.value, txt.value )
		txt.value = "";
		update_options();
		//update_correct();

		return false;
	}

	function del_option()
	{
		var opt = document.all("data[Progress][option_list]").options;
		
		if( opt.selectedIndex > -1 )
		{
			opt[opt.selectedIndex] = null;
			update_options();
			//update_correct();
		}
	}

	function update_options()
	{
		var opt = document.all("data[Progress][option_list]").options;
		var txt = document.all("ProgressOptions");
		
		txt.value = "";
		
		for(var i=0; i<opt.length; i++)
		{
			if(txt.value=="")
			{
				txt.value = opt[i].value;
			}
			else
			{
				txt.value += "|" + opt[i].value;
			}
		}
		
	}

	function init()
	{
		$url = $('input[name="data[Progress][image]"]');
		
		$url.after('<input id="btnUpload" type="button" value="アップロード">');
		$("#btnUpload").click(function(){
			window.open('<?php echo Router::url(array('controller' => 'tasks', 'action' => 'upload'))?>/image', '_upload', 'width=650,height=500,resizable=no');
			return false;
		});
		
		// リッチテキストエディタを起動
		CommonUtil.setRichTextEditor('#ProgressBody', <?php echo (Configure::read('use_upload_image') ? 'true' : 'false')?>, '<?php echo $this->webroot ?>');
		CommonUtil.setRichTextEditor('#ProgressExplain', <?php echo (Configure::read('use_upload_image') ? 'true' : 'false')?>, '<?php echo $this->webroot ?>');
		
		if($("#ProgressOptions").val()=="")
			return;
		
		var options = $("#ProgressOptions").val().split('|');
		
		for(var i=0; i<options.length; i++)
		{
			var isSelected = ($('#ProgressCorrect').val()==(i+1));
			
			$option = $('<option>')
				.val(options[i])
				.text(options[i])
				.prop('selected', isSelected);
			
			$("#ProgressOptionList").append($option);
		}
		
		render();
		update_options();
	}

	function setURL(url)
	{
		$('input[name="data[Progress][image]"]').val(url);
	}
	
	function render()
	{
		if($('#ProgressQuestionTypeSingle:checked').val())
		{
			$('#ProgressOptions').val('');
			$('.row-options').show();
		}
		else
		{
			$('#ProgressOptions').val('none');
			$('.row-options').hide();
		}
	}
</script>
<?php $this->end(); ?>
	<div class="ib-breadcrumb">
	<?php 
		$this->Html->addCrumb('アンケート一覧',  array('controller' => 'tasks', 'action' => 'index_enq'));
		$this->Html->addCrumb($content['Task']['title'], array('controller' => 'progresses', 'action' => 'index_enq', $content['Task']['id']));
		
		echo $this->Html->getCrumbs(' / ');
	?>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading">
			<?php echo ($this->action == 'admin_edit') ? __('編集') :  __('新規質問'); ?>
		</div>
		<div class="panel-body">
			<?php echo $this->Form->create('Progress', Configure::read('form_defaults')); ?>
			<?php
				echo $this->Form->input('id');
				echo $this->Form->input('title',	array('label' => __('タイトル')));
				echo $this->Form->input('body',		array('label' => __('質問文')));
				echo $this->Form->input('progress_type', array(
					'type' => 'radio',
					'before' => '<label class="col col-md-3 col-sm-4 control-label">回答形式</label>',
					'separator'=>"　", 
					'legend' => false,
					'class' => false,
					'options' => Configure::read('progress_type_enq'),
					'default' => 'single',
					'onchange' => 'render()'
					)
				);

			?>
			<div class="form-group row-options required">
				<label for="ProgressOptions" class="col col-md-3 col-sm-4 control-label">選択肢</label>
				<div class="col col-md-9 col-sm-8 required">
				「＋」で選択肢の追加、「−」で選択された選択肢を削除します。（※最大10まで）<br>
				<input type="text" size="20" name="option" style="width:200px;display:inline-block;">
				<button class="btn" onclick="add_option();return false;">＋</button>
				<button class="btn" onclick="del_option();return false;">−</button><br>
			<?php
				echo $this->Form->input('option_list',	array('label' => __('選択肢／正解'), 
					'type' => 'select',
					'label' => false,
					'size' => 5,
				));
				echo $this->Form->hidden('options',	array('label' => __('選択肢')));
			?>
				</div>
			</div>
			<?php
				echo $this->Form->input('comment',	array('label' => __('備考')));
			?>
			<div class="form-group">
				<div class="col col-md-9 col-md-offset-3">
					<?php echo $this->Form->submit('保存', Configure::read('form_submit_defaults')); ?>
				</div>
			</div>
		</div>
	</div>
</div>