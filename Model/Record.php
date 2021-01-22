<?php
/**
 * iroha Compass Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2018 iroha Soft, Inc. (http://irohasoft.jp)
 * @link          http://irohacompass.irohasoft.jp
 * @license       http://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */

App::uses('AppModel', 'Model');

/**
 * Record Model
 *
 * @property Group $Group
 * @property Theme $Theme
 * @property User $User
 * @property Task $Task
 */
class Record extends AppModel
{

	/**
	 * Validation rules
	 *
	 * @var array
	 */
	public $validate = [
			'theme_id' => [
					'numeric' => [
							'rule' => [
									'numeric'
							]
					// 'message' => 'Your custom message here',
					// 'allowEmpty' => false,
					// 'required' => false,
					// 'last' => false, // Stop validation after this rule
					// 'on' => 'create', // Limit validation to 'create' or
					// 'update' operations
										]
			],
			'user_id' => [
					'numeric' => [
							'rule' => [
									'numeric'
							]
					// 'message' => 'Your custom message here',
					// 'allowEmpty' => false,
					// 'required' => false,
					// 'last' => false, // Stop validation after this rule
					// 'on' => 'create', // Limit validation to 'create' or
					// 'update' operations
										]
			],
			'task_id' => [
					'numeric' => [
							'rule' => [
									'numeric'
							]
					// 'message' => 'Your custom message here',
					// 'allowEmpty' => false,
					// 'required' => false,
					// 'last' => false, // Stop validation after this rule
					// 'on' => 'create', // Limit validation to 'create' or
					// 'update' operations
										]
			]
	];
	
	// The Associations below have been created with all possible keys, those
	// that are not needed can be removed
	public $hasMany = [
	];

	/**
	 * belongsTo associations
	 *
	 * @var array
	 */
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
	
	// 検索用
	public $actsAs = [
			'Search.Searchable'
	];

	public $filterArgs = [
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
