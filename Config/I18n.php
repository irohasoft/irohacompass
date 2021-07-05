<?php
class I18n
{
	public static function translate($singular)
	{
		$messages = Configure::read('messages');
		
		if($messages)
		{
			foreach ($messages as $row)
			{
				if($row[0] == $singular)
				{
					$row[1] = str_replace("\r", '', $row[1]);
					$row[1] = str_replace("\n", '', $row[1]);
					$singular = $row[1];
				}
			}
		}
		
		return $singular;
	}
	
	public static function insertArgs($translated, array $args)
	{
		$len = count($args);
		if ($len === 0 || ($len === 1 && $args[0] === null)) {
			return $translated;
		}

		if (is_array($args[0])) {
			$args = $args[0];
		}

		$translated = preg_replace('/(?<!%)%(?![%\'\-+bcdeEfFgGosuxX\d\.])/', '%%', $translated);
		return vsprintf($translated, $args);
	}
}
