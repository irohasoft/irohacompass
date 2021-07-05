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
 * Note Model
 *
 * @property User $User
 */
class Note extends AppModel
{
	/**
	 * バリデーションルール
	 * https://book.cakephp.org/2/ja/models/data-validation.html
	 * @var array
	 */
	public $validate = [
	];

	/**
	 * アソシエーションの設定
	 * https://book.cakephp.org/2/ja/models/associations-linking-models-together.html
	 * @var array
	 */
	public $hasAndBelongsToMany = [
	];

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
	 * ノートにアクセス可能なユーザIDリストの取得
	 */
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
