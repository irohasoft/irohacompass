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
 * Setting Model
 *
 */
class Setting extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = [
		'setting_key' => [
			'notBlank' => [
				'rule' => ['notBlank'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'setting_value' => [
			'notBlank' => [
				'rule' => ['notBlank'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
	];
	
	public function getSettingValue($setting_key)
	{
		$setting_value = "";
		
		$sql = <<<EOF
SELECT setting_value
  FROM ib_settings
 WHERE setting_key = :setting_key
EOF;
		$params = [
				'setting_key' => $setting_key
		];
		
		$data = $this->query($sql, $params);
		
		
		//debug($data);
		/*
		if(count($data) > 0)
			$setting_value = $data['Setting'][0]['setting_value'];
		*/
		
		return $setting_value;
	}
	
	public function getSettings()
	{
		$result = [];
		
		$settings = $this->query("SELECT setting_key, setting_value FROM ib_settings");
		
		foreach ($settings as $setting)
		{
			$result[$setting['ib_settings']['setting_key']] = $setting['ib_settings']['setting_value'];
		}
		
		//debug($result);
		
		return $result;
	}
	
	public function setSettings($settings)
	{
		foreach ($settings as $key => $value)
		{
			$params = [
				'setting_key' => $key,
				'setting_value' => $value
			];
			
			$this->query("UPDATE ib_settings SET setting_value = :setting_value WHERE setting_key = :setting_key", $params);
		}
	}
}
