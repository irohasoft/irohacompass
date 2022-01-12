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

/**
 * Records Controller
 * https://book.cakephp.org/2/ja/controllers.html
 */
class RecordsController extends AppController
{
	/**
	 * 使用するコンポーネント
	 * https://book.cakephp.org/2/ja/core-libraries/toc-components.html
	 */
	public $components = [
			'Paginator',
			'Search.Prg'
	];

	/**
	 * 進捗一覧を表示（学習者側）
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
	
	/**
	 * 進捗一覧を表示
	 */
	public function admin_index()
	{
		// SearchPluginの呼び出し
		$this->Prg->commonProcess();
		
		// モデルの filterArgs で定義した内容にしたがって検索条件を作成
		// ただし独自の検索条件は別途追加する必要がある
		$conditions = $this->Record->parseCriteria($this->Prg->parsedParams());
		
		$group_id	= $this->getQuery('group_id');
		$from_date	= $this->getQuery('from_date');
		$to_date	= $this->getQuery('to_date');
		
		if($group_id != '')
			$conditions['User.id'] = $this->Group->getUserIdByGroupID($group_id);
		
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
		if($this->getQuery('cmd') == 'csv')
		{
			$this->autoRender = false;

			// メモリサイズ、タイムアウト時間を設定
			ini_set('memory_limit', '512M');
			ini_set('max_execution_time', (60 * 10));

			// Content-Typeを指定
			$this->response->type('csv');

			header('Content-Type: text/csv');
			header('Content-Disposition: attachment; filename="user_progresses.csv"');
			
			$fp = fopen('php://output','w');
			
			$this->Record->recursive = 0;
			
			$rows = $this->Record->find()
				->where($conditions)
				->order('Record.created desc')
				->all();
			
			$header = [
				__('学習テーマ'),
				__('課題'),
				__('氏名'),
				__('進捗率'),
				__('進捗率(全体)'),
				__('種別'),
				__('滞留時間'),
				__('更新日時')
			];
			
			mb_convert_variables('SJIS-WIN', 'UTF-8', $header);
			fputcsv($fp, $header);
			
			foreach($rows as $row)
			{
				$row = [
					$row['Theme']['title'],
					$row['Task']['title'],
					$row['User']['name'],
					$row['Record']['rate'],
					$row['Record']['theme_rate'],
					Configure::read('record_type.'.$row['Record']['record_type']),
					Utils::getHNSBySec($row['Record']['study_sec']), 
					Utils::getYMDHN($row['Record']['created']),
				];
				
				mb_convert_variables('SJIS-WIN', 'UTF-8', $row);
				
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
				$records = $this->paginate();
			}
			catch(Exception $e)
			{
				$this->request->params['named']['page']=1;
				$records = $this->paginate();
			}
			
			$groups = $this->Record->User->Group->find('list');
			$themes = $this->Record->Theme->find('list');
			$users  = $this->Record->User->find('list');
			
			$this->set(compact('records', 'groups', 'themes', 'users', 'group_id', 'from_date', 'to_date'));
		}
	}

	/**
	 * 学習履歴を追加
	 * 
	 * @param int $content_id    課題ID
	 * @param int $is_complete   完了フラグ
	 * @param int $study_sec     学習時間
	 * @param int $kind 更新種別
	 */
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
}
