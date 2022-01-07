<?php
/**
 * iroha Compass Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2021 iroha Soft, Inc. (https://irohasoft.jp)
 * @link          https://irohacompass.irohasoft.jp
 * @license       https://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */

App::uses('AppController', 'Controller');

/**
 * Users Controller
 * https://book.cakephp.org/2/ja/controllers.html
 */
class UsersController extends AppController
{
	/**
	 * 使用するコンポーネント
	 * https://book.cakephp.org/2/ja/core-libraries/toc-components.html
	 */
	public $components = [
		'Paginator',
		'Security' => [
			'csrfUseOnce' => false,
			'unlockedActions' => ['login', 'admin_login'],
			'unlockedFields' => ['cmd', 'csvfile.full_path'],
		],
		'Search.Prg',
		'Auth' => [
			'allowedActions' => [
				'index',
				'login',
				'logout'
			]
		]
	];

	/**
	 * ホーム画面（受講コース一覧）へリダイレクト
	 */
	public function index()
	{
		$this->redirect("/users_themes");
	}

	/**
	 * ログイン
	 */
	public function login()
	{
		$username = '';
		$password = '';
		
		// 自動ログイン処理
		// Check cookie's login info.
		if($this->hasCookie('Auth'))
		{
			// クッキー上のアカウントでログイン
			$this->request->data = $this->readCookie('Auth');
			
			if($this->Auth->login())
			{
				// 最終ログイン日時を保存
				$this->User->id = $this->readAuthUser('id');
				$this->User->saveField('last_logined', date(date('Y-m-d H:i:s')));
				$this->writeCookie('LoginStatus', 'logined');
				return $this->redirect( $this->Auth->redirect());
			}
			else
			{
				// ログインに失敗した場合、クッキーを削除
				$this->deleteCookie('Auth');
			}
		}
		
		// 通常ログイン処理
		if($this->request->is('post'))
		{
			if($this->Auth->login())
			{
				if(isset($this->data['User']['remember_me']))
				{
					// Remove remember_me data.
					unset( $this->request->data['User']['remember_me']);
					
					// Save login info to cookie.
					$cookie = $this->request->data;
					$this->writeCookie('Auth', $cookie, true, '+2 weeks');
				}
				
				// 最終ログイン日時を保存
				$this->User->id = $this->readAuthUser('id');
				$this->User->saveField('last_logined', date(date('Y-m-d H:i:s')));
				$this->writeLog('user_logined', '');
				$this->writeCookie('LoginStatus', 'logined');
				$this->deleteSession('Auth.redirect');
				$this->redirect($this->Auth->redirect());
			}
			else
			{
				$this->writeLog('login_error', $this->request->data['User']['username']);
				$this->Flash->error(__('ログインID、もしくはパスワードが正しくありません'));
			}
		}
		else
		{
			// デモモードの場合、ログインID、パスワードの初期値を指定
			if(Configure::read('demo_mode'))
			{
				$username = Configure::read('demo_login_id');
				$password = Configure::read('demo_password');
			}
		}
		
		$this->set(compact('username', 'password'));
	}

	/**
	 * bcrypt パスワード対応ログイン（将来使用予定）
	 */
	private function _login()
	{
		// POSTデータにログインIDが含まれていない場合、認証失敗とする
		if(!isset($this->request->data['User']['username']))
			return false;
		
		$username = $this->request->data['User']['username'];
		$user = $this->User->findByUsername($username);
		
		// 指定したユーザが存在しない場合、認証失敗とする
		if(!$user)
			return false;
		
		$hash = $user['User']['password'];
		
		// 先頭文字列で bcrypt によるハッシュ値かどうか判定
		if(substr($hash, 0, 1) == '$')
		{
			// bcrypt パスワードの認証
			$password = $this->request->data['User']['password'];
			
			if(password_verify($password, $hash))
			{
				return $this->Auth->login($user['User']);
			}
		}
		else
		{
			// 通常(SHA-1)パスワードの認証
			return $this->Auth->login();
		}
		
		return false;
	}

	/**
	 * ログアウト
	 */
	public function logout()
	{
		$this->deleteCookie('Auth');
		$this->deleteCookie('LoginStatus');
		$this->redirect($this->Auth->logout());
	}

	/**
	 * ユーザ一覧を表示
	 */
	public function admin_index()
	{
		// SearchPluginの呼び出し
		$this->Prg->commonProcess();
		
		// Model の filterArgs に定義した内容にしたがって検索条件を作成
		$conditions = $this->User->parseCriteria($this->Prg->parsedParams());
		
		// 選択中のグループをセッションから取得
		if($this->hasQuery('group_id'))
			$this->writeSession('Iroha.group_id', intval($this->getQuery('group_id')));
		
		// GETパラメータから検索条件を抽出
		$group_id = ($this->hasQuery('group_id')) ? $this->getQuery('group_id') : $this->readSession('Iroha.group_id');
		
		// 独自の検索条件を追加（指定したグループに所属するユーザを検索）
		if($group_id != '')
			$conditions['User.id'] = $this->Group->getUserIdByGroupID($group_id);
		
		$this->paginate = [
				'fields' => ['*',
					// 所属グループ一覧 ※パフォーマンス改善
					'(SELECT group_concat(g.title order by g.id SEPARATOR \', \') as group_title  FROM ib_users_groups  ug INNER JOIN ib_groups  g ON g.id = ug.group_id  WHERE ug.user_id = User.id) as group_title',
					// 受講コース一覧   ※パフォーマンス改善
					'(SELECT group_concat(c.title order by c.id SEPARATOR \', \') as theme_title FROM ib_users_themes uc INNER JOIN ib_themes c ON c.id = uc.theme_id WHERE uc.user_id = User.id) as theme_title',
				],
				'conditions' => $conditions,
				'limit' => 20,
				'order' => 'created desc',
		];

		// ユーザ一覧を取得
		try
		{
			$users = $this->paginate();
		}
		catch(Exception $e)
		{
			// 指定したページが存在しなかった場合（主に検索条件変更時に発生）、1ページ目を設定
			$this->request->params['named']['page'] = 1;
			$users = $this->paginate();
		}

		// グループ一覧を取得
		$groups = $this->Group->find('list');

		$this->set(compact('groups', 'users', 'group_id'));
	}

	/**
	 * ユーザを追加（編集画面へ）
	 */
	public function admin_add()
	{
		$this->admin_edit();
		$this->render('admin_edit');
	}

	/**
	 * ユーザ情報編集
	 * @param int $user_id 編集対象のユーザのID
	 */
	public function admin_edit($user_id = null)
	{
		if($this->isEditPage() && !$this->User->exists($user_id))
		{
			throw new NotFoundException(__('Invalid user'));
		}
		
		$username = '';
		
		if($this->request->is(['post', 'put']))
		{
			if(Configure::read('demo_mode'))
				return;
			
			if($this->request->data['User']['new_password'] !== '')
				$this->request->data['User']['password'] = $this->request->data['User']['new_password'];

			if($this->User->save($this->request->data))
			{
				$this->Flash->success(__('ユーザ情報が保存されました'));
				unset($this->request->data['User']['new_password']);
				return $this->redirect(['action' => 'index']);
			}
			else
			{
				$this->Flash->error(__('ユーザ情報が保存できませんでした'));
			}
		}
		else
		{
			$this->request->data = $this->User->get($user_id);
			
			if($this->request->data)
				$username = $this->request->data['User']['username'];
		}

		$themes = $this->User->Theme->find('list');
		$groups = $this->User->Group->find('list');
		
		$this->set(compact('themes', 'groups', 'username'));
	}

	/**
	 * ユーザの削除
	 *
	 * @param int $user_id 削除するユーザのID
	 */
	public function admin_delete($user_id = null)
	{
		if(Configure::read('demo_mode'))
			return;
		
		$this->User->id = $user_id;
		
		if(!$this->User->exists())
		{
			throw new NotFoundException(__('Invalid user'));
		}
		
		$this->request->allowMethod('post', 'delete');
		
		if($this->User->delete())
		{
			$this->Flash->success(__('ユーザが削除されました'));
		}
		else
		{
			$this->Flash->error(__('ユーザを削除できませんでした'));
		}
		
		return $this->redirect(['action' => 'index']);
	}

	/**
	 * ユーザの学習履歴のクリア
	 *
	 * @param int $user_id 学習履歴をクリアするユーザのID
	 */
	public function admin_clear($user_id)
	{
		$this->request->allowMethod('post', 'delete');
		$this->User->deleteUserRecords($user_id);
		$this->Flash->success(__('学習履歴を削除しました'));
		return $this->redirect(['action' => 'edit', $user_id]);
	}

	/**
	 * パスワード変更
	 */
	public function setting()
	{
		if($this->request->is(['post', 'put']))
		{
			if(Configure::read('demo_mode'))
				return;
			
			$this->request->data['User']['id'] = $this->readAuthUser('id');
			
			if($this->request->data['User']['new_password'] != $this->request->data['User']['new_password2'])
			{
				$this->Flash->error(__('入力された「パスワード」と「パスワード（確認用）」が一致しません'));
				return;
			}

			if($this->request->data['User']['new_password'] !== '')
			{
				$this->request->data['User']['password'] = $this->request->data['User']['new_password'];
				
				if($this->User->save($this->request->data))
				{
					$this->Flash->success(__('パスワードが保存されました'));
				}
				else
				{
					$this->Flash->error(__('パスワードが保存できませんでした'));
				}
			}
			else
			{
				$this->Flash->error(__('パスワードを入力して下さい'));
			}
		}
		else
		{
			$this->request->data = $this->User->get($this->readAuthUser('id'));
		}
	}

	/**
	 * パスワード変更
	 */
	public function admin_setting()
	{
		$this->setting();
	}

	/**
	 * ログイン
	 */
	public function admin_login()
	{
		$this->login();
	}

	/**
	 * ログアウト
	 */
	public function admin_logout()
	{
		$this->logout();
	}
}
