<?php
/**
 * iroha Compass Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2018 iroha Soft, Inc. (http://irohasoft.jp)
 * @link          http://irohacompass.irohasoft.jp
 * @license       http://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */

App::uses('Controller', 'Controller');
App::uses('CakeEmail', 'Network/Email');
App::import('Vendor', 'Utils');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{

	public $components = [
			'DebugKit.Toolbar',
			'Session',
			'Flash',
			'Auth' => [
					'loginRedirect' => [
							'controller' => 'users_themes',
							'action' => 'index'
					],
					'logoutRedirect' => [
							'controller' => 'users',
							'action' => 'login',
							'home'
					],
					'authError' => false
			]
	];
	
	//public $helpers = array('Session');
	public $helpers = [
		'Session',
		'Html' => ['className' => 'BoostCake.BoostCakeHtml'],
		'Form' => ['className' => 'BoostCake.BoostCakeForm'],
		'Paginator' => ['className' => 'BoostCake.BoostCakePaginator'],
	];
	
	public $uses = ['Setting'];
	
	public function beforeFilter()
	{
		$this->set('loginedUser', $this->Auth->user());
		
		// 他のサイトの設定が存在する場合、設定情報及びログイン情報をクリア
		if($this->Session->check('Setting'))
		{
			if($this->Session->read('Setting.app_dir')!=APP_DIR)
			{
				// セッション内の設定情報を削除
				$this->Session->delete('Setting');
				
				// 他のサイトとのログイン情報の混合を避けるため、強制ログアウト
				if($this->Auth->user())
				{
					if($this->Cookie)
						$this->Cookie->delete('Auth');
					
					$this->redirect($this->Auth->logout());
					return;
				}
			}
		}
		
		// データベース内に格納された設定情報をセッションに格納
		if(!$this->Session->check('Setting'))
		{
			$settings = $this->Setting->getSettings();
			
			$this->Session->Write('Setting.app_dir', APP_DIR);
			
			foreach ($settings as $key => $value)
			{
				$this->Session->Write('Setting.'.$key, $value);
			}
		}
		
		if (isset($this->request->params['admin']))
		{
			// role が admin, manager, editor, teacher以外の場合、強制ログアウトする
			if($this->Auth->user())
			{
				if(
					($this->Auth->user('role')!='admin')&&
					($this->Auth->user('role')!='manager')&&
					($this->Auth->user('role')!='editor')&&
					($this->Auth->user('role')!='teacher')
				)
				{
					if($this->Cookie)
						$this->Cookie->delete('Auth');
					
					$this->redirect($this->Auth->logout());
					return;
				}
			}
			
			$this->Auth->loginAction = [
					'controller' => 'users',
					'action' => 'login',
					'admin' => true
			];
			$this->Auth->loginRedirect = [
					'controller' => 'users',
					'action' => 'index',
					'admin' => true
			];
			$this->Auth->logoutRedirect = [
					'controller' => 'users',
					'action' => 'login',
					'admin' => true
			];
			$this->set('loginURL', "/admin/users/login/");
			$this->set('logoutURL', "/admin/users/logout/");
			
			// グループ一覧を共通で保持する
			$this->loadModel('Group');
			$group_list = $this->Group->find('all');
			
			$this->set('group_list', 
					$this->Group->find('list', 
							[
									'fields' => [
											'id',
											'title'
									]
							]));
		}
		else
		{
			$this->Auth->loginAction = [
					'controller' => 'users',
					'action' => 'login',
					'admin' => false
			];
			$this->Auth->loginRedirect = [
					'controller' => 'users',
					'action' => 'index',
					'admin' => false
			];
			$this->Auth->logoutRedirect = [
					'controller' => 'users',
					'action' => 'login',
					'admin' => false
			];
			
			$this->set('loginURL', "/users/login/");
			$this->set('logoutURL', "/users/logout/");
			// $this->layout = 'login'; //レイアウトを切り替える。
			// AuthComponent::$sessionKey = "Auth.User";
		}
		
		$user = $this->Auth->user();
		
		if($user['lang']=='en')
		{
			Configure::write('progress_type', [
				'progress'	=> 'Progress',
				'comment'	=> 'Comment',
				'idea'		=> 'Idea, Memo',
				'question'	=> 'Question',
				'answer'	=> 'Answer',
			]);
			Configure::write('task_status', [
				'1' => 'Waiting',
				'2' => 'Working',
				'3' => 'Completed'
			]);
			Configure::write('task_priority', [
				'1' => 'High',
				'2' => 'Middle',
				'3' => 'Low'
			]);
			Configure::write('content_type', [
				'text'		=> 'Text',
				'markdown'	=> 'Markdown',
				'irohanote'	=> 'Idea Map',
			]);
		}
	}

	public function beforeRender()
	{
		//header("X-XSS-Protection: 1; mode=block")
		
		// iframeへの埋め込みの禁止
		//header("X-Frame-Options: DENY");
	}

	function writeLog($log_type, $log_content, $controller = '', $action = '', $params = '', $sec = 0)
	{
		$data = [
			'log_type'		=> $log_type,
			'log_content'	=> $log_content,
			'controller'	=> $controller,
			'action'		=> $action,
			'params'		=> $params,
			'sec'			=> $sec,
			'user_id'		=> $this->Auth->user('id'),
			'user_ip'		=> $_SERVER['REMOTE_ADDR'],
			'user_agent'	=> $_SERVER['HTTP_USER_AGENT']
		];
		
		$this->loadModel('Log');
		$this->Log->create();
		$this->Log->save($data);
	}

}
