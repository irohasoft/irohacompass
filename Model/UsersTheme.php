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
 * UsersTheme Model
 *
 * @property User $User
 * @property Theme $Theme
 */
class UsersTheme extends AppModel
{

	/**
	 * Validation rules
	 *
	 * @var array
	 */
	public $validate = [
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
			]
	];

	public function getThemeRecord($user_id)
	{
		$sql = <<<EOF
 SELECT Theme.id, Theme.title, Theme.user_id, DATE_FORMAT(Theme.created, '%Y/%m/%d') as first_date, DATE_FORMAT(Theme.modified, '%Y/%m/%d') as last_date,
       (ifnull(content_cnt, 0) - ifnull(study_cnt, 0) ) as left_cnt
   FROM ib_themes Theme
   LEFT OUTER JOIN
		-- 対応済み課題数
		(SELECT theme_id, COUNT(*) as study_cnt
		   FROM ib_tasks
		  WHERE status = 3 -- 対応済み
		  GROUP BY theme_id) StudyCount
     ON StudyCount.theme_id   = Theme.id
   LEFT OUTER JOIN
		-- 学習テーマ内の課題数
		(SELECT theme_id, COUNT(*) as content_cnt
		   FROM ib_tasks
		  GROUP BY theme_id) TaskCount
     ON TaskCount.theme_id   = Theme.id
  WHERE Theme.id IN (SELECT theme_id FROM ib_users_groups ug INNER JOIN ib_groups_themes gc ON ug.group_id = gc.group_id WHERE user_id = :user_id)
     OR Theme.id IN (SELECT theme_id FROM ib_users_themes WHERE user_id = :user_id)
  ORDER BY Theme.modified desc
EOF;
		// debug($user_id);

		$params = [
				'user_id' => $user_id
		];

		$data = $this->query($sql, $params);

		return $data;
	}


	public function getMailList($theme_id)
	{
		$sql = <<<EOF
#学習テーマの所有者
SELECT u.email, u.name, u.role
  FROM ib_themes t
 INNER JOIN ib_users u ON t.user_id = u.id
 WHERE t.id = :theme_id AND LENGTH(u.email) > 5
UNION
#管理者
SELECT email, name, role
  FROM ib_users
 WHERE role = 'admin' AND LENGTH(email) > 5
UNION
#学習テーマの関係者
SELECT u.email as email, u.name as name, u.role as role
  FROM ib_users_themes uc
 INNER JOIN ib_themes c ON uc.theme_id = c.id
 INNER JOIN ib_users u ON uc.user_id = u.id
 WHERE uc.theme_id = :theme_id AND LENGTH(u.email) > 5
EOF;
		$params = ['theme_id' => $theme_id];
		$data = $this->query($sql, $params);
		
		//debug($data);
		$list = [];
		
		for($i=0; $i< count($data); $i++)
		{
			$list[$i] = $data[$i][0];
		}
		return $list;
	}

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
			'Theme' => [
					'className' => 'Theme',
					'foreignKey' => 'theme_id',
					'conditions' => '',
					'fields' => '',
					'order' => ''
			]
	];
}
