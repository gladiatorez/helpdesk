<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class Email_template_model extends MY_Model
{
    public $table = 'email_templates';
    public $primary_key = 'id';

    public $fillable = array('slug','name','description','subject','body','lang','is_default','module');
    public $delete_cache_on_save = TRUE;

    public function getTemplate($slug = false)
    {
        $results = $this->get_all(array('slug' => $slug));
        $templates = array();

        if ($results)
        {
            foreach($results as $template)
            {
                $templates[$template->lang] = $template;
            }
        }

        return $templates;
    }

    public function is_default($id = 0)
    {
        return $this->count_rows()->get_count_by(array('id' => $id, 'default' => 0)) > 0;
    }
}