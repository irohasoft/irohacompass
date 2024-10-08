<?php
/**
 * iroha Compass Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2021 iroha Soft, Inc. (https://irohasoft.jp)
 * @link          https://irohacompass.irohasoft.jp
 * @license       https://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */
?>
<!DOCTYPE html>
<html>
<head>
	<?= $this->Html->charset(); ?>
	
	<title><?= h($this->readSession('Setting.title')); ?></title>
	<meta name="application-name" content="<?= APP_NAME; ?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
	<?php
		// 管理画面か確認、ただしログイン画面は例外とする
		$is_admin_page = $this->isAdminPage() && !$this->isLoginPage();
		
		// 受講者向け画面及び、管理システムのログイン画面のみ viewport を設定（スマートフォン対応）
		if(!$is_admin_page)
			echo '<meta name="viewport" content="width=device-width,initial-scale=1">';
		
		echo $this->Html->meta('icon');

		//echo $this->Html->css('cake.generic');
		echo $this->Html->css('jquery-ui.min');
		echo $this->Html->css('bootstrap.min');
		echo $this->Html->css('common.css?20210701');
		
		// 管理画面用CSS
		if($is_admin_page)
			echo $this->Html->css('admin.css?20190401');

		// カスタマイズ用CSS
		echo $this->Html->css('custom.css?20190401');
		
		echo $this->Html->script('jquery-3.6.4.min.js');
		echo $this->Html->script('jquery-ui-1.13.2.min.js');
		echo $this->Html->script('jquery.localize.min.js');
		echo $this->Html->script('bootstrap.min.js');
		echo $this->Html->script('marked.min.js');
		echo $this->Html->script('common.js?20200622');
		
		// 管理画面用スクリプト
		if($is_admin_page)
			echo $this->Html->script('admin.js?20190401');
		
		// デモモード用スクリプト
		if(Configure::read('demo_mode'))
			echo $this->Html->script('demo.js');
		
		// カスタマイズ用スクリプト
		echo $this->Html->script('custom.js?20190401');
		
		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
		echo $this->fetch('css-embedded');
		echo $this->fetch('script-embedded');
	?>
	<script>
	var _controller		= '<?= $this->params['controller'] ?>';
	var _action			= '<?= $this->params['action'] ?>';
	var _params			= '<?= join(',', $this->params['pass']) ?>';
	var _sec			= 0;
	var G_WEBROOT		= '<?= $this->Html->webroot;?>';
	var G_LANG			= '<?= isset($loginedUser) ? $loginedUser['lang'] : 'jp';?>';
	var URL_LOGS_ADD	= '<?= Router::url(['controller' => 'logs', 'action' => 'add']) ?>';
	
	var URL_LOGS_ADD	= '<?= Router::url(['controller' => 'logs', 'action' => 'add']) ?>';
	var ACCESS_COUNT_LABEL = '<?= __('アクセス回数') ?>';
	var UPDATE_COUNT_LABEL = '<?= __('進捗更新回数') ?>';
	
	</script>
	<style>
		.ib-theme-color
		{
			background-color	: <?= h($this->readSession('Setting.color')); ?>;
			color				: white;
		}
		
		.ib-logo a
		{
			color				: white;
			text-decoration		: none;
		}
	</style>
</head>
<body>
	<div class="header ib-theme-color">
		<div class="ib-logo ib-left">
			<a href="<?= $this->Html->url('/')?>"><?= h($this->readSession('Setting.title')); ?></a>
		</div>
		<?php if(isset($loginedUser)) {?>
		<div class="ib-navi-item ib-right ib-navi-logout"><?= $this->Html->link(__('ログアウト'), ['controller' => 'users', 'action' => 'logout']); ?></div>
		<div class="ib-navi-sepa ib-right ib-navi-sepa-1 "></div>
		<div class="ib-navi-item ib-right ib-navi-setting"><?= $this->Html->link(__('設定'), ['controller' => 'users', 'action' => 'setting']); ?></div>
		<div class="ib-navi-sepa ib-right ib-navi-sepa-2 "></div>
		<!--
		<div class="ib-navi-item ib-right navi-item-idea"><?= $this->Html->link(__('アイデア'), ['controller' => 'ideas']); ?></div>
		<div class="ib-navi-sepa ib-right ib-navi-sepa-3"></div>
		-->
		<div class="ib-navi-item ib-right ib-navi-welcome"><?= __('ようこそ').' '.h($loginedUser['name']).' '.__('さん'); ?></div>
		<?php }?>
	</div>
	
	<div id="container">
		<div id="content" class="row">
			<?= $this->Session->flash(); ?>
			<?= $this->fetch('content'); ?>
		</div>
	</div>
	
	<div class="footer ib-theme-color text-center">
		<?= h($this->readSession('Setting.copyright')); ?>
	</div>
	
	<?php if(isset($loginedUser)) {?>
	<div class="irohasoft">
		Powered by <a href="https://irohacompass.irohasoft.jp/"><?= APP_NAME; ?></a>
	</div>
	<?php }?>
	
	<?= $this->element('sql_dump'); ?>
</body>
</html>
