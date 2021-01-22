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
 * RecordsQuestion Model
 *
 * @property Record $Record
 * @property Question $Question
 */
class RecordsQuestion extends AppModel
{

	/**
	 * Validation rules
	 *
	 * @var array
	 */
	public $validate = [
			'record_id' => [
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
			'progress_id' => [
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
			'score' => [
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
			'Record' => [
					'className' => 'Record',
					'foreignKey' => 'record_id',
					'conditions' => '',
					'fields' => '',
					'order' => ''
			],
			'Progress' => [
					'className' => 'Progress',
					'foreignKey' => 'progress_id',
					'conditions' => '',
					'fields' => '',
					'order' => ''
			]
	];
}
