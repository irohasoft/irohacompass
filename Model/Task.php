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
	 * バリデーションルール
	 * https://book.cakephp.org/2/ja/models/data-validation.html
	 * @var array
	 */
	public $validate = [
		'theme_id'	=> ['numeric'  => ['rule' => ['numeric']]],
		'user_id'	=> ['numeric'  => ['rule' => ['numeric']]],
		'title'		=> ['notBlank' => ['rule' => ['notBlank']]],
		'body'		=> ['notBlank' => ['rule' => ['notBlank']]],
	];

	/**
	 * アソシエーションの設定
	 * https://book.cakephp.org/2/ja/models/associations-linking-models-together.html
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

	/**
	 * 進捗率を更新
	 */
	public function updateRate($task_id)
	{
		$sql = "UPDATE ib_tasks SET rate = (SELECT MAX(rate) FROM ib_progresses WHERE task_id = :task_id) WHERE id= :task_id";

		$params = [
			'task_id' => $task_id
		];

		$this->query($sql, $params);
	}
}
