  <nav class="navbar navbar-default">
    <div class="container">
      <div class="navbar-collapse collapse">
        <ul class="nav navbar-nav">
<?php
		$is_active = (($this->name=='Users')&&($this->params["action"]!='admin_password')) ? ' active' : '';
		echo '<li class="'.$is_active.'">'.$this->Html->link(__('ユーザ'), ['controller' => 'users', 'action' => 'index']).'</li>';
	
		$is_active = ($this->name=='Groups') ? ' active' : '';
		echo '<li class="'.$is_active.'">'.$this->Html->link(__('グループ'), ['controller' => 'groups', 'action' => 'index']).'</li>';
		
		$is_active = ((($this->name=='Themes')||($this->name=='Tasks')||($this->name=='Progresses')) && (strpos($this->params["action"], 'enq') <  1)) ? ' active' : '';
		echo '<li class="'.$is_active.'">'.$this->Html->link(__('学習テーマ'), ['controller' => 'themes', 'action' => 'index']).'</li>';
		
		/*
		$is_active = ((($this->name=='Themes')||($this->name=='Tasks')||($this->name=='Progresses')) && (strpos($this->params["action"], 'enq') > -1)) ? ' active' : '';
		echo '<li class="'.$is_active.'">'.$this->Html->link(__('アンケート'), array('controller' => 'tasks', 'action' => 'index_enq')).'</li>';
		*/
	
		$is_active = ($this->name=='Infos') ? ' active' : '';
		echo '<li class="'.$is_active.'">'.$this->Html->link(__('お知らせ'), ['controller' => 'infos', 'action' => 'index']).'</li>';
	
		$is_active = ($this->name=='Records') ? ' active' : '';
		echo '<li class="'.$is_active.'">'.$this->Html->link(__('進捗'), ['controller' => 'records', 'action' => 'index']).'</li>';

		if($loginedUser['role']=='admin')
		{
			$is_active = ($this->name=='Settings') ? ' active' : '';
			echo '<li class="'.$is_active.'">'.$this->Html->link(__('システム設定'), ['controller' => 'settings', 'action' => 'index']).'</li>';
		}
?>
        </ul>
      </div><!--/.nav-collapse -->
    </div>
  </nav>
