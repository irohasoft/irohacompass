<?php if(!$is_user) echo $this->element('admin_menu');?>
<?php $this->start('script-embedded'); ?>
<script>
	var URL_UPLOAD	= '<?php echo Router::url(array('controller' => 'tasks', 'action' => 'upload', 'admin' => false))?>/file';
	var URL_NOTE	= '<?php echo Router::url(array('controller' => 'notes', 'action' => 'page', 'admin' => false))?>/';
	
	/* 進捗一覧用 */
	$(function(){
		var html = marked($('#content_body').val(),
		{
			breaks: true,
			sanitize: true
		});
		
		$("#content_body").before(html);
		
		$('.progress').each(function(index)
		{
			var html = marked($(this).val(),
			{
				breaks: true,
				sanitize: true
			});
			
			$(this).before(html);
		});
		
		/* 進捗登録用 */
		
		// 添付ファイルアップロードボタンを追加
		$url = $('.form-control-upload');
		$url.after('<button id="btnUpload"><span data-localize="upload">Upload</span></button>');
		
		// アップロード画面の呼び出し
		$("#btnUpload").click(function(){
			window.open(URL_UPLOAD, '_upload', 'width=650,height=500,resizable=no');
			return false;
		});
		
		// 進捗編集画面の再描画
		renderEditForm();
	});
	
	function smile(progress_id)
	{
		
		$.ajax({
			url: "<?php echo Router::url(array('action' => 'smile')) ?>",
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
		
		$this->Html->addCrumb('学習テーマ一覧', array('controller' => $controller, 'action' => 'index'));
		$this->Html->addCrumb($content['Theme']['title'], array('controller' => 'tasks', 'action' => 'index', $content['Theme']['id']));
		$this->Html->addCrumb(h($content['Task']['title']));
		
		echo $this->Html->getCrumbs(' / ');
	?>
	</div>
	<div class="panel panel-info">
		<div class="panel-heading"><b><span data-localize="task">課題</span></b></div>
		<div class="panel-body">
			<big>
			<?php echo $this->Form->hidden('content_body', array('value' => $content['Task']['body']));?>
			</big>
			<div>
				<?php 
				if(Configure::read('demo_mode'))
				{
					echo Utils::getDownloadLink('javascript:alert(\'デモモードの為、ダウンロードできません。\');', $content['Task']['file_name'], $this->Html);
				}
				else
				{
					echo Utils::getDownloadLink($content['Task']['file'], $content['Task']['file_name'], $this->Html);
				}
				
				echo Utils::getNoteLink($content['Task']['page_id'], $this->Html);
				?>
			</div>
			<div>
				<br>
				<button type="button" class="btn btn-primary btn-success" onclick="location.href='<?php echo Router::url(array('controller' => 'tasks', 'action' => 'edit', $content['Theme']['id'], $content['Task']['id'])) ?>'"><span data-localize="edit">編集</span></button>
			</div>
		</div>
	</div>

	<div class="ib-page-title"><span data-localize="progress_list">進捗一覧</span></div>
	
	<div class="buttons_container">
		<?php if($is_add) {?>
		<button type="button" class="btn btn-primary btn-add" onclick="$('html, body').animate({scrollTop: ($(document).height()-1050)},800);"><span data-localize="add">+ 追加</span></button>
		<?php } else {?>
		<button type="button" class="btn btn-primary btn-add" onclick="location.href='<?php echo Router::url(array('action' => 'index', $content['Task']['id']));?>#edit'"><span data-localize="add">+ 追加</span></button>
		<?php }?>
	</div>
	
	<?php if(count($progresses) > 0) {?>
	<span onclick="$('html, body').animate({scrollTop: $(document).height()},800);">
		<a href="#">　▼ ページの下へ</a>　
	</span>
	並べ替え：
	<span class="sort-item"><?php echo $this->Paginator->sort('created', '作成日時', array('direction' => 'desc')); ?></span>
	<?php }?>
	
	<?php foreach ($progresses as $progress): ?>
	<?php if($progress['Progress']['progress_type']=='progress') { ?>
	<div class="panel panel-success">
	<?php }else {?>
	<div class="panel panel-default">
	<?php }?>
		<div class="panel-heading">
			<div class="pull-left">
			[<?php echo h(Configure::read('progress_type.'.$progress['Progress']['progress_type'])); ?>] <?php echo h($progress['User']['name']); ?>
			</div>
			<div class="pull-right">
			<?php echo h(Utils::getYMDHN($progress['Progress']['created'])); ?>
			</div>
		</div>
		<div class="panel-body">
			<div class="text-left">
			<?php if($progress['Progress']['progress_type']=='progress') { ?>
				進捗率 : <?php echo h($progress['Progress']['rate']); ?>% &nbsp;&nbsp;
			<?php }?>
			<?php if(Configure::read('use_emotion_icon')) { ?>
				感情 : <?php echo $this->Html->image($progress['Progress']['emotion_icon'].'.png', array('width' => 30)); ?>
			<?php }?>
			</div>
			<div class="progress-text bg-warning">
				<h4><?php echo h($progress['Progress']['title']); ?></h4>
				<?php 
				$content_type = $progress['Progress']['content_type'];
				
				switch($content_type)
				{
					case 'text':
						echo h($progress['Progress']['body']);
						break;
					case '':
					case 'markdown':
						echo $this->Form->hidden('progress_'.$progress['Progress']['id'], array('value' => $progress['Progress']['body'], 'class' => 'progress'));
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
				
				echo $this->Form->button('<span data-localize="edit">編集</span>', array(
					'class'		=> 'btn btn-success btn-edit',
					'onclick'	=> "location.href='".Router::url(array('action' => 'index', $progress['Task']['id'], $progress['Progress']['id'], $sort_key, $direction))."#edit'",
				));
				
				echo $this->Form->postLink(__('削除'), 
						array('action' => 'delete', $progress['Progress']['id']), 
						array('class'=>'btn btn-danger','data-localize' => 'delete'), 
						__('[%s] を削除してもよろしいですか?', $progress['Progress']['title'])
				); 
				echo $this->Form->hidden('id', array('id'=>'', 'class'=>'target_id', 'value'=>$progress['Progress']['id']));
			}
			
			// スマイル機能
			if(Configure::read('use_smile'))
			{
				$image_file = ($progress['is_smiled']) ? 'smile-on.png' : 'smile-off.png';
				
				echo $this->Html->image($image_file, array(
					'width'		=> 40, 
					'class'		=>'smile-icon smile-icon-'.$progress['Progress']['id'], 
					'onclick'	=>'smile('.$progress['Progress']['id'].');',
					'title'		=>'スマイルする', 
				));
				echo '<div class="name_display">'.$progress['name_display'].'</div>';
			}
			?>
			</div>
		</div>
	</div>
	<?php endforeach; ?>
	
	<!--進捗編集エリア-->
	<?php 
	?>
	<a name="edit"></a>
	<div class="panel <?php echo ($is_add) ? 'panel-default' :  'panel-danger'; ?>">
		<div class="panel-heading">
			<?php echo (!$is_add) ? '<span data-localize="edit">編集</span>' :  '<span data-localize="add">新規追加</span>'; ?>
		</div>
		<div class="panel-body">
			<?php echo $this->Form->create('Progress', Configure::read('form_defaults')); ?>
			<?php
				echo $this->Form->input('id');
				echo $this->Form->input('title',	array('label' => "<span data-localize='title'>タイトル</span>"));
				echo $this->Form->input('progress_type',	array(
					'type' => 'radio',
					'before' => '<label class="col col-sm-3 control-label"><span data-localize="kind">種別</span></label>',
					'separator'=>"　", 
					'legend' => false,
					'class' => false,
					'options' => Configure::read('progress_type'),
					'default' => 'progress',
					'div' => 'form-group row-progress-type',
					'onchange' => 'setProgressType(this)',
					)
				);
				echo $this->Form->input('content_type',	array(
					'type' => 'radio',
					'before' => '<label class="col col-sm-3 control-label"><span data-localize="content_type">進捗の入力形式</span></label>',
					'separator'=>"　", 
					'legend' => false,
					'class' => false,
					'options' => Configure::read('content_type'),
					'default' => 'markdown',
					'div' => 'form-group row-content-type',
					'onchange' => 'setContentType(this)',
					)
				);
				echo $this->Form->input('body',		array(
					'label' =>  '<span data-localize="content">内容</span>',
					'div' => 'form-group row-body',
					)
				);
				
				
				if(Configure::read('use_irohanote'))
				{
					Utils::writeFormGroup('<span data-localize="content">内容</span>', 
						'<iframe id="fraIrohaNote" width="100%" height="400"></iframe>',
						false, 'row-irohanote'
					);
				}
				
				Utils::writeFormGroup('', '※ <a href="https://ja.wikipedia.org/wiki/Markdown" target="_blank">Markdown 形式</a> で記述可能です。', false, 'row-markdown');
				
				echo $this->Form->hidden('page_id', array('class' => 'form-group row-page-id'));
				
				echo $this->Form->input('file',		array('label' => '<span data-localize="attachment">添付ファイル</span>', 'class' => 'form-control form-control-upload'));

				echo $this->Form->input('status',	array(
					'type' => 'radio',
					'before' => '<label class="col col-sm-3 control-label"><span data-localize="status">課題のステータス</span></label>',
					'separator'=>"　", 
					'disabled'=>false, 
					'legend' => false,
					'class' => false,
					'value' => $content['Task']['status'],
					'options' => Configure::read('task_status')
					)
				);
				
				$rate_list = array(
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
				);
				
				echo $this->Form->input('rate',		array(
					'label' => '<span data-localize="progress_rate">進捗率</span>', 
					'options'=>$rate_list, 
					'class' => 'form-control',
					'value' => $content['Task']['rate'],
					'div' => 'form-group row-progress',
				));
				
				Configure::read('emotion_icons');
				$emotion_icons = array();
				
				foreach(Configure::read('emotion_icons') as $key => $value)
				{
					$emotion_icons[$key] = $this->Html->image($value, array('width' => 40));
				}
				
				if(Configure::read('use_emotion_icon'))
				{
					echo $this->Form->input('emotion_icon',	array(
						'type' => 'radio',
						'before' => '<label class="col col-sm-3 control-label">感情</label><div>　※ 現在の感情を選択してください</div>',
						'after' => '',
						'separator'=>"　", 
						'legend' => false,
						'class' => false,
						'options' => $emotion_icons,
						)
					);
				}
				
				echo $this->Form->hidden('file_name', array('class' => 'form-control-filename'));
				
				
				// メール通知対象リスト
				$mail_target = '';
				
				foreach($mail_list as $item)
				{
					if($mail_target!='')
						$mail_target .= ', ';
					
					$mail_target .= str_replace(',', '', $item['name']);
				}
			?>
			<div class="form-group">
				<div class="col col-sm-9 col-sm-offset-3">
					<p> ※ 学習テーマの所有者・関係者・管理者 (<?php echo $mail_target; ?>) に更新が発生した旨を通知します<br><input name="is_mail" type="checkbox">&nbsp;<span data-localize="email_notification">メール通知</span></p>
				</div>
				<div class="col col-sm-9 col-sm-offset-3">
					<?php echo $this->Form->submit(($is_add) ? __('追加') :  __('保存'), Configure::read('form_submit_defaults')); ?>
				</div>
			</div>
			<input name="study_sec" type="hidden" value="0">
			<?php echo $this->Form->end(); ?>
		</div>
	</div>
	
	
	
	<?php if(count($progresses) > 0) {?>
	<div onclick="$('html, body').animate({scrollTop: 0},800);">
	<a href="#">　▲ ページのTOPへ</a>
	</div>
	<br>
	<?php }?>
</div>