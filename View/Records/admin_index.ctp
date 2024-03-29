<?= $this->element('admin_menu');?>
<?php $this->start('script-embedded'); ?>
<script>
	function openRecord(theme_id, user_id)
	{
		window.open(
			'<?= Router::url(['controller' => 'tasks', 'action' => 'record']) ?>/'+theme_id+'/'+user_id,
			'irohacompass_record',
			'width=1100, height=700, menubar=no, toolbar=no, scrollbars=yes'
		);
	}
	
	function openTestRecord(content_id, record_id)
	{
		window.open(
			'<?= Router::url(['controller' => 'progresses', 'action' => 'record_each']) ?>/'+content_id+'/'+record_id,
			'irohacompass_record',
			'width=1100, height=700, menubar=no, toolbar=no, scrollbars=yes'
		);
	}
	
	function openProgress(user_id, user_name)
	{
		document.getElementById("fraDetail").src = '<?= Router::url(['controller' => 'records', 'action' => 'progress'])?>/' + user_id;
		$('#progressModal .modal-title').text(user_name + ' さんの進捗');
		$('#progressModal').modal();
	}
	
	function downloadCSV()
	{
		var url = '<?= Router::url(['action' => 'csv']) ?>/' + $('#MembersEventEventId').val() + '/' + $('#MembersEventStatus').val() + '/' + $('#MembersEventUsername').val();
		$("#RecordCmd").val("csv");
		$("#RecordAdminIndexForm").submit();
		$("#RecordCmd").val("");
	}
</script>
<?php $this->end(); ?>
<div class="admin-records-index">
	<div class="ib-page-title"><?= __('進捗一覧'); ?></div>
	<div class="ib-horizontal">
	<?php
		echo $this->Form->create('Record');
		echo '<div class="ib-search-buttons">';
		echo $this->Form->submit(__('検索'),	['class' => 'btn btn-info', 'div' => false]);
		echo $this->Form->hidden('cmd');
		echo '<button type="button" class="btn btn-default" onclick="downloadCSV()">'.__('CSV出力').'</button>';
		echo '</div>';
		
		echo '<div class="ib-row">';
		echo $this->Form->searchField('theme_id',	['label' => __('学習テーマ'), 'options' => $themes, 'empty' => '全て']);
		echo $this->Form->searchField('task_title',	['label' => __('課題名')]);
		echo '</div>';
		
		echo '<div class="ib-row">';
		echo $this->Form->searchField('group_id',	['label' => __('グループ'), 'options' => $groups, 'empty' => '全て', 'selected' => $group_id]);
		echo $this->Form->searchField('user_id',	['label' => __('ユーザ'), 'options' => $users, 'empty' => '全て']);
		echo $this->Form->searchField('username',	['label' => __('ログインID')]);
		echo '</div>';

		echo '<div class="ib-search-date-container">';
		echo $this->Form->searchDate('from_date', ['label'=> __('対象日時'), 'value' => $from_date]);
		echo $this->Form->searchDate('to_date',   ['label'=> __('～'), 'value' => $to_date]);
		echo '</div>';
		echo $this->Form->end();
	?>
	</div>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
		<th nowrap><?= $this->Paginator->sort('theme_id', __('学習テーマ')); ?></th>
		<th nowrap><?= $this->Paginator->sort('task_id', __('課題')); ?></th>
		<th nowrap><?= $this->Paginator->sort('User.name', __('氏名')); ?></th>
		<th nowrap class="ib-col-center"><?= $this->Paginator->sort('rate', __('進捗率')); ?></th>
		<th nowrap class="ib-col-center"><?= $this->Paginator->sort('theme_rate', __('進捗率(全体)')); ?></th>
		<th class="ib-col-center" nowrap><?= $this->Paginator->sort('record_type', __('種別')); ?></th>
		<th class="ib-col-center"><?= $this->Paginator->sort('study_sec', __('滞留時間')); ?></th>
		<th class="ib-col-datetime"><?= $this->Paginator->sort('created', __('更新日時')); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($records as $record): ?>
	<tr>
		<td><a href="<?= Router::url(['controller' => 'tasks', 'action' => 'index', $record['Theme']['id']]);?>"><?= h($record['Theme']['title']); ?></a></td>
		<td><a href="<?= Router::url(['controller' => 'progresses', 'action' => 'index', $record['Task']['id']]);?>"><?= h($record['Task']['title']); ?></a></td>
		<td><a href="javascript:openProgress('<?= h($record['User']['id']); ?>', '<?= h($record['User']['name']); ?>');"><?= h($record['User']['name']); ?></a></td>
		<td class="ib-col-center"><?= h($record['Record']['rate']); ?>&nbsp;</td>
		<td class="ib-col-center"><?= h($record['Record']['theme_rate']); ?>&nbsp;</td>
		<td nowrap class="ib-col-center"><?= h(Configure::read('record_type.'.$record['Record']['record_type'])); ?>&nbsp;</td>
		<td class="ib-col-center"><?= h(Utils::getHNSBySec($record['Record']['study_sec'])); ?>&nbsp;</td>
		<td class="ib-col-date"><?= h(Utils::getYMDHN($record['Record']['created'])); ?>&nbsp;</td>
	</tr>
	<?php endforeach; ?>
	</tbody>
	</table>
	<?= $this->element('paging');?>
</div>

<!--進捗ダイアログ-->
<div class="modal fade" id="progressModal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title">進捗</h4>
			</div>
			<div class="modal-body">
				<iframe id="fraDetail" class="modal-frame"></iframe>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">閉じる</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
