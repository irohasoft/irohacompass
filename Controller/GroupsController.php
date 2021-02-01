<?php
/**
 * iroha Compass Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2021 iroha Soft, Inc. (https://irohasoft.jp)
 * @link          https://irohacompass.irohasoft.jp
 * @license       https://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */

App::uses('AppController', 'Controller');

class GroupsController extends AppController
{

	public $components = [
		'Paginator',
		'Security' => [
			'csrfUseOnce' => false,
		],
	];

	/**
	 * グループ一覧を表示
	 */
	public function admin_index()
	{
		$this->Group->recursive = 0;
		$this->Group->virtualFields['theme_title'] = 'GroupTheme.theme_title'; // 外部結合テーブルのフィールドによるソート用
		
		$this->Paginator->settings = [
			'fields' => ['*', 'GroupTheme.theme_title'],
			'limit' => 20,
			'order' => 'created desc',
			'joins' => [
				['type' => 'LEFT OUTER', 'alias' => 'GroupTheme',
						'table' => '(SELECT gc.group_id, group_concat(c.title order by c.id SEPARATOR \', \') as theme_title FROM ib_groups_themes gc INNER JOIN ib_themes c ON c.id = gc.theme_id  GROUP BY gc.group_id)',
						'conditions' => 'Group.id = GroupTheme.group_id']
			]
		];
		
		$this->set('groups', $this->Paginator->paginate());
	}

	/**
	 * グループの追加
	 */
	public function admin_add()
	{
		$this->admin_edit();
		$this->render('admin_edit');
	}

	/**
	 * グループの編集
	 * @param int $group_id 編集するグループのID
	 */
	public function admin_edit($group_id = null)
	{
		if($this->action == 'edit' && !$this->Group->exists($group_id))
		{
			throw new NotFoundException(__('Invalid group'));
		}
		
		if($this->request->is(['post', 'put']))
		{
			if($this->Group->save($this->request->data))
			{
				$this->Flash->success(__('グループ情報を保存しました'));
				return $this->redirect(['action' => 'index']);
			}
			else
			{
				$this->Flash->error(__('The group could not be saved. Please, try again.'));
			}
		}
		else
		{
			$this->request->data = $this->Group->get($group_id);
		}
		
		$users   = $this->Group->User->find('list');
		$themes = $this->Group->Theme->find('list');
		
		$this->set(compact('themes', 'users'));
	}

	/**
	 * グループの削除
	 * @param int $group_id 削除するグループのID
	 */
	public function admin_delete($group_id = null)
	{
		$this->Group->id = $group_id;
		
		if(!$this->Group->exists())
		{
			throw new NotFoundException(__('Invalid group'));
		}
		
		$this->request->allowMethod('post', 'delete');
		
		if($this->Group->delete())
		{
			$this->Flash->success(__('グループ情報を削除しました'));
		}
		else
		{
			$this->Flash->error(__('The group could not be deleted. Please, try again.'));
		}
		
		return $this->redirect(['action' => 'index']);
	}
}
