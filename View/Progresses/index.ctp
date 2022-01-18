<?php if(!$is_user) echo $this->element('admin_menu');?>
<?= $this->Html->css( 'select2.min.css');?>
<?= $this->Html->script( 'select2.min.js');?>
<?php $this->start('script-embedded'); ?>
<script>
	var URL_UPLOAD	= '<?= Router::url(['controller' => 'tasks', 'action' => 'upload', 'admin' => false])?>/file';
	var URL_NOTE	= '<?= Router::url(['controller' => 'notes', 'action' => 'page', 'admin' => false])?>/';
	var LS_KEY_EMAIL_USER = 'ic-email-user-<?= $task['Theme']['id']; ?>';
	
	/* 進捗一覧用 */
	$(function(){
		var html = marked.parse($('#content_body').val(),
		{
			breaks: true,
			sanitize: true
		});
		
		$("#content_body").before(html);
		
		$('.progress').each(function(index)
		{
			var html = marked.parse($(this).val(),
			{
				breaks: true,
				sanitize: true
			});
			
			$(this).before(html);
		});
		
		/* 進捗登録用 */
		
		// 添付ファイルアップロードボタンを追加
		$url = $('.form-control-upload');
		$url.after('<button id="btnUpload">Upload</button>');
		
		// アップロード画面の呼び出し
		$("#btnUpload").click(function(){
			//window.open(URL_UPLOAD, '_upload', 'width=650,height=500,resizable=no');
			//ファイルアップロードダイアログの iframe にURLを設定
			$("#uploadFrame").attr("src", URL_UPLOAD);
			//ファイルアップロードダイアログを表示
			$('#uploadDialog').modal('show');
			return false;
		});
		

		// メール通知する場合、メール通知先の入力をチェック
		$('#ProgressIndexForm').submit(function()
		{
			if($('input[name="is_mail"]:checked').length==0)
				return true;
			
			if($('#ProgressUser').val()==null)
			{
				alert('メール通知するユーザを選択してください\n次回以降は自動で選択されます');
				return false;
			}
			
			CommonUtil.setLocalStorage(LS_KEY_EMAIL_USER, $('#ProgressUser').val());
			return true;
		});

		// メール通知用
		$('#ProgressUser').select2({placeholder:   "メール通知するユーザを選択して下さい。(複数選択可／次回以降は自動で選択されます)", closeOnSelect: <?= (Configure::read('close_on_select') ? 'true' : 'false'); ?>,});
		$('#ProgressUser').val(CommonUtil.getLocalStorage(LS_KEY_EMAIL_USER)).change();
		
		// 進捗編集画面の再描画
		renderEditForm();
	});
	
	function smile(progress_id)
	{
		
		$.ajax({
			url: "<?= Router::url(['action' => 'smile']) ?>",
			type: "POST",
			data: {
				progress_id	: progress_id
			},
			dataType: "text",
			success : function(response){
				//通信成功時の処理
				var icon = $('.smile-icon-'+progress_id)[0];
				
				//alert(response);
				if(icon.src.indexOf('smile-on.png') > 0)
				{
					icon.src = icon.src.replace('on.png', 'off.png');
				}
				else
				{
					icon.src = icon.src.replace('off.png', 'on.png');
				}
			},
			error: function(){
				//通信失敗時の処理
				//alert('通信失敗');
			}
		});
		
		return;
	}
	
	/* 進捗編集用 */
	function setURL(url, file_name)
	{
		$('.form-control-upload').val(url);
		
		if(file_name)
			$('.form-control-filename').val(file_name);

		$('#uploadDialog').modal('hide');
	}
	
	function closeDialog(url, file_name)
	{
		$('#uploadDialog').modal('hide');
	}
	
	function setProgressType(element)
	{
		$('.row-progress-type input[type="radio"]').each(function(){
			$('.panel-body').removeClass('progress-type-' + $(this).val());
		});
		
		$('.panel-body').addClass('progress-type-' + $(element).val());
	}

	function setContentType(element)
	{
		$('.row-content-type input[type="radio"]').each(function(){
			$('.panel-body').removeClass('content-type-' + $(this).val());
		});
		
		$('.panel-body').addClass('content-type-' + $(element).val());
		
		if($(element).val()=='irohanote')
		{
			// ページ番号が設定されていない場合、ページ番号を生成
			var page_id = $('.row-page-id').val();
			
			if(!page_id)
			{
				page_id = Math.round(Math.random() * 1000000);
				$('.row-page-id').val(page_id);
			}
			
			document.getElementById("fraIrohaNote").src = URL_NOTE + page_id + '/edit';
			
			
			// 内容にダミーの文字列を設定
			if(!$('.row-body textarea').val())
				$('.row-body textarea').val('dummy');
		}
		else
		{
			// ダミーの文字列をクリア
			if($('.row-body textarea').val()=='dummy')
				$('.row-body textarea').val('');
		}
	}

	function renderEditForm()
	{
		var element = $($('.row-progress-type input[type="radio"]:checked'));
		setProgressType(element);
		
		element = $($('.row-content-type input[type="radio"]:checked'));
		setContentType(element);
	}
</script>
<?php $this->end(); ?>

<div class="progresses-index">
	<div class="ib-breadcrumb">
	<?php
		$controller = ($is_user) ? 'users_themes' : 'themes';
		
		$this->Html->addCrumb(__('学習テーマ一覧'), ['controller' => $controller, 'action' => 'index']);
		$this->Html->addCrumb($task['Theme']['title'], ['controller' => 'tasks', 'action' => 'index', $task['Theme']['id']]);
		$this->Html->addCrumb(h($task['Task']['title']));
		
		echo $this->Html->getCrumbs(' / ');
	?>
	</div>
	<div class="panel panel-info">
		<div class="panel-heading"><b><?= __('課題')?></b></div>
		<div class="panel-body">
			<big>
			<?= $this->Form->hidden('content_body', ['value' => $task['Task']['body']]);?>
			</big>
			<div>
				<?php 
				if(Configure::read('demo_mode'))
				{
					echo Utils::getDownloadLink('javascript:alert(\'デモモードの為、ダウンロードできません。\');', $task['Task']['file_name'], $this->Html);
				}
				else
				{
					echo Utils::getDownloadLink($task['Task']['file'], $task['Task']['file_name'], $this->Html);
				}
				
				echo Utils::getNoteLink($task['Task']['page_id'], $this->Html);
				?>
			</div>
			<div>
				<br>
				<button type="button" class="btn btn-primary btn-success" onclick="location.href='<?= Router::url(['controller' => 'tasks', 'action' => 'edit', $task['Theme']['id'], $task['Task']['id'], 'progresses']) ?>'"><?= __('編集')?></button>
			</div>
		</div>
	</div>

	<div class="ib-page-title"><?= __('進捗一覧')?></div>
	
	<div class="buttons_container">
		<?php if($is_add) {?>
		<button type="button" class="btn btn-primary btn-add" onclick="$('html, body').animate({scrollTop: ($(document).height()-1050)},800);">+ <?= __('追加')?></button>
		<?php } else {?>
		<button type="button" class="btn btn-primary btn-add" onclick="location.href='<?= Router::url(['action' => 'index', $task['Task']['id']]);?>#edit'">+ <?= __('追加')?></button>
		<?php }?>
	</div>
	
	<?php if(count($progresses) > 0) {?>
	<span onclick="$('html, body').animate({scrollTop: $(document).height()},800);">
		<a href="#">　▼ <?= __('ページの下へ')?></a>　
	</span>
	<?= __('並べ替え')?>：
	<span class="sort-item"><?= $this->Paginator->sort('created', __('作成日時'), ['direction' => 'desc']); ?></span>
	<?php }?>
	
	<?php foreach ($progresses as $progress): ?>
	<?php if($progress['Progress']['progress_type']=='progress') { ?>
	<div class="panel panel-success">
	<?php }else {?>
	<div class="panel panel-default">
	<?php }?>
		<div class="panel-heading">
			<div class="pull-left">
			[<?= h(Configure::read('progress_type.'.$progress['Progress']['progress_type'])); ?>] <?= h($progress['User']['name']); ?>
			</div>
			<div class="pull-right">
			<?= h(Utils::getYMDHN($progress['Progress']['created'])); ?>
			</div>
		</div>
		<div class="panel-body">
			<div class="text-left">
			<?php if($progress['Progress']['progress_type']=='progress') { ?>
				<?= __('進捗率')?> : <?= h($progress['Progress']['rate']); ?>% &nbsp;&nbsp;
			<?php }?>
			<?php if(Configure::read('use_emotion_icon')) { ?>
				感情 : <?= $this->Html->image($progress['Progress']['emotion_icon'].'.png', ['width' => 30]); ?>
			<?php }?>
			</div>
			<div class="progress-text bg-warning">
				<h4><?= h($progress['Progress']['title']); ?></h4>
				<?php 
				$content_type = $progress['Progress']['content_type'];
				
				switch($content_type)
				{
					case 'text':
						$body  = $progress['Progress']['body'];
						//$body  = $this->Text->autoLinkUrls($body, ['escape' => false, 'target' => '_blank']);
						$body  = $this->Text->autoLinkUrls($body, ['target' => '_blank']);
						$body  = nl2br($body);
						echo $body;
						break;
					case '':
					case 'markdown':
						echo $this->Form->hidden('progress_'.$progress['Progress']['id'], ['value' => $progress['Progress']['body'], 'class' => 'progress']);
						break;
					case 'irohanote':
						echo Utils::getNoteLink($progress['Progress']['page_id'], $this->Html);
						break;
				}
				?>
				<div>
					<?php
						if(Configure::read('demo_mode'))
						{
							echo Utils::getDownloadLink('javascript:alert(\'デモモードの為、ダウンロードできません。\');', $progress['Progress']['file_name'], $this->Html);
						}
						else
						{
							echo Utils::getDownloadLink($progress['Progress']['file'], $progress['Progress']['file_name'], $this->Html);
						}
					?>
				</div>
			</div>
			<div>
			
			<?php
			// 自分の進捗のみ編集、削除可能とする
			if($progress['User']['id']==$loginedUser['id'])
			{
				$sort_key	= ($this->request->params['named']) ? 'sort:'.$this->request->params['named']['sort'] : '';
				$direction	= ($this->request->params['named']) ? 'direction:'.$this->request->params['named']['direction'] : '';
				
				echo $this->Form->button(__('編集'), [
					'class'		=> 'btn btn-success btn-edit',
					'onclick'	=> "location.href='".Router::url(['action' => 'index', $progress['Task']['id'], $progress['Progress']['id'], $sort_key, $direction])."#edit'",
				]);
				
				echo $this->Form->postLink(__('削除'), 
					['action' => 'delete', $progress['Progress']['id']], 
					['class'=>'btn btn-danger btn-delete'], 
					__('[%s] を削除してもよろしいですか?', $progress['Progress']['title'])
				); 
				
				echo $this->Form->button(__('移動'), [
					'class'		=> 'btn btn-warning btn-move',
					'onclick'	=> "location.href='".Router::url(['action' => 'move', $progress['Progress']['id']])."'",
				]);
				
				echo $this->Form->hidden('id', ['id'=>'', 'class'=>'target_id', 'value'=>$progress['Progress']['id']]);
			}
			
			// スマイル機能
			if(Configure::read('use_smile'))
			{
				$image_file = ($progress['is_smiled']) ? 'smile-on.png' : 'smile-off.png';
				
				echo $this->Html->image($image_file, [
					'width'		=> 40, 
					'class'		=>'smile-icon smile-icon-'.$progress['Progress']['id'], 
					'onclick'	=>'smile('.$progress['Progress']['id'].');',
					'title'		=>'スマイルする', 
				]);
				echo '<div class="name_display">'.$progress['name_display'].'</div>';
			}
			?>
			</div>
		</div>
	</div>
	<?php endforeach; ?>
	<?= $this->element('paging');?>
	
	<!--進捗編集エリア-->
	<?php 
	?>
	<a name="edit"></a>
	<div class="panel <?= ($is_add) ? 'panel-default' :  'panel-danger'; ?>">
		<div class="panel-heading">
			<?= (!$is_add) ? __('編集') :  __('新規追加'); ?>
		</div>
		<div class="panel-body">
			<?php
				echo $this->Form->create('Progress', Configure::read('form_defaults'));
				echo $this->Form->input('id');
				echo $this->Form->input('title',	['label' => __('タイトル')]);

				echo $this->Form->inputRadio('progress_type',	['label' => __('種別'), 
					'options' => Configure::read('progress_type'), 'default' => 'progress', 'div' => 'form-group row-progress-type', 'onchange' => 'setProgressType(this)']);
				echo $this->Form->inputRadio('content_type',	['label' => __('進捗の入力形式'), 
					'options' => Configure::read('content_type'), 'default' => 'markdown', 'div' => 'form-group row-content-type','onchange' => 'setContentType(this)']);
				
				echo $this->Form->input('body', ['label' =>  __('内容'), 'div' => 'form-group row-body']);
				
				if(Configure::read('use_irohanote'))
				{
					echo $this->Form->block(__('内容'), '<iframe id="fraIrohaNote" width="100%" height="400"></iframe>', false, 'row-irohanote');
				}
				
				echo $this->Form->block('', '※ <a href="https://ja.wikipedia.org/wiki/Markdown" target="_blank">Markdown 形式</a> で記述可能です。', false, 'row-markdown');
				
				echo $this->Form->hidden('page_id', ['class' => 'form-group row-page-id']);
				
				echo $this->Form->input('file',		['label' => __('添付ファイル'), 'class' => 'form-control form-control-upload']);
				
				echo $this->Form->inputRadio('status',	['label' => __('課題のステータス'), 'options' => Configure::read('task_status'), 'value' => $task['Task']['status'],'default' => 1]);
				
				$rate_list = [
					'0'  => '0%',
					'10' => '10%',
					'20' => '20%',
					'30' => '30%',
					'40' => '40%',
					'50' => '50%',
					'60' => '60%',
					'70' => '70%',
					'80' => '80%',
					'90' => '90%',
					'100' => '100%',
				];
				
				echo $this->Form->input('rate', ['label' => __('進捗率'), 'options'=>$rate_list, 'default' => $task['Task']['rate'], 'div' => 'form-group row-progress']);
				
				Configure::read('emotion_icons');
				$emotion_icons = [];
				
				foreach(Configure::read('emotion_icons') as $key => $value)
				{
					$emotion_icons[$key] = $this->Html->image($value, ['width' => 40]);
				}
				
				if(Configure::read('use_emotion_icon'))
				{
					echo $this->Form->input('emotion_icon',	[
						'type' => 'radio',
						'before' => '<label class="col col-sm-3 control-label">感情</label><div>　※ 現在の感情を選択してください</div>',
						'after' => '',
						'separator'=>"　", 
						'legend' => false,
						'class' => false,
						'options' => $emotion_icons,
						]
					);
				}
				
				echo $this->Form->hidden('file_name', ['class' => 'form-control-filename']);
				echo $this->Form->input('User', ['label' => __('　'), 'size' => 20, 'multiple' => true, 
					'before' => '<div class="col col-sm-9 col-sm-offset-3"><input name="is_mail" type="checkbox">&nbsp;'.__('メール通知').'</div>']);
				
				echo '<input name="study_sec" type="hidden" value="0">';
				echo Configure::read('form_submit_before')
					.$this->Form->submit(($is_add) ? __('追加') :  __('保存'), Configure::read('form_submit_defaults'))
					.Configure::read('form_submit_after');
				echo $this->Form->end();
			?>
		</div>
	</div>
	
	
	
	<?php if(count($progresses) > 0) {?>
	<div onclick="$('html, body').animate({scrollTop: 0},800);">
	<a href="#">　▲ <?= __('ページのTOPへ')?></a>
	</div>
	<br>
	<?php }?>
</div>

<!--ファイルアップロードダイアログ-->
<div class="modal fade" id="uploadDialog" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-id='1'>
	<div class="modal-dialog">
		<div class="modal-content" style="width:660px;">
			<div class="modal-body" id='modal-body_1'>
				<iframe id="uploadFrame" width="100%" style="height: 440px;" scrolling="no" frameborder="no"></iframe>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
