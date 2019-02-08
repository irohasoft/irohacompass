<?php if(!$is_user) echo $this->element('admin_menu');?>
<?php $this->start('css-embedded'); ?>
<style type='text/css'>
	.td-reader
	{
		width:200px;
		text-overflow:ellipsis;
		overflow:hidden;
		white-space:nowrap;
	}

	table
	{
		table-layout:fixed;
	}

	#sortable-table tbody
	{
		cursor: move;
	}

	.progress-text,
	.correct-text
	{
		padding: 10px;
		border-radius	: 6px;
		margin-bottom: 10px;
		word-wrap: break-word;
	}

	.panel-heading
	{
		min-height: 36px;
	}
</style>
<?php $this->end(); ?>
<?php $this->start('css-embedded'); ?>
<script>
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
	});
</script>
<?php $this->end(); ?>

<div class="progresses index">
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
		<div class="panel-heading"><b>課題</b></div>
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
				<button type="button" class="btn btn-primary btn-success" onclick="location.href='<?php echo Router::url(array('controller' => 'tasks', 'action' => 'edit', $content['Theme']['id'], $content['Task']['id'])) ?>'">編集</button>
			</div>
		</div>
	</div>

	<div class="ib-page-title"><?php echo __('進捗一覧'); ?></div>
	
	<div class="buttons_container">
		<button type="button" class="btn btn-primary btn-add" onclick="location.href='<?php echo Router::url(array('action' => 'add', $content['Task']['id'])) ?>'">+ 追加</button>
	</div>
	<?php if(count($progresses) > 0) {?>
	<div onclick="$('html, body').animate({scrollTop: $(document).height()},800);">
	<a href="#">　▼ ページの下へ</a>
	</div>
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
			<?php if($progress['Progress']['progress_type']=='progress') { ?>
			<div class="text-left">
				進捗率 : <?php echo h($progress['Progress']['rate']); ?>%
			</div>
			<?php }?>
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
			<button type="button" class="btn btn-success" onclick="location.href='<?php echo Router::url(array('action' => 'edit', $progress['Task']['id'], $progress['Progress']['id'])) ?>'">編集</button>
			<?php
			echo $this->Form->postLink(__('削除'), 
					array('action' => 'delete', $progress['Progress']['id']), 
					array('class'=>'btn btn-danger'), 
					__('[%s] を削除してもよろしいですか?', $progress['Progress']['title'])
			); 
			echo $this->Form->hidden('id', array('id'=>'', 'class'=>'target_id', 'value'=>$progress['Progress']['id']));
			?>
			</div>
		</div>
	</div>
	<?php endforeach; ?>
	
	<?php if(count($progresses) > 0) {?>
	<div class="buttons_container">
		<button type="button" class="btn btn-primary btn-add" onclick="location.href='<?php echo Router::url(array('action' => 'add', $content['Task']['id'])) ?>'">+ 追加</button>
	</div>
	<div onclick="$('html, body').animate({scrollTop: 0},800);">
	<a href="#">　▲ ページのTOPへ</a>
	</div>
	<br>
	<?php }?>
</div>