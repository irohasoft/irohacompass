<?php
/**
 * iroha Compass Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2021 iroha Soft, Inc. (https://irohasoft.jp)
 * @link          https://irohacompass.irohasoft.jp
 * @license       https://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */

App::uses('AppModel', 'Model');

/**
 * Record Model
 *
 * @property Theme $Theme
 * @property User $User
 * @property Task $Task
 */
class Record extends AppModel
{
	/**
	 * バリデーションルール
	 * https://book.cakephp.org/2/ja/models/data-validation.html
	 * @var array
	 */
	public $validate = [
		'theme_id'  => ['numeric' => ['rule' => ['numeric']]],
		'user_id'    => ['numeric' => ['rule' => ['numeric']]],
		'task_id' => ['numeric' => ['rule' => ['numeric']]]
	];

	/**
	 * アソシエーションの設定
	 * https://book.cakephp.org/2/ja/models/associations-linking-models-together.html
	 * @var array
	 */
	public $hasMany = [
	];

	public $belongsTo = [
		'Theme' => [
			'className' => 'Theme',
			'foreignKey' => 'theme_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'User' => [
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'Task' => [
			'className' => 'Task',
			'foreignKey' => 'task_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		]
	];

	/**
	 * 検索用
	 */
	public $actsAs = [
		'Search.Searchable'
	];

	/**
	 * 検索条件
	 * https://github.com/CakeDC/search/blob/master/Docs/Home.md
	 */
	public $filterArgs = [
		'theme_id' => [
			'type' => 'value',
			'field' => 'Record.theme_id'
		],
		'task_title' => [
			'type' => 'like',
			'field' => 'Task.title'
		],
		'user_id' => [
			'type' => 'value',
			'field' => 'Record.user_id'
		],
		'username' => [
			'type' => 'like',
			'field' => 'User.username'
		],
		'name' => [
			'type' => 'like',
			'field' => 'User.name'
		]
	];

	/**
	 * 学習テーマ、課題、進捗の追加、更新履歴を追加
	 *
	 * @param array record 更新履歴(ユーザID, テーマID, 課題ID, 進捗ID, 感情アイコン, 課題進捗率, 履歴種別)
	 */
	public function addRecord($record)
	{
		$status = null;
		$this->create();
		$theme_rate  = null;
		$task_rate = null;
		
		// 学習テーマの進捗率（全課題の平均）
		$sql = "SELECT AVG(rate) as theme_avg FROM ib_tasks WHERE user_id = :user_id AND theme_id = :theme_id";
		$params = [
			'user_id'	=> $record['user_id'],
			'theme_id'	=> $record['theme_id']
		];
		$data = $this->query($sql, $params);
		$theme_rate = $data[0][0]["theme_avg"];
		
		// 課題の進捗率、ステータス
		if($record['task_id'] > 0)
		{
			$sql = "SELECT ifnull(rate, 0) as task_rate, status FROM ib_tasks WHERE id = :task_id";
			$params = [
				'task_id' => $record['task_id']
			];
			$data = $this->query($sql, $params);
			//debug($data);
			
			$task_rate	= $data[0][0]["task_rate"];
			$status		= $data[0]['ib_tasks']["status"];
		}
		
		$data = [
			'user_id'		=> $record['user_id'],
			'theme_id'		=> $record['theme_id'],
			'task_id'		=> $record['task_id'],
			'study_sec'		=> $record['study_sec'],
			'rate'			=> $task_rate,
			'emotion_icon'	=> @$record['emotion_icon'],
			'theme_rate'	=> $theme_rate,
			'record_type'	=> $record['record_type'],
			'is_complete'	=> $status
		];
		
		//debug($data);
		
		return $this->save($data);
	}

	/**
	 * ログイン回数情報を取得
	 */
	public function getLoginData($user_id, $date_list)
	{
		$sql = <<<EOF
SELECT DATE(created) as created, COUNT(*) as cnt
  FROM ib_logs
 WHERE user_id = :user_id
   AND created > :start_date
#   AND log_type = 'user_logined'
 GROUP BY DATE(created)
 ORDER BY DATE(created)
EOF;
		// 集計開始日を指定（デモモードの場合、設定ファイルの日付を設定）
		$start_date = Configure::read('demo_mode') ? Configure::read('demo_target_date') : date('Y-m-d', strtotime('-13 day'));
		
		$params = [
			'user_id'		=> $user_id,
			'start_date'	=> $start_date,
		];
		
		$data = $this->query($sql, $params);
		$ret_data = [];
		
		//debug($data);
		
		for($i=0; $i<14; $i++ )
		{
			$index = count($ret_data);
			
			$ret_data[$index] = 0;
			
			foreach ($data as $item)
			{
				$target_date = Configure::read('demo_mode') ? date('Y-m-d', strtotime(($i).' day', strtotime(Configure::read('demo_target_date')))) : date('Y-m-d', strtotime(($i-13).' day'));
				
				if($item[0]['created'] == $target_date)
					$ret_data[$index] = $item[0]['cnt'];
			}
		}
		
		//debug($ret_data);
		return $ret_data;
	}

	/**
	 * 進捗更新回数情報を取得
	 */
	public function getProgressData($user_id, $date_list)
	{
		$sql = <<<EOF
SELECT DATE(created) as created, COUNT(*) as cnt
  FROM ib_records
 WHERE user_id = :user_id
   AND created > :start_date
 GROUP BY DATE(created)
 ORDER BY DATE(created)
EOF;
		// 集計開始日を指定（デモモードの場合、設定ファイルの日付を設定）
		$start_date = Configure::read('demo_mode') ? Configure::read('demo_target_date') : date('Y-m-d', strtotime('-13 day'));
		
		$params = [
			'user_id'		=> $user_id,
			'start_date'	=> $start_date,
		];
		
		$data = $this->query($sql, $params);
		$ret_data = [];
		
		//debug($data);
		
		for($i=0; $i<14; $i++ )
		{
			$index = count($ret_data);
			
			$ret_data[$index] = 0;
			
			foreach ($data as $item)
			{
				$target_date = Configure::read('demo_mode') ? date('Y-m-d', strtotime(($i).' day', strtotime(Configure::read('demo_target_date')))) : date('Y-m-d', strtotime(($i-13).' day'));
				
				if($item[0]['created'] == $target_date)
					$ret_data[$index] = $item[0]['cnt'];
			}
		}
		
		//debug($ret_data);
		return $ret_data;
	}

	/**
	 * 日付ラベルリストを取得
	 */
	public function getDateLabels()
	{
		$labels		= [];
		
		for($i=0; $i<14; $i++ )
		{
			// 日付ラベルを指定（デモモードの場合、設定ファイルの日付を基に計算）
			$target_date = Configure::read('demo_mode') ? date('m/d', strtotime(($i).' day', strtotime(Configure::read('demo_target_date')))) : date('m/d', strtotime(($i-13).' day'));
			
			$labels[count($labels)] = $target_date;
		}
		
		return $labels;
	}
}
