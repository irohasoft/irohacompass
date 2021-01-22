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
 * Note Model
 *
 * @property User $User
 * @property Group $Group
 */
class Note extends AppModel
{

	/**
	 * Validation rules
	 *
	 * @var array
	 */
	public $validate = [
	];
	
	// The Associations below have been created with all possible keys, those
	// that are not needed can be removed
	
	/**
	 * belongsTo associations
	 *
	 * @var array
	 */
	public $hasAndBelongsToMany = [
	];

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
	public function getUserIDList($note_id)
	{
		$sql = <<<EOF
SELECT id FROM ib_users
EOF;


		$params = [
//			'note_id' => $note_id,
		];
		
		$data = $this->query($sql, $params);
		
		$user_id_list =  [];
		
		//debug($data);
		foreach ($data as $item)
		{
			$user_id_list[] = $item['ib_users']['id'];
		}
		
		return $user_id_list;
	}
}
