<?php
/**
 * iroha Compass Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2021 iroha Soft, Inc. (https://irohasoft.jp)
 * @link          https://irohacompass.irohasoft.jp
 * @license       https://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */

App::uses('AppController',		'Controller');
App::uses('RecordsQuestion',	'RecordsQuestion');
App::uses('UsersGroup',			'UsersGroup');
App::uses('Theme', 'Theme');
App::uses('User',   'User');
App::uses('Group',  'Group');

/**
 * Records Controller
 *
 * @property Record $Record
 * @property PaginatorComponent $Paginator
 */
class RecordsController extends AppController
{

	public $components = [
			'Paginator',
			'Search.Prg'
	];

	//public $presetVars = true;

	public $paginate = [];
	
	public $presetVars = [
		[
			'name' => 'name', 
			'type' => 'value',
			'field' => 'User.name'
		], 
		[
			'name' => 'username',
			'type' => 'like',
			'field' => 'User.username'
		], 
		[
			'name' => 'contenttitle', 'type' => 'like',
			'field' => 'Task.title'
		]
	];
	
	// 検索対象のフィルタ設定
	/*
	 * public $filterArgs = array( array('name' => 'name', 'type' => 'value',
	 * 'field' => 'User.name'), array('name' => 'username', 'type' => 'like',
	 * 'field' => 'User.username'), array('name' => 'title', 'type' => 'like',
	 * 'field' => 'Task.title') );
	 */
	public function progress($user_id = null)
	{
		$is_popup	= true;
		
		// 検索条件設定
		$this->Prg->commonProcess();
		
		// アクセス可能な学習テーマ一覧を取得
		$this->loadModel('UsersTheme');
		$themes = $this->UsersTheme->getThemeRecord( $this->readAuthUser('id') );
		$theme_ids = [];
		
		foreach ($themes as $theme)
		{
			array_push($theme_ids, $theme['Theme']['id']);
		}
		
		$conditions = $this->Record->parseCriteria($this->Prg->parsedParams());
		
		$theme_id		= $this->getQuery('theme_id');
		$contenttitle	= $this->getQuery('contenttitle');
		$from_date		= $this->getQuery('from_date');
		$to_date		= $this->getQuery('to_date');
		
		$conditions['Theme.id'] = ($theme_id != '') ? $theme_id : $theme_ids;
		
		if(!$from_date)
			$from_date = [
				'year' => date('Y', strtotime("-1 month")),
				'month' => date('m', strtotime("-1 month")), 
				'day' => date('d', strtotime("-1 month"))
			];
		
		if(!$to_date)
			$to_date = ['year' => date('Y'), 'month' => date('m'), 'day' => date('d')];
		
		if(Configure::read('demo_mode'))
			$from_date = explode("/", Configure::read('demo_target_date'));
		
		// 学習日付による絞り込み
		$conditions['Record.created BETWEEN ? AND ?'] = [
			implode("/", $from_date), 
			implode("/", $to_date).' 23:59:59'
		];
		
		if($contenttitle != '')
			$conditions['Task.title like'] = '%'.$contenttitle.'%';
		
		$this->Paginator->settings['conditions'] = $conditions;
		$this->Paginator->settings['order']      = 'Record.created desc';
		$this->Record->recursive = 0;
		
		try
		{
			$result = $this->paginate();
		}
		catch (Exception $e)
		{
			$this->request->params['named']['page'] = 1;
			$result = $this->paginate();
		}
		
		$this->set('records', $result);
		
		$this->Theme = new Theme();
		$this->User = new User();
		
		// ユーザIDが指定されていない、もしくは管理者以外の場合、自身の進捗データを取得
		if(
			(!$user_id)||
			($this->readAuthUser('role')!='admin')
		)
		{
			$is_popup = false;
			$user_id = $this->readAuthUser('id');
		}
		
		
		// 進捗チャート用の情報を取得
		$labels			= $this->Record->getDateLabels();
		$login_data		= $this->Record->getLoginData($user_id, $labels);
		$progress_data	= $this->Record->getProgressData($user_id, $labels);
		$themes			= $this->Theme->find('list', ['conditions' => ['Theme.id' => $theme_ids]]);
		
		$this->set(compact('labels', 'login_data', 'progress_data', 'themes', 'theme_id', 'contenttitle', 'from_date', 'to_date', 'is_popup'));
	}
	
	public function admin_progress($id)
	{
		$this->progress($id);
		$this->render('progress');
	}
	
	// 検索対象のフィルタ設定
	/*
	 * public $filterArgs = array( array('name' => 'name', 'type' => 'value',
	 * 'field' => 'User.name'), array('name' => 'username', 'type' => 'like',
	 * 'field' => 'User.username'), array('name' => 'title', 'type' => 'like',
	 * 'field' => 'Task.title') );
	 */
	public function admin_index()
	{
		// 検索条件設定
		$this->Prg->commonProcess();
		
		$conditions = [];
		
		$group_id	= $this->getQuery('group_id');
		$theme_id	= $this->getQuery('theme_id');
		$user_id	= $this->getQuery('user_id');
		$task_title	= $this->getQuery('task_title');
		$from_date	= $this->getQuery('from_date');
		$to_date	= $this->getQuery('to_date');
		
		if($group_id != '')
			$conditions['User.id'] = $this->Group->getUserIdByGroupID($group_id);
		
		if($theme_id != '')
			$conditions['Theme.id'] = $theme_id;
		
		if($user_id != '')
			$conditions['User.id'] = $user_id;
		
		if($task_title != '')
			$conditions['Task.title like'] = '%'.$task_title.'%';
		
		if(!$from_date)
			$from_date = [
				'year' => date('Y', strtotime("-1 month")),
				'month' => date('m', strtotime("-1 month")), 
				'day' => date('d', strtotime("-1 month"))
			];
		
		if(!$to_date)
			$to_date = ['year' => date('Y'), 'month' => date('m'), 'day' => date('d')];
		
		// 学習日付による絞り込み
		$conditions['Record.created BETWEEN ? AND ?'] = [
			implode("/", $from_date), 
			implode("/", $to_date).' 23:59:59'
		];
		
		// CSV出力モードの場合
		if($this->getQuery('cmd')=='csv')
		{
			$this->autoRender = false;

			//Task-Typeを指定
			$this->response->type('csv');

			header('Task-Type: text/csv');
			header('Task-Disposition: attachment; filename="user_records.csv"');
			
			$fp = fopen('php://output','w');
			
			$rows = $this->Record->find()
				->where($conditions)
				->all();
			
			$header = ["学習テーマ", "コンテンツ", "氏名", "得点", "合格点", "結果", "理解度", "学習時間", "学習日時"];
			
			mb_convert_variables("SJIS-WIN", "UTF-8", $header);
			fputcsv($fp, $header);
			
			foreach($rows as $row)
			{
				$row = [
					$row['Theme']['title'], 
					$row['Task']['title'], 
					$row['User']['name'], 
					$row['Record']['rate'], 
					$row['Record']['theme_rate'], 
					Utils::getHNSBySec($row['Record']['study_sec']), 
					Utils::getYMDHN($row['Record']['created']),
				];
				
				mb_convert_variables("SJIS-WIN", "UTF-8", $row);
				
				fputcsv($fp, $row);
			}
			
			fclose($fp);
		}
		else
		{
			$this->Paginator->settings['conditions'] = $conditions;
			$this->Paginator->settings['order']      = 'Record.created desc';
			$this->Record->recursive = 0;
			
			try
			{
				$result = $this->paginate();
			}
			catch (Exception $e)
			{
				$this->request->params['named']['page'] = 1;
				$result = $this->paginate();
			}
			
			$this->set('records', $result);
			
			$groups = $this->Record->User->Group->find('list');
			$themes = $this->Record->Theme->find('list');
			$users  = $this->Record->User->find('list');
			
			$this->set(compact('groups', 'themes', 'users', 'group_id', 'theme_id', 'user_id', 'task_title', 'from_date', 'to_date'));
		}
	}

	public function add($task_id, $is_complete, $study_sec, $kind)
	{
		// コンテンツ情報を取得
		$this->loadModel('Task');
		$content = $this->Task->get($task_id);
		
		$this->Record->create();
		$data = [
			'user_id'		=> $this->readAuthUser('id'),
			'theme_id'		=> $content['Theme']['id'],
			'task_id'		=> $task_id,
			'study_sec' 	=> $study_sec,
			'is_complete'	=> $is_complete
		];
		
		if($this->Record->save($data))
		{
			$this->Flash->success(__('学習履歴を保存しました'));
			return $this->redirect(['controller' => 'tasks', 'action' => 'index', $content['Theme']['id']]);
		}
		else
		{
			$this->Flash->error(__('The record could not be saved. Please, try again.'));
		}
	}

	public function record($task_id, $record, $details)
	{
		// コンテンツ情報を取得
		$this->loadModel('Task');
		$content = $this->Task->get($task_id);
		
		$this->Record->create();
		
		$data = [
			'user_id'		=> $this->readAuthUser('id'),
			'theme_id'		=> $content['Theme']['id'],
			'task_id'		=> $task_id,
			'theme_rate'	=> $record['theme_rate'],
			'rate'			=> $record['rate'],
			'emotion_icon'	=> $record['emotion_icon'],
			'is_passed'		=> $record['is_passed'],
			'study_sec'		=> $record['study_sec'],
			'is_complete'	=> 1
		];
		
		if($this->Record->save($data))
		{
			$this->RecordsQuestion = new RecordsQuestion();
			
			foreach ($details as $detail)
			{
				$this->RecordsQuestion->create();
				$detail['record_id'] = $this->Record->getLastInsertID();
				$this->RecordsQuestion->save($detail);
			}
		}
	}

	public function edit($id = null)
	{
		if(!$this->Record->exists($id))
		{
			throw new NotFoundException(__('Invalid record'));
		}
		
		if($this->request->is(['post', 'put']))
		{
			if($this->Record->save($this->request->data))
			{
				$this->Flash->success(__('The record has been saved.'));
				return $this->redirect(['action' => 'index']);
			}
			else
			{
				$this->Flash->error(__('The record could not be saved. Please, try again.'));
			}
		}
		else
		{
			$this->request->data = $this->Record->get($id);
		}
		
		$groups = $this->Record->Group->find('list');
		$themes = $this->Record->Theme->find('list');
		$users = $this->Record->User->find('list');
		$tasks = $this->Record->Task->find('list');
		$this->set(compact('groups', 'themes', 'users', 'tasks'));
	}

	public function admin_delete($id = null)
	{
		$this->Record->id = $id;
		
		if(!$this->Record->exists())
		{
			throw new NotFoundException(__('Invalid record'));
		}
		
		$this->request->allowMethod('post', 'delete');
		
		if($this->Record->delete())
		{
			$this->Flash->success(__('The record has been deleted.'));
		}
		else
		{
			$this->Flash->error(__('The record could not be deleted. Please, try again.'));
		}
		
		return $this->redirect(['action' => 'index']);
	}
}
