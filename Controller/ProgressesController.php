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
App::uses('Record', 'Record');

/**
 * Progresss Controller
 *
 * @property Progress $Progress
 * @property PaginatorComponent $Paginator
 */
class ProgressesController extends AppController
{

	public $components = [
		'Paginator',
		'Security' => [
			'validatePost' => false,
			'csrfUseOnce' => false,
			'csrfCheck' => false,
			'unlockedActions' => ['admin_order']
		],
	];

	public function index($task_id, $progress_id = null)
	{
		//$this->Progress->recursive = 0;
		$this->Paginator->settings = [
			'limit' => 500,
			'maxLimit' => 500,
			'order' => 'Progress.created asc',
			'conditions' => [
				'task_id' => $task_id
			],
		];
		
		//debug($this->request->params);
		
		$this->loadModel('Theme');
		
		$progresses = $this->paginate();
		
		// 管理者以外の場合、コンテンツの閲覧権限の確認
		if($this->readAuthUser('role') != 'admin')
		{
			
			if(count($progresses) > 0)
			{
				if(!$this->Theme->hasRight($this->readAuthUser('id'), $progresses[0]['Task']['theme_id']))
				{
					throw new NotFoundException(__('Invalid access'));
				}
			}
		}
		
		// コンテンツ情報を取得
		$this->loadModel('Task');
		$content = $this->Task->get($task_id);
		
		$is_record = $this->isRecordPage();
		$is_admin  = $this->isAdminPage();
		$is_user   = $this->isIndexPage() && !$this->isAdminPage();
		
		$this->loadModel('User');
		
		for($i=0; $i < count($progresses); $i++)
		{
			$smiled_ids   = []; // スマイルした人のID（自分以外）
			
			$smiles = $progresses[$i]['Smile'];
			
			$is_smiled = false;
			
			for($j=0; $j < count($smiles); $j++)
			{
				// 自分自身がスマイルしたかどうか
				if($smiles[$j]['user_id']==$this->readAuthUser('id'))
				{
					$is_smiled = true;
				}
				else
				{
					array_push($smiled_ids, $smiles[$j]['user_id']);
				}
			}
			
			$this->User->recursive = 0;
			$user = $this->User->find()
				->select(["GROUP_CONCAT(User.name SEPARATOR ', ') as name_display"])
				->where(['User.id'	=> $smiled_ids])
				->group(['User.id'])
				->first();
			
			// スマイルした名前を表示
			$progresses[$i]['name_display']	= isset($user[0]['name_display']) ? $user[0]['name_display'] : null;
			// 自分自身がスマイルしたかどうか
			$progresses[$i]['is_smiled']	= $is_smiled;
		}
		
		$is_add  = ($progress_id==null);
		
		if($this->request->is(['post', 'put']))
		{
			if(Configure::read('demo_mode'))
				return;
			
			if($is_add)
			{
				$this->request->data['Progress']['user_id'] = $this->readAuthUser('id');
				$this->request->data['Progress']['task_id'] = $task_id;
			}
			
			if(!$this->Progress->validates())
				return;
			
			if($this->Progress->save($this->request->data))
			{
				$this->__save_record($task_id, $progress_id);
				$this->Flash->success(__('進捗を保存しました'));
				return $this->redirect(['controller' => 'progresses', 'action' => 'index', $task_id]);
			}
			else
			{
				$this->Flash->error(__('The tasks progress could not be saved. Please, try again.'));
			}
		}
		else
		{
			$this->request->data = $this->Progress->get($progress_id);
		}
		
		
		// 学習テーマに紐づくユーザを取得
		$this->loadModel('UsersTheme');
		//$mail_list = $this->UsersTheme->getMailList($content['Theme']['id']);
		//debug($list);
		
		// メール通知用
		$users = $this->User->find('list');
		
		$this->set(compact('content', 'progresses', 'mail_list', 'is_record', 'is_admin', 'is_add', 'is_user', 'users'));
	}

	/**
	 * 進捗の移動
	 */
	public function move($progress_id)
	{
		if(!$this->Progress->exists($progress_id))
		{
			throw new NotFoundException(__('Invalid progress'));
		}
		
		if($this->request->is(['post', 'put']))
		{
			if(Configure::read('demo_mode'))
				return;
			
			if($this->Progress->save($this->request->data))
			{
				$this->Flash->success(__('進捗を移動しました'));
				return $this->redirect(['action' => 'index', $this->request->data['Progress']['task_id']]);
			}
			else
			{
				$this->Flash->error(__('The progress could not be saved. Please, try again.'));
			}
		}
		else
		{
			$this->request->data = $this->Progress->get($progress_id);
		}
		
		$theme_id = $this->request->data['Task']['theme_id'];
		$task_id = $this->request->data['Task']['id'];
		
		$this->loadModel('Task');
		$tasks = $this->Task->find()->where(['theme_id' => $theme_id])->toList();

		$this->set(compact('task_id', 'tasks'));
	}

	private function __save_record($task_id, $progress_id)
	{
		$is_add			= ($progress_id==null);
		$progress_type	= $this->request->data['Progress']['progress_type'];
		$record_type	= $is_add ? $progress_type : $progress_type.'_update';
		$task_status	= $this->request->data['Progress']['status'];
		$emotion_icon	= @$this->request->data['Progress']['emotion_icon'];
		
		// 課題の進捗率を更新（種別が進捗の場合のみ）
		if($progress_type=='progress')
		{
			$this->loadModel('Task');
			$this->Task->updateRate($task_id);
		}
		
		// 課題のステータスを更新
		$this->Task->id = $task_id;
		$this->Task->saveField('status', $task_status);
		
		// 課題情報を取得
		$task = $this->Task->get($task_id);
		
		// 学習履歴を追加
		$this->loadModel('Record');
		
		$this->Record->addRecord([
			'user_id'		=> $this->readAuthUser('id'),
			'theme_id'		=> $task['Theme']['id'],
			'task_id'		=> $task_id,
			'study_sec'		=> $this->request->data['study_sec'],
			'emotion_icon'	=> $emotion_icon,
			'record_type'	=> $record_type,
		]);
		
		// 課題の更新日時を更新
		$this->Task->id = $task_id;
		$this->Task->saveField('modified', date(date('Y-m-d H:i:s')));
		
		// 学習テーマの更新日時を更新
		$this->Task->Theme->id = $task['Theme']['id'];
		$this->Task->Theme->saveField('modified', date(date('Y-m-d H:i:s')));
		
		

		// メール通知がオンの場合
		if(@$this->request->data['is_mail']=='on')
		{
			$this->loadModel('UsersTheme');
			
			// 学習テーマに紐づくユーザのメールアドレスを取得
			//$list = $this->UsersTheme->getMailList($task['Theme']['id']);
			
			// メール通知リスト
			$users = $this->User->find()
				->where(['User.id' => $this->request->data['Progress']['User']])
				->all();
			
			$admin_from	= Configure :: read('admin_from');
			$mail_title	= Configure :: read('mail_title');
			
			foreach ($users as $user)
			{
				if(strlen($user['User']['email']) < 6)
					continue;
				
				// 管理者か学習者かによってURLを変更
				$url = Router::url(['controller' => 'progresses', 'action' => 'index', $task_id, 'admin' => ($user['User']['role']=='admin')], true);
				
				$params = [
					'name'			=> $user['User']['name'],
					'theme_title'	=> $task['Theme']['title'],
					'content_title'	=> $task['Task']['title'],
					'record_type'	=> Configure::read('record_type.'.$record_type),
					'url'			=> $url,
					'updater'		=> $this->readAuthUser('name'),
				];
				
				// メールの送信
				$mail = new CakeEmail();
				$mail->from($admin_from);
				$mail->to($user['User']['email']);
				$mail->subject($mail_title);
				$mail->template('update');
				$mail->viewVars($params);
				$mail->send();
				
				$this->writeLog('mail_sent', $user['User']['email'], 'progresses', 'index', $task_id);
			}
		}
	}

	public function index_enq($task_id, $record_id = null)
	{
		$this->Progress->recursive = 0;
		$progresses = $this->Progress->find()
				->where(['task_id' => $task_id])
				->order(['Progress.sort_no' => 'asc'])
				->all();
		
		// 管理者以外の場合、コンテンツの閲覧権限の確認
		if(
			($this->readAuthUser('role') != 'admin')&&
			($this->readAuthUser('role') != 'manager')
		)
		{
			$this->loadModel('Theme');
			
			if(count($progresses) > 0)
			{
				if(!$this->Theme->hasRight($this->readAuthUser('id'), $progresses[0]['Task']['theme_id']))
				{
					throw new NotFoundException(__('Invalid access'));
				}
			}
		}
		
		// レコードIDが指定されている場合、成績を取得
		if($record_id)
		{
			$this->loadModel('Record');
			$record = $this->Record->get($record_id);
			
			$this->set('mode',   "record");
			$this->set('record', $record);
		}
		
		// コンテンツ情報を取得
		$this->loadModel('Task');
		$content = $this->Task->get($task_id);
		
		// 採点処理
		if($this->request->is('post'))
		{
			$details = [];
			
			// 成績の詳細情報の作成
			$i = 0;
			foreach ($progresses as $progress)
			{
				$progress_id = $progress['Progress']['id'];
				$answer = @$this->request->data['answer_' . $progress_id];
				
				$details[$i] = [
					'progress_id' => $progress_id,
					'answer' => $answer,
				];
				$i ++;
			}
			
			$record = [
				'study_sec' => $this->request->data['Progress']['study_sec']
			];
			
			$this->loadModel('Record');
			$this->Record->create();
			
			//debug($this->Record);
			$data = [
				'user_id'		=> $this->readAuthUser('id'),
				'theme_id'		=> 0,
				'task_id'	=> $task_id,
				'study_sec'		=> $record['study_sec'],
				'is_complete'	=> 1
			];
			
			//debug($data);
			if($this->Record->save($data))
			{
				$this->loadModel('RecordsQuestion');
				$record_id = $this->Record->getLastInsertID();
				
				foreach ($details as $detail)
				{
					$this->RecordsQuestion->create();
					$detail['record_id'] = $record_id;
					$this->RecordsQuestion->save($detail);
				}
				
				$this->Flash->success(__('アンケートの回答内容を送信しました'));
				$this->redirect(['action' => 'record_enq', $task_id, $this->Record->getLastInsertID()]);
			}
			else
			{
				$this->Flash->error(__('The tasks progress could not be saved. Please, try again.'));
			}
		}
		
		$is_record = (($this->action == 'record_enq') || ($this->action == 'admin_record_enq') || ($this->action == 'admin_record_enq_each'));
		$is_admin  = (($this->action == 'admin_record_enq') || ($this->action == 'admin_record_enq_each'));
		//debug($is_record);
		
		$this->set(compact('content', 'progresses', 'is_record', 'is_admin'));
	}

	public function record($id, $record_id)
	{
		$this->index($id, $record_id);
		$this->render('index');
	}

	public function record_enq($id, $record_id)
	{
		$this->index_enq($id, $record_id);
		$this->render('index_enq');
	}

	public function admin_record($id, $record_id)
	{
		$this->index($id, $record_id);
		$this->render('index');
	}

	public function admin_record_enq($id, $record_id)
	{
		$this->index_enq($id, $record_id);
		$this->render('index_enq');
	}

	public function admin_index($task_id, $progress_id = null)
	{
		$this->index($task_id, $progress_id);
		$this->render('index');
	}

	public function admin_index_enq($id)
	{
		$this->Progress->recursive = 0;
		$progresses = $this->Progress->find()
				->where(['task_id' => $id])
				->order(['Progress.sort_no' => 'asc'])
				->all();
		
		// コースの情報を取得
		$this->loadModel('Task');
		
		$content = $this->Task->get($id);
		
		$this->set(compact('content', 'progresses'));
	}

	public function admin_add_enq($task_id)
	{
		$this->admin_edit_enq($task_id);
		$this->render('admin_edit_enq');
	}

	public function admin_edit_enq($task_id, $id = null)
	{
		//$this->Progress->validator()->delete('option_list');
		
		//$this->Progress->validate['option_list'] = null;
		//$this->Progress->unbindValidation('remove', array('option_list'), true);
		unset($this->Progress->validate['option_list']['notBlank']);
		//debug($this->Progress->validator());
		/*
		$this->User->validator()->add('group_id', 'required', array(
			'rule' => 'notBlank',
			'required' => 'create'
		));
		*/
		if($this->action == 'edit_enq' && !$this->Post->exists($id))
		{
			throw new NotFoundException(__('Invalid tasks progress'));
		}
		
		if($this->request->is(['post', 'put']))
		{
			if($id == null)
			{
				$this->request->data['Progress']['user_id'] = $this->readAuthUser('id');
				$this->request->data['Progress']['task_id'] = $task_id;
			}
			
			if(!$this->Progress->validates())
				return;
			
			//debug($this->request->data);
			//exit;
			if($this->Progress->save($this->request->data))
			{
				$this->Flash->success(__('質問が保存されました'));
				return $this->redirect(['controller' => 'progresses', 'action' => 'index_enq', $task_id]);
			}
			else
			{
				$this->Flash->error(__('The tasks progress could not be saved. Please, try again.'));
			}
		}
		else
		{
			$this->request->data = $this->Progress->get($id);
		}
		
		// コンテンツ情報を取得
		$this->loadModel('Task');
		$content = $this->Task->get($task_id);
		
		$this->set(compact('content'));
	}

	/**
	 * delete method
	 *
	 * @throws NotFoundException
	 * @param string $id        	
	 * @return void
	 */
	public function admin_delete($id)
	{
		$this->delete($id);
	}
	
	public function delete($id)
	{
		$this->Progress->id = $id;
		if(!$this->Progress->exists())
		{
			throw new NotFoundException(__('Invalid tasks progress'));
		}
		
		$this->request->allowMethod('post', 'delete');
		
		// 進捗情報を取得
		$progress = $this->Progress->get($id);
		
		if($this->Progress->delete())
		{
			$this->Flash->success(__('進捗が削除されました'));
			return $this->redirect(['controller' => 'progresses', 'action' => 'index', $progress['Progress']['task_id']]);
		}
		else
		{
			$this->Flash->error(__('The tasks progress could not be deleted. Please, try again.'));
		}
	}

	public function admin_delete_enq($task_id, $id)
	{
		$this->Progress->id = $id;
		if(!$this->Progress->exists())
		{
			throw new NotFoundException(__('Invalid tasks progress'));
		}
		
		$this->request->allowMethod('post', 'delete');
		
		// 問題情報を取得
		$progress = $this->Progress->get($id);
		
		if($this->Progress->delete())
		{
			$this->Flash->success(__('質問が削除されました'));
			return $this->redirect(['controller' => 'progresses', 'action' => 'index_enq', $task_id]);
		}
		else
		{
			$this->Flash->error(__('The tasks progress could not be deleted. Please, try again.'));
		}
	}

	public function admin_order()
	{
		$this->autoRender = FALSE;
		if($this->request->is('ajax'))
		{
			$this->Progress->setOrder($this->data['id_list']);
			return "OK";
		}
	}

	public function admin_smile()
	{
		$this->smile();
	}

	public function smile()
	{
		$this->autoRender = FALSE;
		
		if($this->request->is('ajax'))
		{
			$this->loadModel('Smile');
			
			$conditions = [
				'progress_id'	=> $this->data['progress_id'],
				'user_id'		=> $this->readAuthUser('id'),
			];
			
			$smile = $this->Smile->find()
				->where($conditions)
				->first();
			
			if($smile)
			{
				$this->Smile->delete($smile['Smile']['id']);
			}
			else
			{
				$this->Smile->create();
				$this->Smile->save($data);
			}
			
			return "OK";
		}
	}
}
