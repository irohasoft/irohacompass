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
 * Theme Model
 *
 * @property Group $Group
 * @property Task $Task
 * @property Record $Record
 * @property User $User
 */
class Theme extends AppModel
{

	/**
	 * Validation rules
	 *
	 * @var array
	 */
	public $validate = [
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
			'learning_target' => [
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
			]
	];
	
	// The Associations below have been created with all possible keys, those
	// that are not needed can be removed
	
	/**
	 * belongsTo associations
	 *
	 * @var array
	 */
	public $belongsTo = [
		'User' => [
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];

	/**
	 * hasMany associations
	 *
	 * @var array
	 */
	public $hasMany = [
	/*
		'Task' => array(
			'className' => 'Task',
			'foreignKey' => 'theme_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	*/
	];

	/**
	 * hasAndBelongsToMany associations
	 *
	 * @var array
	 */
	 /*
	public $hasAndBelongsToMany = array(
			'User' => array(
					'className' => 'User',
					'joinTable' => 'users_themes',
					'foreignKey' => 'theme_id',
					'associationForeignKey' => 'user_id',
					'unique' => 'keepExisting',
					'conditions' => '',
					'fields' => '',
					'order' => '',
					'limit' => '',
					'offset' => '',
					'finderQuery' => ''
			)
	);
	*/

	public function setOrder($id_list)
	{
		for($i=0; $i< count($id_list); $i++)
		{
			$sql = "UPDATE ib_themes SET sort_no = :sort_no WHERE id= :id";

			$params = [
					'sort_no' => ($i+1),
					'id' => $id_list[$i]
			];

			$this->query($sql, $params);
		}
	}
	
	// コースへのアクセス権限チェック
	public function hasRight($user_id, $theme_id)
	{
		$has_right = false;
		
		$params = [
			'user_id'   => $user_id,
			'theme_id' => $theme_id
		];
		
		$sql = <<<EOF
SELECT count(*) as cnt
  FROM ib_users_themes
 WHERE theme_id = :theme_id
   AND user_id   = :user_id
EOF;
		$data = $this->query($sql, $params);
		
		if($data[0][0]["cnt"] > 0)
			$has_right = true;
		
		$sql = <<<EOF
SELECT count(*) as cnt
  FROM ib_groups_themes gc
 INNER JOIN ib_users_groups ug ON gc.group_id = ug.group_id AND ug.user_id   = :user_id
 WHERE gc.theme_id = :theme_id
EOF;
		$data = $this->query($sql, $params);
		
		if($data[0][0]["cnt"] > 0)
			$has_right = true;
		
		return $has_right;
	}

	public function getUserTheme($user_id)
	{
		$sql = <<<EOF
 SELECT Theme.*, Theme.id, Theme.title, User.name, first_date, last_date,
       (ifnull(task_cnt, 0) - ifnull(study_cnt, 0) ) as left_cnt,
       (SELECT kind
          FROM ib_records h1
         WHERE h1.theme_id = Theme.id
           AND h1.user_id     	=:user_id
         ORDER BY created
          DESC LIMIT 1) as kind
   FROM ib_themes Theme
  INNER JOIN ib_users User ON Theme.user_id = User.id
   LEFT OUTER JOIN
       (SELECT h.theme_id, h.user_id,
               MAX(DATE_FORMAT(created, '%Y/%m/%d')) as last_date,
               MIN(DATE_FORMAT(created, '%Y/%m/%d')) as first_date
          FROM ib_records h
         WHERE h.user_id =:user_id
         GROUP BY h.theme_id, h.user_id) Record
     ON Record.theme_id   = Theme.id
    AND Record.user_id     =:user_id
   LEFT OUTER JOIN
		(SELECT theme_id, COUNT(*) as study_cnt
		   FROM
			(SELECT r.theme_id, r.task_id, COUNT(*)
			   FROM ib_records r
			  INNER JOIN ib_tasks c ON r.task_id = c.id
			  WHERE r.user_id = :user_id
			  GROUP BY r.theme_id, r.task_id) as c
		 GROUP BY theme_id) StudyCount
     ON StudyCount.theme_id   = Theme.id
   LEFT OUTER JOIN
		(SELECT theme_id, COUNT(*) as task_cnt
		   FROM ib_tasks
		  WHERE kind NOT IN ('label', 'file')
		  GROUP BY theme_id) TaskCount
     ON TaskCount.theme_id   = Theme.id
  WHERE Theme.id IN (SELECT theme_id FROM ib_users_groups ug INNER JOIN ib_groups_themes gc ON ug.group_id = gc.group_id WHERE user_id = :user_id)
     OR Theme.id IN (SELECT theme_id FROM ib_users_themes WHERE user_id = :user_id)
     OR Theme.user_id = :user_id
  ORDER BY Theme.sort_no asc
EOF;
		// debug($user_id);

		$params = [
				'user_id' => $user_id
		];

		$data = $this->query($sql, $params);

		return $data;
	}

	public function addUserTheme($user_id, $theme_id)
	{
		$sql = "SELECT COUNT(*) as cnt FROM ib_users_themes WHERE user_id = :user_id AND theme_id = :theme_id";
		
		$params = [
			'user_id' => $user_id,
			'theme_id' => $theme_id,
		];
		
		$data = $this->query($sql, $params);
		$cnt  = $data[0][0]['cnt'];
		
		if($cnt < 1)
		{
			$sql = "INSERT INTO ib_users_themes(user_id, theme_id) VALUES(:user_id, :theme_id)";
			$params = [
				'user_id' => $user_id,
				'theme_id' => $theme_id,
			];
			$this->query($sql, $params);
		}
	}
}
