<?php if(!$is_user) echo $this->element('admin_menu');?>
<?php $this->start('css-embedded'); ?>
<!--[if !IE]><!-->
<style>
<?php if($this->isRecordPage()) {?>
.ib-navi-item
{
	display: none;
}

.ib-logo a
{
	pointer-events: none;
}
<?php }?>
</style>
<!--<![endif]-->
<?php $this->end(); ?>
<?php $this->start('script-embedded'); ?>
	<script>
		//var _markedRenderer = new marked.Renderer;
		$(function(){
			markedText('#learning_target');
		});

		function markedText(target)
		{
			if($(target))
			{
				var html = marked($(target).val(),
				{
	//				renderer: _markedRenderer,
					breaks: true,
					sanitize: true
				});
				
				$(target).before(html);
			}
		}
		
		function openNote(page_id)
		{
			window.open('<?= Router::url(['controller' => 'notes', 'action' => 'page', 'admin' => false])?>/'+page_id, '_note', 'width=1000,height=700,resizable=yes');
		}
	</script>
<?php $this->end(); ?>
<div class="tasks-index">
	<div class="ib-breadcrumb">
	<?php
	$controller = ($is_user) ? 'users_themes' : 'themes';
	
	if(!$this->isRecordPage())
	{
		$this->Html->addCrumb('<< '.__('学習テーマ一覧'), [
			'controller' => $controller,
			'action' => 'index'
		]);
	}
	
	echo $this->Html->getCrumbs(' / ');
	//debug($tasks);

	?>
	</div>

	<div class="panel panel-info">
	<div class="panel-heading lead"><?= h($theme['Theme']['title']); ?></div>
	<div class="panel-body">
	<div class="well">
		<?php if($theme['Theme']['learning_target']!='') {?>
		<?= $this->Form->hidden('learning_target', ['value' => $theme['Theme']['learning_target'], 'id' => 'learning_target']);?>
		<?php }?>
		<div>
			<?= Utils::getNoteLink($theme['Theme']['page_id'], $this->Html);?>
		</div>
		<div>
			<button type="button" class="btn btn-primary btn-success" onclick="location.href='<?= Router::url(['controller' => 'themes', 'action' => 'edit', $theme['Theme']['id']]) ?>'"><span data-localize='edit'>編集</span></button>
		</div>
	</div>
	<div class="buttons_container">
		<button type="button" class="btn btn-primary btn-add" onclick="location.href='<?= Router::url(['action' => 'add', $theme['Theme']['id']]) ?>'"><span data-localize='add_task'>+ 課題を追加</span></button>
	</div>
	<div class="ib-horizontal">
		<?php
			$status_list = Configure::read('task_status');
			$status_list[99] = '完了以外';
			
			echo $this->Form->create('Task');
			echo $this->Form->input('status', [
				'label'		=> '<span data-localize="status">ステータス</span> : ', 
				'options'	=> $status_list, 
				'selected'	=> $status, 
				'empty'		=> '全て', 
				'required'	=> false, 
				'class'		=> 'form-control',
				'onchange'	=> 'submit(this.form);'
			]);
			
			echo $this->Form->input('keyword',		['label' => '<span data-localize="keyword">キーワード</span> : ', 'value' => $keyword, 'required' => false]);
		?>
		<button class="btn btn-info btn-add"><span data-localize="search">検索</span></button>
		<?php
			echo $this->Form->end();
		?>
	</div>
	<table class="responsive-table">
		<thead>
			<tr>
				<th nowrap><span data-localize='task'><?= $this->Paginator->sort('title',			'課題名'); ?></span></th>
				<th class="text-center" nowrap><span data-localize='status'><?= $this->Paginator->sort('status',			'状態'); ?></span></th>
				<th class="text-center" nowrap><span data-localize='progress_rate'><?= $this->Paginator->sort('rate',	'進捗率'); ?></span></th>
				<th class="text-center" nowrap><span data-localize='priority'><?= $this->Paginator->sort('priority',		'優先度'); ?></span></th>
				<th class="text-center" nowrap><span data-localize='deadline'><?= $this->Paginator->sort('deadline',		'期日'); ?></span></th>
				<th class="ib-col-date" nowrap><span data-localize='created_date'><?= $this->Paginator->sort('created',	'作成日時'); ?></span></th>
				<th class="ib-col-date" nowrap><span data-localize='updated_date'><?= $this->Paginator->sort('modified',	'更新日時'); ?></span></th>
				<th class="actions text-center" nowrap><?= __('Actions'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($tasks as $task): ?>
			<tr>
				<td>
					<?php
						echo $this->Html->link($task['Task']['title'], ['controller' => 'progresses', 'action' => 'index', $task['Task']['id']]);
						echo $this->Form->hidden('id', ['id'=>'', 'class'=>'task_id', 'value'=>$task['Task']['id']]);
					?>
				</td>
				<td class="text-center" nowrap><?= Configure::read('task_status.'.$task['Task']['status']); ?>&nbsp;</td>
				<td class="text-center" nowrap><?= $task['Task']['rate']; ?>%&nbsp;</td>
				<td class="text-center" nowrap><?= Configure::read('task_priority.'.$task['Task']['priority']); ?>&nbsp;</td>
				<td class="text-center" nowrap><?= Utils::getYMD($task['Task']['deadline']); ?>&nbsp;</td>
				<td class="ib-col-date" nowrap><?= Utils::getYMDHN($task['Task']['created']); ?>&nbsp;</td>
				<td class="ib-col-date" nowrap><?= Utils::getYMDHN($task['Task']['modified']); ?>&nbsp;</td>
				<td class="ib-col-action">
					<button type="button" class="btn btn-success" onclick="location.href='<?= Router::url(['action' => 'edit', $theme['Theme']['id'], $task['Task']['id']]) ?>'"><span data-localize='edit'>編集</span></button>
					<?php
						echo $this->Form->postLink(__('削除'),
							['action' => 'delete', $task['Task']['id']],
							['class'=>'btn btn-danger', 'data-localize' => 'delete'],
							__('[%s] を削除してもよろしいですか?', $task['Task']['title'])
						);
					?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?= $this->element('paging');?>

	</div>
	</div>
</div>
