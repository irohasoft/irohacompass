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
	public $validate = array(
			'theme_id' => array(
					'numeric' => array(
							'rule' => array(
									'numeric'
							)
					// 'message' => 'Your custom message here',
					// 'allowEmpty' => false,
					// 'required' => false,
					// 'last' => false, // Stop validation after this rule
					// 'on' => 'create', // Limit validation to 'create' or
					// 'update' operations
										)
			),
			'user_id' => array(
					'numeric' => array(
							'rule' => array(
									'numeric'
							)
					// 'message' => 'Your custom message here',
					// 'allowEmpty' => false,
					// 'required' => false,
					// 'last' => false, // Stop validation after this rule
					// 'on' => 'create', // Limit validation to 'create' or
					// 'update' operations
										)
			),
			'task_id' => array(
					'numeric' => array(
							'rule' => array(
									'numeric'
							)
					// 'message' => 'Your custom message here',
					// 'allowEmpty' => false,
					// 'required' => false,
					// 'last' => false, // Stop validation after this rule
					// 'on' => 'create', // Limit validation to 'create' or
					// 'update' operations
										)
			)
	);
	
	// The Associations below have been created with all possible keys, those
	// that are not needed can be removed
	public $hasMany = array(
	);

	/**
	 * belongsTo associations
	 *
	 * @var array
	 */
	public $belongsTo = array(
			'Theme' => array(
					'className' => 'Theme',
					'foreignKey' => 'theme_id',
					'conditions' => '',
					'fields' => '',
					'order' => ''
			),
			'User' => array(
					'className' => 'User',
					'foreignKey' => 'user_id',
					'conditions' => '',
					'fields' => '',
					'order' => ''
			),
			'Task' => array(
					'className' => 'Task',
					'foreignKey' => 'task_id',
					'conditions' => '',
					'fields' => '',
					'order' => ''
			)
	);
	
	// 検索用
	public $actsAs = array(
			'Search.Searchable'
	);

	public $filterArgs = array(
			'username' => array(
					'type' => 'like',
					'field' => 'User.username'
			),
			'name' => array(
					'type' => 'like',
					'field' => 'User.name'
			)
	);

	public function addRecord($user_id, $theme_id, $task_id, $record_type, $study_sec)
	{
		$status = null;
		$this->create();
		$theme_rate  = null;
		$task_rate = null;
		
		// 学習テーマの進捗率（全課題の平均）
		$sql = "SELECT AVG(rate) as theme_avg FROM ib_tasks WHERE user_id = :user_id AND theme_id = :theme_id";
		$params = array(
			'user_id'   => $user_id,
			'theme_id' => $theme_id
		);
		$data = $this->query($sql, $params);
		$theme_rate = $data[0][0]["theme_avg"];
		
		// 課題の進捗率、ステータス
		if($task_id > 0)
		{
			$sql = "SELECT ifnull(rate, 0) as task_rate, status FROM ib_tasks WHERE id = :task_id";
			$params = array(
				'task_id' => $task_id
			);
			$data = $this->query($sql, $params);
			debug($data);
			
			$task_rate  = $data[0][0]["task_rate"];
			$status        = $data[0]['ib_tasks']["status"];
		}
		
		$data = array(
			'user_id'		=> $user_id,
			'theme_id'		=> $theme_id,
			'task_id'		=> $task_id,
			'study_sec'		=> $study_sec,
			'rate'			=> $task_rate,
			'theme_rate'	=> $theme_rate,
			'record_type'	=> $record_type,
			'is_complete'	=> $status
		);
		
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
		
		$params = array(
			'user_id'		=> $user_id,
			'start_date'	=> $start_date,
		);
		
		$data = $this->query($sql, $params);
		$ret_data = array();
		
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
		
		$params = array(
			'user_id'		=> $user_id,
			'start_date'	=> $start_date,
		);
		
		$data = $this->query($sql, $params);
		$ret_data = array();
		
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
		$labels		= array();
		
		for($i=0; $i<14; $i++ )
		{
			// 日付ラベルを指定（デモモードの場合、設定ファイルの日付を基に計算）
			$target_date = Configure::read('demo_mode') ? date('m/d', strtotime(($i).' day', strtotime(Configure::read('demo_target_date')))) : date('m/d', strtotime(($i-13).' day'));
			
			$labels[count($labels)] = $target_date;
		}
		
		return $labels;
	}
}
