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
 * Progress Model
 *
 * @property Task $Task
 * @property User $User
 */
class Progress extends AppModel
{
	/**
	 * バリデーションルール
	 * https://book.cakephp.org/2/ja/models/data-validation.html
	 * @var array
	 */
	public $validate = [
		'task_id' 		=> ['numeric'  => ['rule' => ['numeric']]],
		'progress_type'	=> ['notBlank' => ['rule' => ['notBlank']]],
		'body'			=> ['notBlank' => ['rule' => ['notBlank']]],
		'rate'			=> ['notBlank' => ['rule' => ['notBlank']]],
		'emotion_icon'	=> ['notBlank' => ['rule' => ['notBlank']]],
	];

	/**
	 * アソシエーションの設定
	 * https://book.cakephp.org/2/ja/models/associations-linking-models-together.html
	 * @var array
	 */
	public $belongsTo = [
		'Task' => [
			'className' => 'Task',
			'foreignKey' => 'task_id',
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
		'Smile' => [
			'className' => 'Smile',
			'foreignKey' => 'progress_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		]
	];
}
