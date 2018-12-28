<?php if(!$is_user) echo $this->element('admin_menu');?>
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
.task-label
{
	/*
	background: #999;
	color: #fff;
	*/
	font-size: 22px;
	padding-bottom: 0px;
}

<?php if($this->action=='admin_record') {?>
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
			if($("#introduction"))
			{
				var html = marked($('#introduction').val(),
				{
	//				renderer: _markedRenderer,
					breaks: true,
					sanitize: true
				});
				
				$("#introduction").before(html);
			}
		});
		
		function openNote(page_id)
		{
			window.open('<?php echo Router::url(array('controller' => 'notes', 'action' => 'page', 'admin' => false))?>/'+page_id, '_note', 'width=1000,height=700,resizable=yes');
		}
	</script>
<?php $this->end(); ?>
<div class="tasks index">
	<div class="ib-breadcrumb">
	<?php
	$controller = ($is_user) ? 'users_themes' : 'themes';
	
	if($this->action!='admin_record')
	{
		$this->Html->addCrumb('<< '.__('学習テーマ一覧'), array(
			'controller' => $controller,
			'action' => 'index'
		));
	}

	echo $this->Html->getCrumbs(' / ');
	//debug($tasks);

	?>
	</div>

	<div class="panel panel-info">
	<div class="panel-heading"><?php echo h($theme['Theme']['title']); ?></div>
	<div class="panel-body">
	<div class="well">
		<?php if($theme['Theme']['introduction']!='') {?>
		<?php echo $this->Form->hidden('introduction', array('value' => $theme['Theme']['introduction'], 'id' => 'introduction'));?>
		<?php }?>
		<div>
			<?php echo Utils::getNoteLink($theme['Theme']['page_id'], $theme['Theme']['page_image'], $this->Html);?>
		</div>
		<div>
			<button type="button" class="btn btn-primary btn-success" onclick="location.href='<?php echo Router::url(array('controller' => 'themes', 'action' => 'edit', $theme['Theme']['id'])) ?>'">編集</button>
		</div>
	</div>
	<div class="buttons_container">
		<button type="button" class="btn btn-primary btn-add" onclick="location.href='<?php echo Router::url(array('action' => 'add', $theme['Theme']['id'])) ?>'">+ 課題を追加</button>
	</div>
	<div class="ib-horizontal">
		<?php
			$status_list = Configure::read('task_status');
			$status_list[99] = '完了以外';
			
			echo $this->Form->create('Task');
			echo $this->Form->input('status', array(
				'label'		=> 'ステータス : ', 
				'options'	=> $status_list, 
				'selected'	=> $status, 
				'empty'		=> '全て', 
				'required'	=> false, 
				'class'		=> 'form-control',
				'onchange'	=> 'submit(this.form);'
			));
			
			echo $this->Form->input('keyword',		array('label' => 'キーワード : ', 'value' => $keyword, 'required' => false));
		?>
		<input type="submit" class="btn btn-info btn-add" value="検索">
		<?php
			echo $this->Form->end();
		?>
	</div>
	<table class="responsive-table">
		<thead>
			<tr>
				<th nowrap><?php echo $this->Paginator->sort('title',			'課題名'); ?></th>
				<th class="text-center" nowrap><?php echo $this->Paginator->sort('status',			'状態'); ?></th>
				<th class="text-center" nowrap><?php echo $this->Paginator->sort('rate',			'進捗率'); ?></th>
				<th class="text-center" nowrap><?php echo $this->Paginator->sort('priority',		'優先度'); ?></th>
				<th class="text-center" nowrap><?php echo $this->Paginator->sort('deadline',		'期日'); ?></th>
				<th class="ib-col-date" nowrap><?php echo $this->Paginator->sort('created',	'作成日時'); ?></th>
				<th class="ib-col-date" nowrap><?php echo $this->Paginator->sort('modified',	'更新日時'); ?></th>
				<th class="actions text-center" nowrap><?php echo __('Actions'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($tasks as $task): ?>
			<tr>
				<td>
					<?php
						echo $this->Html->link($task['Task']['title'], array('controller' => 'progresses', 'action' => 'index', $task['Task']['id']));
						echo $this->Form->hidden('id', array('id'=>'', 'class'=>'task_id', 'value'=>$task['Task']['id']));
					?>
				</td>
				<td class="text-center" nowrap><?php echo Configure::read('task_status.'.$task['Task']['status']); ?>&nbsp;</td>
				<td class="text-center" nowrap><?php echo $task['Task']['rate']; ?>%&nbsp;</td>
				<td class="text-center" nowrap><?php echo Configure::read('task_priority.'.$task['Task']['priority']); ?>&nbsp;</td>
				<td class="text-center" nowrap><?php echo Utils::getYMD($task['Task']['deadline']); ?>&nbsp;</td>
				<td class="ib-col-date" nowrap><?php echo Utils::getYMDHN($task['Task']['created']); ?>&nbsp;</td>
				<td class="ib-col-date" nowrap><?php echo Utils::getYMDHN($task['Task']['modified']); ?>&nbsp;</td>
				<td class="ib-col-action">
					<button type="button" class="btn btn-success" onclick="location.href='<?php echo Router::url(array('action' => 'edit', $theme['Theme']['id'], $task['Task']['id'])) ?>'">編集</button>
					<?php
						echo $this->Form->postLink(__('削除'),
							array('action' => 'delete', $task['Task']['id']),
							array('class'=>'btn btn-danger'),
							__('[%s] を削除してもよろしいですか?', $task['Task']['title'])
						);
					?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php echo $this->element('paging');?>

	</div>
	</div>
</div>
