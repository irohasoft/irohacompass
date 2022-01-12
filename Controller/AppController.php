<?php
/**
 * iroha Compass Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2021 iroha Soft, Inc. (https://irohasoft.jp)
 * @link          https://irohacompass.irohasoft.jp
 * @license       https://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */

App::uses('Controller', 'Controller');
App::uses('CakeEmail', 'Network/Email');
App::import('Vendor', 'Utils');

/**
 * Application Controller
 * https://book.cakephp.org/2/ja/controllers.html
 */
class AppController extends Controller
{
	/**
	 * 使用するコンポーネント
	 * https://book.cakephp.org/2/ja/core-libraries/toc-components.html
	 */
	public $components = [
			'DebugKit.Toolbar',
			'Session',
		'Cookie',
			'Flash',
			'Auth' => [
			'loginRedirect' => ['controller' => 'users_themes', 'action' => 'index'],
			'logoutRedirect' => ['controller' => 'users','action' => 'login','home'],
					'authError' => false
			]
	];
	
	/**
	 * 使用するヘルパー
	 * https://book.cakephp.org/2/ja/core-libraries/toc-helpers.html
	 */
	public $helpers = [
		'Session',
		'Html' => ['className' => 'BoostCake.BoostCakeHtml'],
		'Form' => ['className' => 'BoostCake.BoostCakeForm'],
		'Paginator' => ['className' => 'BoostCake.BoostCakePaginator'],
	];
	
	public $uses = ['Setting', 'Group'];
	public $viewClass = 'App'; // 独自のビュークラスを指定
	
	/**
	 * コールバック（コントローラのアクションロジック実行前に実行）
	 */
	public function beforeFilter()
	{
		$this->set('loginedUser', $this->readAuthUser()); // ログインユーザ情報（旧バージョン用）
		
		// 他のサイトの設定が存在する場合、設定情報及びログイン情報をクリア
		if($this->hasSession('Setting'))
		{
			if($this->readSession('Setting.app_dir') != APP_DIR)
			{
				// セッション内の設定情報を削除
				$this->deleteSession('Setting');
				
				// 他のサイトとのログイン情報の混合を避けるため、強制ログアウト
				if($this->readAuthUser())
				{
					//$this->Cookie->delete('Auth');
					$this->redirect($this->Auth->logout());
					return;
				}
			}
		}
		
		// データベース内に格納された設定情報をセッションに格納
		if(!$this->hasSession('Setting'))
		{
			$settings = $this->Setting->getSettings();
			
			$this->writeSession('Setting.app_dir', APP_DIR);
			
			foreach($settings as $key => $value)
			{
				$this->writeSession('Setting.'.$key, $value);
			}
		}
		
		if($this->isAdminPage())
		{
			// role が admin, manager, editor, teacher以外の場合、強制ログアウトする
			if($this->readAuthUser())
			{
				if(
					($this->readAuthUser('role') != 'admin')&&
					($this->readAuthUser('role') != 'manager')&&
					($this->readAuthUser('role') != 'editor')&&
					($this->readAuthUser('role') != 'teacher')
				)
				{
					if($this->Cookie)
						$this->Cookie->delete('Auth');
					
					$this->Flash->error(__('管理画面へのアクセス権限がありません'));
					$this->redirect($this->Auth->logout());
					return;
				}
			}
			
			$this->Auth->loginAction = ['controller' => 'users','action' => 'login','admin' => true];
			$this->Auth->loginRedirect = ['controller' => 'users','action' => 'index','admin' => true];
			$this->Auth->logoutRedirect = ['controller' => 'users','action' => 'login','admin' => true];
		}
		else
		{
			$this->Auth->loginAction = ['controller' => 'users', 'action' => 'login', 'admin' => false];
			$this->Auth->loginRedirect = ['controller' => 'users', 'action' => 'index', 'admin' => false];
			$this->Auth->logoutRedirect = ['controller' => 'users', 'action' => 'login', 'admin' => false];
			
			// ユーザの言語が英語の場合、翻訳ファイルをロード
			if($this->readAuthUser('lang') == 'en')
			{
				$this->loadTranslateMessages('en');
			}
		}
	}

	public function beforeRender()
	{
		//header("X-XSS-Protection: 1; mode=block")
		
		// 他のドメインからのiframeへの埋め込みの禁止
		header("X-Frame-Options: SAMEORIGIN");
	}

	/**
	 * セッションの取得
	 * @param string $key キー
	 */
	protected function readSession($key)
	{
		return $this->Session->read($key);
	}

	/**
	 * セッションの削除
	 * @param string $key キー
	 */
	protected function deleteSession($key)
	{
		$this->Session->delete($key);
	}

	/**
	 * セッションの存在確認
	 * @param string $key キー
	 */
	protected function hasSession($key)
	{
		return $this->Session->check($key);
	}

	/**
	 * セッションの保存
	 * @param string $key キー
	 * @param string $value 値
	 */
	protected function writeSession($key, $value)
	{
		$this->Session->write($key, $value);
	}

	/**
	 * クッキーの取得
	 * @param string $key キー
	 */
	protected function readCookie($key)
	{
		return $this->Cookie->read($key);
	}

	/**
	 * クッキーの削除
	 * @param string $key キー
	 */
	protected function deleteCookie($key)
	{
		$this->Cookie->delete($key);
	}

	/**
	 * クッキーの存在確認
	 * @param string $key キー
	 */
	protected function hasCookie($key)
	{
		return $this->Cookie->check($key);
	}

	/**
	 * クッキーの保存
	 * @param string $key キー
	 * @param string $value 値
	 */
	protected function writeCookie($key, $value, $secure = true, $expires = '+2 weeks')
	{
		$this->Cookie->write($key, $value, $secure, $expires);
	}

	/**
	 * ログインユーザ情報の取得
	 * @param string $key キー
	 */
	protected function readAuthUser($key = null)
	{
		if(!$key)
			return $this->Auth->user();
		
		return $this->Auth->user($key);
	}

	/**
	 * ログイン確認
	 * @return bool true : ログイン済み, false : ログインしていない
	 */
	protected function isLogined()
	{
		$val =  $this->Auth->user();
		
		return  ($val != null);
	}

	/**
	 * クエリストリングの取得
	 * @param string $key キー
	 * @param string $default キーが存在しない場合に返す値
	 */
	protected function getQuery($key, $default = '')
	{
		if(!isset($this->request->query[$key]))
			return $default;
		
		$val = $this->request->query[$key];
		
		if($val == null)
			return $default;
		
		return $val;
	}

	/**
	 * クエリストリングの存在確認
	 * @param string $key キー
	 */
	protected function hasQuery($key)
	{
		return isset($this->request->query[$key]);
	}

	/**
	 * ルート要素とリクエストパラメータを取得
	 * @param string $key キー
	 * @param string $default キーが存在しない場合に返す値
	 */
	protected function getParam($key, $default = '')
	{
		if(!isset($this->request->params[$key]))
			return $default;
		
		$val = $this->request->params[$key];
		
		if($val == null)
			return $default;
		
		return $val;
	}

	/**
	 * POSTデータの取得
	 * @param string $key キー
	 * @param string $default キーが存在しない場合に返す値
	 */
	protected function getData($key = null, $default = null)
	{
		$val = $this->request->data;
		
		if(!$val)
			return $default;
		
		if($key)
			$val = empty($val[$key]) ? $default :$val[$key];
		
		return $val;
	}

	/**
	 * POSTデータの上書き
	 * @param string $key キー
	 * @param string $value 値
	 */
	protected function setData($key, $value)
	{
		if($key)
		{
			$this->request->data[$key] = $value;
		}
		else
		{
			$this->request->data = $value;
		}
	}

	/**
	 * 管理画面へのアクセスかを確認
	 * @return bool true : 管理画面, false : 受講者画面
	 */
	protected function isAdminPage()
	{
		return (isset($this->request->params['admin']));
	}

	/**
	 * 編集画面へのアクセスかを確認
	 */
	protected function isEditPage()
	{
		return (($this->action == 'edit') || ($this->action == 'admin_edit'));
	}

	/**
	 * 学習履歴画面へのアクセスかを確認
	 */
	protected function isRecordPage()
	{
		return (($this->action == 'record') || ($this->action == 'admin_record'));
	}

	/**
	 * ログイン画面へのアクセスかを確認
	 */
	protected function isLoginPage()
	{
		return (($this->action == 'login') || ($this->action == 'admin_login'));
	}

	/**
	 * 一覧ページかどうかを確認
	 */
	protected function isIndexPage()
	{
		return (($this->action == 'index') || ($this->action == 'admin_index'));
	}

	/**
	 * 管理者かどうかを確認
	 */
	protected function isAdminRole()
	{
		return ($this->readAuthUser('role') == 'admin');
	}

	protected function addCondition($where, $key, $field)
	{
		$val = $this->getQuery($key);
		
		if(!$val)
			return $where;
		
		if(strpos(strtolower($field), 'like') > 0)
		{
			$where[$field] = '%'.$val.'%';
		}
		else
		{
			$where[$field] = $val;
		}
		
		return $where;
	}

	/**
	 * ログを記録
	 */
	protected function writeLog($log_type, $log_content, $controller = '', $action = '', $params = '', $sec = 0)
	{
		$data = [
			'log_type'		=> $log_type,
			'log_content'	=> $log_content,
			'controller'	=> $controller,
			'action'		=> $action,
			'params'		=> $params,
			'sec'			=> $sec,
			'user_id'		=> $this->readAuthUser('id'),
			'user_ip'		=> $_SERVER['REMOTE_ADDR'],
			'user_agent'	=> $_SERVER['HTTP_USER_AGENT']
		];
		
		$this->loadModel('Log');
		$this->Log->create();
		$this->Log->save($data);
	}

	/**
	 * 日本語と英語の対応表をロード
	 */
	private function loadTranslateMessages($lang = 'en')
	{
		$this->replaceConfigure('progress_type');
		$this->replaceConfigure('content_type');
		$this->replaceConfigure('record_type');
		$this->replaceConfigure('task_priority');
		$this->replaceConfigure('task_status');
		
		$file_path = APP.'Config'.DS.'message-'.$lang.'.csv';
		
		if(!file_exists($file_path))
			return;
		
		$csv = file($file_path);
		$csv_body = array_splice($csv, 0);
		
		$messages = [];
		
		foreach ($csv_body as $row)
		{
			$row_array = explode(',', $row);
			$messages[] = explode("\t", $row);
		}
		
		Configure::write('messages', $messages);
		//debug(Configure::read('messages'));
	}

	/**
	 * 既存の設定を上書き
	 */
	private function replaceConfigure($key, $lang = 'en')
	{
		Configure::delete($key);
		Configure::write($key, Configure::read($key.'_'.$lang));
	}
}
