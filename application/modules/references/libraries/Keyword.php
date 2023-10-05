<?php defined('BASEPATH') or exit('No direct script access allowed');

class Keyword
{
	/**
	 * The Keywords Construct
	 */
	public function __construct()
	{
		ci()->load->model('references/keyword_model');
		ci()->load->model('references/keyword_applied_model');
	}

	public static function getString($hash)
    {
        $results = array();
        $keywords = ci()->keyword_applied_model
            ->with('detail')
            ->get_all(array('hash' => $hash));
        if ($keywords) {
            foreach ($keywords as $item) {
                $results[] = $item->detail->name;
            }
        }

        return implode(', ', $results);
    }

	public static function getArray($hash)
	{
		$results = array();
        $keywords = ci()->keyword_applied_model->with('detail')
            ->get_all(array('hash' => $hash));
        if ($keywords) {
            foreach ($keywords as $item)
            {
                $results[] = $item->detail->name;
            }
        }
		return $results;
	}

	public static function getArrayField($hash)
    {
        $results = array();
        $keywords = ci()->keyword_applied_model->with('detail')
            ->get_all(array('hash' => $hash));
        if ($keywords) {
            foreach ($keywords as $item) {
                $results[] = array(
                    'id' => $item->detail->id,
                    'name' => $item->detail->name,
                );
            }
        }
        return $results;
    }

    public static function getDropdown($hash)
    {
        if (is_array($hash)) {
            log_message('error', 'array');
        }

        $results = array();
        $keywords = ci()->keyword_applied_model->with('detail')
            ->get_all(array('hash' => $hash));
        if ($keywords) {
            foreach ($keywords as $item) {
                $results[$item->detail->id] = $item->detail->name;
            }
        }

        return $results;
    }

	public static function get($hash)
	{
		return ci()->keyword_applied_model->with('detail')
            ->get_all(array('hash' => $hash));
	}

	public static function add($keyword)
	{
		return ci()->keyword_model->insert(array('name' => self::prep($keyword)));
	}

	public static function prep($keyword)
	{
		if (function_exists('mb_strtolower')) {
			return mb_strtolower(trim($keyword));
		}
		else {
			return strtolower(trim($keyword));
		}
	}

	public static function process($keywords, $old_hash = null)
	{
        if (is_array($keywords))
            $keywords = implode(',', $keywords);

		// Remove the old keyword assignments if we're updating
		if ($old_hash !== null) {
		    ci()->keyword_applied_model->delete(array('hash' => $old_hash));
		}

		// No keywords? Let's not bother then
		if ( ! ($keywords = trim($keywords))) {
			return '';
		}

		$assignment_hash = md5(microtime().mt_rand());
		// Split em up and prep away
		$keywords = explode(',', $keywords);
		foreach ($keywords as &$keyword)
		{
			$keyword = self::prep($keyword);

			// Keyword already exists
			if (($row = ci()->keyword_model->get(array('name' => $keyword)))) {
				$keywordId = $row->id;
			}
			// Create it, and keep the record
			else {
				$keywordId = self::add($keyword);
			}
			
			// Create assignment record
            ci()->keyword_applied_model->insert(array(
                'hash' => $assignment_hash,
                'keyword_id' => $keywordId
            ));
		}
		
		return $assignment_hash;
	}

}

/* End of file Keywords.php */