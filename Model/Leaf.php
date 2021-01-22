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
 * Leaf Model
 *
 * @property User $User
 * @property Group $Group
 */
class Leaf extends AppModel
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


	public function deleteLeaf($user_id, $leaf_id)
	{
		// リーフの削除
		$sql = 'DELETE FROM ib_leafs WHERE leaf_id = :leaf_id';
		
		$params = [
//			'user_id' => $user_id,
			'leaf_id' => $leaf_id,
		];
		
		$this->query($sql, $params);
		
		// リンクの削除
		$sql = 'DELETE FROM ib_links WHERE leaf_id = :leaf_id OR leaf_id2 = :leaf_id';
		
		$params = [
//			'user_id' => $user_id,
			'leaf_id' => $leaf_id,
		];
		
		$this->query($sql, $params);
	}

	// 指定したキーワードを含むカードが所属する課題ID一覧を取得（キーワード検索用）
	public function getTaskIdByKeyword($keyword, $theme_id)
	{
		$sql = <<<EOF
SELECT 'task', tm.id as theme_id, tm.title as theme_title, ts.id as task_id, ts.title as task_title, l.leaf_title, l.leaf_content
  FROM ib_leafs l INNER JOIN ib_tasks ts ON l.page_id = ts.page_id INNER JOIN ib_themes tm ON ts.theme_id = tm.id
 WHERE  tm.id = :theme_id AND (l.leaf_title like :keyword OR l.leaf_content like :keyword)
 UNION
SELECT 'progress', tm.id as theme_id, tm.title as theme_title, ts.id as task_id, ts.title as task_title, l.leaf_title, l.leaf_content
  FROM ib_leafs l INNER JOIN ib_progresses p ON l.page_id = p.page_id INNER JOIN ib_tasks ts ON p.task_id = ts.id INNER JOIN ib_themes tm ON ts.theme_id = tm.id
 WHERE  tm.id = :theme_id AND (l.leaf_title like :keyword OR l.leaf_content like :keyword)
EOF;
		$params = ['theme_id' => $theme_id, 'keyword' => '%'.$keyword.'%'];
		$data = $this->query($sql, $params);
		
		$list = [];
		
		for($i=0; $i< count($data); $i++)
		{
			$list[$i] = $data[$i][0]['task_id'];
		}
		
		return $list;
	}
}
