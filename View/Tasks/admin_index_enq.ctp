<?= $this->element('admin_menu'); ?>
<?php $this->start('css-embedded'); ?>
<!--[if !IE]><!-->
<style>
@media only screen and (max-width:800px)
{
	.responsive-table
	{
		display: block;
	}

	.responsive-table thead
	{
		display: none;
	}

	.responsive-table tbody
	{
		display: block;
	}

	.responsive-table tbody tr
	{
		display: block;
		margin-bottom: 1.5em;
	}

	.responsive-table tbody th,
	.responsive-table tbody td
	{
		display: list-item;
		list-style: none;
		border: none;
	}

	.responsive-table tbody th
	{
		margin-bottom: 5px;
		list-style-type: none;
		color: #fff;
		background: #000;
	}

	.responsive-table tbody td
	{
		margin-left: 10px;
		padding: 0;
	}

	.responsive-table a
	{
		font-size: 18px;
		font-weight: bold;
	}

	.responsive-table tbody td:before { width: 100px; display: inline-block;}
	.responsive-table tbody td:nth-of-type(2):before { width: 100px; display: inline-block; content: "種別 : ";}
	.responsive-table tbody td:nth-of-type(3):before { content: "進捗率 : "; }
	.responsive-table tbody td:nth-of-type(4):before { content: "優先度 : "; }
	.responsive-table tbody td:nth-of-type(5):before { content: "期日 : "; }
	.responsive-table tbody td:nth-of-type(6):before { content: "作成日時 : "; }
	.responsive-table tbody td:nth-of-type(7):before { content: "更新日時 : "; }
	
	.ib-col-center,
	.ib-col-date
	{
		text-align: left;
		width:100%;
	}
	
	.text-center
	{
		text-align: left;
	}
}
.content-label
{
	/*
	background: #999;
	color: #fff;
	*/
	font-size: 22px;
	padding-bottom: 0px;
}

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
	</script>
<?php $this->end(); ?>
<div class="tasks index">
	<div class="ib-page-title"><?= __('アンケート一覧'); ?></div>
	<div class="buttons_container">
		<button type="button" class="btn btn-primary btn-add" onclick="location.href='<?= Router::url(['action' => 'add_enq']) ?>'">+ 追加</button>
	</div>
	<table id='sortable-table'>
	<thead>
	<tr>
		<th>コンテンツ名</th>
		<th>コンテンツ種別</th>
		<th class="ib-col-date">作成日時</th>
		<th class="ib-col-date">更新日時</th>
		<th class="ib-col-action"><?= __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($tasks as $content): ?>
	<tr>
		<td>
			<?php
				echo $this->Html->link($content['Task']['title'], ['controller' => 'progresses', 'action' => 'index_enq', $content['Task']['id']]);
				echo $this->Form->hidden('id', ['id'=>'', 'class'=>'content_id', 'value'=>$content['Task']['id']]);
			?>
		</td>
		<td><?= h(Configure::read('content_kind.'.$content['Task']['kind'])); ?>&nbsp;</td>
		<td class="ib-col-date"><?= Utils::getYMDHN($content['Task']['created']); ?>&nbsp;</td>
		<td class="ib-col-date"><?= Utils::getYMDHN($content['Task']['modified']); ?>&nbsp;</td>
		<td class="ib-col-action">
			<button type="button" class="btn btn-success" onclick="location.href='<?= Router::url(['action' => 'edit_enq', $content['Task']['id']]) ?>'">編集</button>
			<?php
			if($loginedUser['role']=='admin')
			{
				echo $this->Form->postLink(__('削除'),
					['action' => 'delete_enq', $content['Task']['id']],
					['class'=>'btn btn-danger'],
					__('[%s] を削除してもよろしいですか?', $content['Task']['title'])
				);
			}?>
		</td>
	</tr>
	<?php endforeach; ?>
	</tbody>
	</table>
</div>
