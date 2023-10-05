<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Module_model
 *
 * @property The_auth $the_auth
 */
class Module_model extends MY_Model
{
    public $table = 'modules';
    public $primary_key = 'id';
    public $fillable = ['status'];

    protected $_moduleExists = [];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Exists
     *
     * Checks if a module exists
     *
     * @param	string	$slug	The module slug
     * @return	bool
     */
    public function exists($slug)
    {
        if (!$slug) {
            return false;
        }

        if (isset($this->_moduleExists[$slug]))
        {
            return $this->_moduleExists[$slug];
        }

        return $this->count_rows(['slug' => $slug, 'status' => 'A']) > 0;
    }

    private function _spawnClass($slug)
    {
        $details_file = APPPATH.'modules/'.$slug.'/details'.EXT;
        if ( ! is_file($details_file)) {
            return false;
        }
        include_once $details_file;

        $class = 'Module_'.ucfirst(strtolower($slug));
        if ( ! class_exists($class)) {
            throw new Exception("Module $slug has an incorrect details.php class. It should be called '$class'.");
        }

        $langs = $this->config->item('supported_languages');
        $lang = $langs[CURRENT_LANGUAGE];
        $menulang_file = APPPATH.'modules/'.$slug.'/language/'.$lang['folder'].'/menu_lang'.EXT;
        if (is_file($menulang_file)) {
            $this->lang->load($slug.'/menu');
        }

        $permissionlang_file = APPPATH.'modules/'.$slug.'/language/'.$lang['folder'].'/permissions_lang'.EXT;
        if (is_file($permissionlang_file)) {
            $this->lang->load($slug.'/permissions');
        }

        return array(new $class, dirname($details_file));
    }

    public function getAll($params = [], $asBackend = false)
    {
        $modules = [];
        if ($params) {
            foreach ($params as $field => $value) {
                if (in_array($field, array('menu', 'status', 'menu_group'))) {
                    $this->_database->where($field, $value);
                }
                if (in_array($field, array('order'))) {
                    $this->_database->order_by('`order`', 'ASC');
                }
            }
        }

        $result = $this->get_all($this->table);
        if ($result) {
            foreach ($result as $row) {
                if (!$module = $this->_spawnClass($row->slug)) {
                    continue;
                }

                list($class, $location) = $module;
                $info = $class->info();
                $name = !isset($info['name'][CURRENT_LANGUAGE]) ? $info['name']['en'] : $info['name'][CURRENT_LANGUAGE];
                $module = [
                    'name' => $name,
                    'module' => $class,
                    'version' => $class->version,
                    'slug' => $row->slug,
                    'menu' => $row->menu,
                    'menu_group' => $row->menu_group,
                    'order' => $row->order,
                    'sections' => !empty($info['sections']) ? $info['sections'] : array(),
                    'roles' => !empty($info['roles']) ? $info['roles'] : array(),
                    'shortcuts' => !empty($info['shortcuts']) ? $info['shortcuts'] : array(),
                    'path' => $location,
                    'enabled' => $row->status === 'A' ? true : false,
                ];
                $this->_moduleExists[$row->slug] = true;

                if ($asBackend) {
                    if (!$this->the_auth_backend->isUserAdmin() && empty(ci()->permissions[$row->slug])) {
                        continue;
                    }
                }

                $modules[$module['name']] = $module;
            }
        }
        return array_values($modules);
    }

    public function getAllPermissions()
    {
        $modules = $this->getAll(['status' => 'A']);
        if (!$modules) {
            return [];
        }

        $roles = [];
        foreach ($modules as $module)
        {
            $section = [];
            if (isset($module['sections']))
            {
                foreach ($module['sections'] as $key => $item)
                {
                    $section[] = [
                        'name'  => lang($item['name']),
                        'slug'  => $key,
                        'roles' => isset($item['roles'])
                            ? array_map(function($value) use ($key, $module) {
                                return [
                                    'value' => $value,
                                    'text' => lang('roles::'.$module['slug'].'::'.$key.':'.$value)
                                ];
                            }, $item['roles'] )
                            : []
                    ];
                }
            }

            $roles[] = [
                'slug' => $module['slug'],
                'name' => $module['name'],
                'sections' => $section,
                'roles' => isset($module['roles'])
                    ? array_map(function($value) use ($module) {
                        return [
                            'value' => $value,
                            'text' => lang('roles::'.$module['slug'].'::'.$value)
                        ];
                    }, $module['roles'] )
                    : [],
            ];
        }

        return $roles;
    }
}