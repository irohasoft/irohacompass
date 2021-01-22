<div class="infos-view">
	<div class="breadcrumb">
	<?php
	$this->Html->addCrumb('HOME', [
			'controller' => 'users_themes',
			'action' => 'index'
	]);

	$this->Html->addCrumb('お知らせ一覧', [
		'controller' => 'infos',
		'action' => 'index'
	]);

	echo $this->Html->getCrumbs(' / ');
	
	$title = h($info['Info']['title']);
	$date  = h(Utils::getYMD($info['Info']['created']));
	$body  = $info['Info']['body'];
	//$body  = $this->Text->autoLinkUrls($body);
	// 暫定対応
	$body  = $this->Text->autoLinkUrls($body, ['escape' => false]);
	$body  = nl2br($body);
	?>
	</div>

	<div class="panel panel-success">
		<div class="panel-heading"><?php echo $title; ?></div>
		<div class="panel-body">
			<div class="text-right"><?php echo $date; ?></div>
			<?php echo $body; ?>
		</div>
	</div>
</div>
