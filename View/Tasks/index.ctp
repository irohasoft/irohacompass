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
				var html = marked.parse($(target).val(),
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
			<?= Utils::getNoteLink($page_id, $this->Html);?>
		</div>
		<div>
			<button type="button" class="btn btn-primary btn-success" onclick="location.href='<?= Router::url(['controller' => 'themes', 'action' => 'edit', $theme['Theme']['id']]) ?>'"><?= __('編集')?></button>
		</div>
	</div>
	<div class="buttons_container">
		<button type="button" class="btn btn-primary btn-add" onclick="location.href='<?= Router::url(['action' => 'add', $theme['Theme']['id']]) ?>'">+ <?= __('課題を追加')?></button>
	</div>
	<div class="ib-horizontal">
		<?php
		$status_list = Configure::read('task_status');
		$status_list[99] = __('完了以外');
		
		echo $this->Form->create('Task');
		echo $this->Form->searchField('status',	['label' => __('ステータス'), 'options' => $status_list, 'selected' => $status, 'empty' => '全て', 'onchange'	=> 'submit(this.form);']);
		echo $this->Form->searchField('keyword',['label' => __('キーワード'), 'value' => $keyword]);
		?>
		<button class="btn btn-info btn-add"><?= __('検索')?></button>
		<?php
			echo $this->Form->end();
		?>
	</div>
	<table class="responsive-table">
		<thead>
			<tr>
				<th nowrap><?= $this->Paginator->sort('title',						__('課題名')); ?></th>
				<th class="text-center" nowrap><?= $this->Paginator->sort('status',	__('状態')); ?></th>
				<th class="text-center" nowrap><?= $this->Paginator->sort('rate',	__('進捗率')); ?></th>
				
				<th class="text-center" nowrap><?= $this->Paginator->sort('priority',	__('優先度')); ?></th>
				<th class="text-center" nowrap><?= $this->Paginator->sort('deadline',	__('期日')); ?></th>
				<th class="ib-col-date" nowrap><?= $this->Paginator->sort('created',	__('作成日時')); ?></th>
				<th class="ib-col-date" nowrap><?= $this->Paginator->sort('modified',	__('更新日時')); ?></th>
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
					<button type="button" class="btn btn-success" onclick="location.href='<?= Router::url(['action' => 'edit', $theme['Theme']['id'], $task['Task']['id']]) ?>'"><?= __('編集')?></button>
					<?php
						echo $this->Form->postLink(__('削除'),
							['action' => 'delete', $task['Task']['id']],
							['class'=>'btn btn-danger'],
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
