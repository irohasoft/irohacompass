<?= $this->element('admin_menu');?>
<div class="admin-themes-index">
	<div class="ib-page-title"><?= __('学習テーマ一覧'); ?></div>
	<div class="buttons_container">
		<button type="button" class="btn btn-primary btn-add" onclick="location.href='<?= Router::url(['action' => 'add']) ?>'">+ 追加</button>
	</div>

	<table id='sortable-table'>
	<thead>
	<tr>
		<th><?= $this->Paginator->sort('Theme.title', __('学習テーマ名')); ?></th>
		<th class="ib-col-datetime"><?= $this->Paginator->sort('User.name', __('所有者')); ?></th>
		<th class="ib-col-datetime"><?= $this->Paginator->sort('Theme.created', __('作成日時')); ?></th>
		<th class="ib-col-datetime"><?= $this->Paginator->sort('Theme.modified', __('更新日時')); ?></th>
		<th class="ib-col-action"><?= __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($themes as $theme): ?>
	<tr>
		<td>
			<?php
				echo $this->Html->link($theme['Theme']['title'], ['controller' => 'tasks', 'action' => 'index', $theme['Theme']['id']]);
				echo $this->Form->hidden('id', ['id'=>'', 'class'=>'theme_id', 'value'=>$theme['Theme']['id']]);
			?>
		</td>
		<td class="ib-col-date"><?= h($theme['User']['name']); ?>&nbsp;</td>
		<td class="ib-col-date"><?= h(Utils::getYMDHN($theme['Theme']['created'])); ?>&nbsp;</td>
		<td class="ib-col-date"><?= h(Utils::getYMDHN($theme['Theme']['modified'])); ?>&nbsp;</td>
		<td class="ib-col-action">
			<button type="button" class="btn btn-success" onclick="location.href='<?= Router::url(['action' => 'edit', $theme['Theme']['id']]) ?>'">編集</button>
			<?php
			if($loginedUser['role']=='admin')
			{
				echo $this->Form->postLink(__('削除'),
					['action' => 'delete', $theme['Theme']['id']],
					['class'=>'btn btn-danger'],
					__('[%s] を削除してもよろしいですか?', $theme['Theme']['title'])
				);
			}?>
		</td>
	</tr>
	<?php endforeach; ?>
	</tbody>
	</table>
	<?= $this->element('paging');?>
</div>
