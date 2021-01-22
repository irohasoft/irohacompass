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
 * Task Model
 *
 * @property Group $Group
 * @property Theme $Theme
 * @property User $User
 * @property Record $Record
 */
class Task extends AppModel
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
			'title' => [
					'notBlank' => [
							'rule' => [
									'notBlank'
							]
					// 'message' => 'Your custom message here',
					// 'allowEmpty' => false,
					// 'required' => false,
					// 'last' => false, // Stop validation after this rule
					// 'on' => 'create', // Limit validation to 'create' or
					// 'update' operations
										]
			],
			'body' => [
					'notBlank' => [
							'rule' => [
									'notBlank'
							]
					// 'message' => 'Your custom message here',
					// 'allowEmpty' => false,
					// 'required' => false,
					// 'last' => false, // Stop validation after this rule
					// 'on' => 'create', // Limit validation to 'create' or
					// 'update' operations
										]
			],
			'timelimit' => [
					'numeric' => [
						'rule' => ['range', -1, 101],
						'message' => '0-100の整数で入力して下さい。',
						'allowEmpty' => true,
					// 'required' => false,
					// 'last' => false, // Stop validation after this rule
					// 'on' => 'create', // Limit validation to 'create' or
					// 'update' operations
										]
			],
			'kind' => [
					'notBlank' => [
							'rule' => [
									'notBlank'
							]
					// 'message' => 'Your custom message here',
					// 'allowEmpty' => false,
					// 'required' => false,
					// 'last' => false, // Stop validation after this rule
					// 'on' => 'create', // Limit validation to 'create' or
					// 'update' operations
										]
			],
			'sort_no' => [
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
	];

	public function getTaskRecord($user_id, $theme_id)
	{
		$sql = <<<EOF
 SELECT Task.*, first_date, last_date, record_id, Record.study_sec, Record.study_count,
       (SELECT kind
          FROM ib_records h1
         WHERE h1.task_id = Task.id
           AND h1.user_id    =:user_id
         ORDER BY created
          DESC LIMIT 1) as kind,
       (SELECT ifnull(is_passed, 0)
          FROM ib_records h1
         WHERE h1.task_id = Task.id
           AND h1.user_id    =:user_id
         ORDER BY created
          DESC LIMIT 1) as is_passed
   FROM ib_tasks Task
   LEFT OUTER JOIN
       (SELECT h.task_id, h.user_id,
               MAX(DATE_FORMAT(created, '%Y/%m/%d')) as last_date,
               MIN(DATE_FORMAT(created, '%Y/%m/%d')) as first_date,
			   MAX(id) as record_id,
			   SUM(ifnull(study_sec, 0)) as study_sec,
			   COUNT(*) as study_count
		  FROM ib_records h
         WHERE h.user_id    =:user_id
		   AND h.theme_id  =:theme_id
         GROUP BY h.task_id, h.user_id) Record
     ON Record.task_id  = Task.id
    AND Record.user_id     =:user_id
  WHERE Task.theme_id  =:theme_id
  ORDER BY Task.sort_no
EOF;
		// debug($user_id);

		$params = [
//				'group_id' => $group_id,
				'user_id' => $user_id,
				'theme_id' => $theme_id
		];

		$data = $this->query($sql, $params);

		return $data;
	}
	// The Associations below have been created with all possible keys, those
	// that are not needed can be removed


	public function setOrder($id_list)
	{
		for($i=0; $i< count($id_list); $i++)
		{
			$sql = "UPDATE ib_tasks SET sort_no = :sort_no WHERE id= :id";

			$params = [
					'sort_no' => ($i+1),
					'id' => $id_list[$i]
			];

			$this->query($sql, $params);
		}
	}

	public function updateRate($task_id)
	{
		$sql = "UPDATE ib_tasks SET rate = (SELECT MAX(rate) FROM ib_progresses WHERE task_id = :task_id) WHERE id= :task_id";

		$params = [
			'task_id' => $task_id
		];

		$this->query($sql, $params);
	}

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
			]
	];

	/**
	 * hasMany associations
	 *
	 * @var array
	 */
	public $hasMany = [
	];

	// 検索用
	public $actsAs = [
			'Search.Searchable'
	];

	public $filterArgs = [
			'status' => [
					'type' => 'value',
					'field' => 'Task.status'
			],
			'themetitle' => [
					'type' => 'like',
					'field' => 'Theme.title'
			],
			'contenttitle' => [
					'type' => 'like',
					'field' => 'Task.title'
			],
			'active' => [
					'type' => 'value'
			]
	];

}
