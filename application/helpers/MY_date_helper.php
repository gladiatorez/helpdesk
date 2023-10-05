<?php defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('timespan'))
{
	/**
	 * Timespan
	 *
	 * Returns a span of seconds in this format:
	 *	10 days 14 hours 36 minutes 47 seconds
	 *
	 * @param	int	a number of seconds
	 * @param	int	Unix timestamp
	 * @param	int	a number of display units
	 * @return	array
	 */
	function timespanArr($seconds = 1, $time = '', $units = 7)
	{
		$CI =& get_instance();
		$CI->lang->load('date');

		is_numeric($seconds) OR $seconds = 1;
		is_numeric($time) OR $time = time();
		is_numeric($units) OR $units = 7;

		$seconds = ($time <= $seconds) ? 1 : $time - $seconds;

		$str = array();
		$years = floor($seconds / 31557600);

		if ($years > 0)
		{
			$str['year'] = $years;
		}

		$seconds -= $years * 31557600;
		$months = floor($seconds / 2629743);

		if (count($str) < $units && ($years > 0 OR $months > 0))
		{
			if ($months > 0)
			{
				$str['month'] = $months;
			}

			$seconds -= $months * 2629743;
		}

		$weeks = floor($seconds / 604800);

		if (count($str) < $units && ($years > 0 OR $months > 0 OR $weeks > 0))
		{
			if ($weeks > 0)
			{
				$str['weeks'] = $weeks;
			}

			$seconds -= $weeks * 604800;
		}

		$days = floor($seconds / 86400);

		if (count($str) < $units && ($months > 0 OR $weeks > 0 OR $days > 0))
		{
			if ($days > 0)
			{
				$str['days'] = $days;
			}

			$seconds -= $days * 86400;
		}

		$hours = floor($seconds / 3600);

		if (count($str) < $units && ($days > 0 OR $hours > 0))
		{
			if ($hours > 0)
			{
				$str['hours'] = $hours;
			}

			$seconds -= $hours * 3600;
		}

		$minutes = floor($seconds / 60);

		if (count($str) < $units && ($days > 0 OR $hours > 0 OR $minutes > 0))
		{
			if ($minutes > 0)
			{
				$str['minutes'] = $minutes;
			}

			$seconds -= $minutes * 60;
		}

		if (count($str) === 0)
		{
			$str['seconds'] = $seconds;
		}

		return $str;
	}
}

if ( ! function_exists('timespanFormat'))
{
    function timespanFormat($seconds = 1, $time = '', $units = 7)
    {
        $timespan = timespanArr($seconds, $time, $units);
        $weeks = isset($timespan['weeks']) ? $timespan['weeks'] : 0 ;
        $days = isset($timespan['days']) ? $timespan['days'] + ($weeks * 7) : 0 ;
        $hours = isset($timespan['hours']) ? $timespan['hours'] + ($days * 24) : 0;
        $minutes = isset($timespan['minutes']) ? $timespan['minutes'] : 0;
        $seconds = isset($timespan['seconds']) ? $timespan['seconds'] : 0;

        return sprintf(
            '%s:%s:%s',
            strlen($hours) < 2 ? str_pad($hours, 2, '0', STR_PAD_LEFT) : $hours,
            strlen($minutes) < 2 ? str_pad($minutes, 2, '0', STR_PAD_LEFT) : $minutes,
            strlen($seconds) < 2 ? str_pad($seconds, 2, '0', STR_PAD_LEFT) : $seconds
        );
    }
}