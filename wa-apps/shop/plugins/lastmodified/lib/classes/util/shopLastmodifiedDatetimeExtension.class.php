<?php

class shopLastmodifiedDatetimeExtension
{
	public static function getTimestamp(DateTime $datetime)
	{
		return mktime(
			$datetime->format('H'),
			$datetime->format('i'),
			$datetime->format('s'),
			$datetime->format('m'),
			$datetime->format('d'),
			$datetime->format('Y')
		);
	}

	public static function getDateByType($type)
	{
		$dt = new DateTime('now', new DateTimezone('UTC'));
		$timestamp = self::getTimestamp($dt);
		
		if ($type == 'prev_hour')
		{
			$timestamp -= 60 * 60;
		}
		elseif ($type == 'prev_day')
		{
			$timestamp -= 24 * 60 * 60;
		}

		return new DateTime(date('Y-m-d H:i:s', $timestamp), new DateTimezone('UTC'));
	}
}