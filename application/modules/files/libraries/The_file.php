<?php defined('BASEPATH') OR exit('No direct script access allowed');

class The_file
{
    public static $path;
    public static $maxSizePossible;
    public static $maxSizeAllowed;
    protected static $_cachePath;

    protected static $_type = '';
    protected static $_ext;
    protected static $_filename = null;
    protected static $_mimeType;

    public function __construct()
    {
        ci()->load->config('files/the_file', TRUE);

        self::$path = ci()->config->item('file_path', 'the_file');
        self::$_cachePath = ci()->config->item('cache_dir') . 'files/';

        $postMax = str_replace('M', '', ini_get('post_max_size'));
        $fileMax = str_replace('M', '', ini_get('upload_max_filesize'));

        self::$maxSizePossible = ($fileMax > $postMax ? $postMax : $fileMax) * 1048576; // convert to bytes
        self::$maxSizeAllowed = Setting::get('files_upload_limit', 'files') * 1048576; // convert this to bytes also

        set_exception_handler(array($this, 'exceptionHandler'));
        set_error_handler(array($this, 'errorHandler'));

        ci()->load->model('files/file_model');
        ci()->load->model('files/folder_model');
        ci()->lang->load('files/the_file');
    }

    public static function exceptionHandler($e)
    {
        log_message('debug', $e->getMessage());

        echo json_encode(
            array('status' 	=> false,
                'message' => $e->getMessage(),
                'data' 	=> ''
            )
        );
    }

    public static function errorHandler($e_number, $error)
    {
        log_message('debug', $error);

        // only output the S3 error messages
        if (strpos($error, 'S3') !== false)
        {
            echo json_encode(
                array('status' 	=> false, // clean up the error message to make it more readable
                    'message' => preg_replace('@S3.*?\[.*?\](.*)$@ms', '$1', $error),
                    'data' 	=> ''
                )
            );
            die();
        }
    }

    protected static function createSlug($name)
    {
        $name = convert_accented_characters($name);
        return strtolower(preg_replace('/-+/', '-', preg_replace('/[^a-zA-Z0-9]/', '-', $name)));
    }

    public static function result($status = true, $message = '', $args = false, $data = '')
    {
        return array('status' 	=> $status,
            'message' 	=> $args ? sprintf($message, $args) : $message,
            'data' 	=> $data
        );
    }

    public static function createFolder($parent = 0, $name = 'Folder Baru')
    {
        $i = 0;
        $oriSlug = self::createSlug($name);
        $oriName = $name;
        $slug = $oriSlug;

        while (ci()->folder_model->count_rows(array('slug' => $slug))) {
            $i++;
            $slug = $oriSlug . '-' . $i;
            $name = $oriName . '-' . $i;
        }

        $insert = array(
            'parent_id' => $parent,
            'name' => $name,
            'slug' => $slug,
        );
        $id = ci()->folder_model->insert($insert);

        $insert['id'] = $id;
        $insert['file_count'] = 0;

        return self::result(true, lang('the_file::file_item_created'), $insert['name'], $insert);
    }

    public static function renameFolder($id = 0, $name)
    {
        $i = 0;
        $original_slug = self::createSlug($name);
        $original_name = $name;
        $slug = $original_slug;
        ci()->db->where('id !=', $id);
        while (ci()->folder_model->count_rows(array('slug' => $slug))) {
            $i++;
            $slug = $original_slug.'-'.$i;
            $name = $original_name.'-'.$i;
        }

        $insert = array(
            'slug' => $slug,
            'name' => $name
        );

        ci()->folder_model->update($insert, ['id' => $id]);

        return self::result(true, lang('the_file::file_item_updated'), $insert['name'], $insert);
    }

    public static function deleteFolder($id = 0)
    {
        $folder = ci()->folder_model->get($id);
        if (! $files = ci()->file_model->get(array('folder_id' => $id)) &&
            ! ci()->folder_model->get(array('parent_id' => $id))) {
            ci()->folder_model->delete($id);
            return self::result(true, lang('the_file::file_item_deleted'), $folder->name);
        }
        else {
            return self::result(false, lang('the_file::file_folder_not_empty'), $folder->name);
        }
    }

    public static function folderTree()
    {
        $folders = array();
        $folderArr = array();

        $allFolder = ci()->folder_model
            ->order_by('name', 'asc')->as_array()->get_all();
        foreach ($allFolder as $row) {
            $folders[$row['id']] = $row;
        }

        foreach ($folders as $row) {
            if (array_key_exists($row['parent'], $folders)) {
                $folders[$row['parent']]['children'][] =& $folders[$row['id']];
            }
            if ($row['parent'] == 0) {
                $folderArr[] =& $folders[$row['id']];
            }
        }

        return $folderArr;
    }

    public function folderContents($parent = 0)
    {
        if ( ! is_numeric($parent))
        {
            $segment = explode('/', trim($parent, '/#'));
            $result = ci()->folder_model->get(array('slug' => array_pop($segment)));

            $parent = ($result ? $result->id : 0);
        }

        $folders = ci()->folder_model->order_by('name', 'asc')
            ->get_all(array('parent_id' => $parent));
        $files = ci()->file_model->order_by('name', 'asc')
            ->set_relationship('folder')
            ->get_all_by(array('folder' => $parent));

        if ($folders) {
            foreach ($folders as &$folder) {
                $folder->fileCount = ci()->file_model->get_count_by(array('folder_id' => $folder->id));
            }
        }

        return self::result(true, null, null, array('folder' => $folders, 'file' => $files, 'parent' => $parent));
    }

    public static function checkDir($path)
    {
        if (is_dir($path) and is_really_writable($path))
        {
            return self::result(true);
        }
        else if (!is_dir($path)) {
            if ( ! @mkdir($path, 0777, true)) {
                return self::result(false, lang('the_file::file_mkdir_error'), $path);
            }
            else {
                $uph = fopen($path . 'index.html', 'w');
                fclose($uph);
            }
        }
        else {
            if ( ! chmod($path, 0777)) {
                return self::result(false, lang('the_file::file_chmod_error'));
            }
        }
    }

    private static function _checkExt($field)
    {
        if ( ! empty($_FILES[$field]['name']))
        {
            $ext		= pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
            $allowed	= ci()->config->item('file_allowed_file_ext', 'the_file');

            foreach ($allowed as $type => $ext_arr) {
                if (in_array(strtolower($ext), $ext_arr)) {
                    self::$_type		= $type;
                    self::$_ext			= implode('|', $ext_arr);
                    self::$_filename	= trim(url_title($_FILES[$field]['name'], 'dash', true), '-');

                    break;
                }
            }

            if ( ! self::$_ext) {
                return self::result(false, lang('the_file::file_invalid_extension'), $_FILES[$field]['name']);
            }
        }
        else if (ci()->method === 'upload') {
            return self::result(false, lang('the_file::file_upload_error'));
        }

        return self::result(true);
    }

    private static function _unlinkFile($file)
    {
        if ( ! isset($file->filename) ) {
            return false;
        }

        @unlink(self::$path . DIRECTORY_SEPARATOR . $file->filename);
        return true;
    }

    public static function getFilePath($id)
    {
        $file = ci()->file_model->get(['id' => $id]);
        if ($file) {
            if ( ! isset($file->fileName) ) {
                return false;
            }

            return self::$path . DIRECTORY_SEPARATOR . $file->fileName;
        }

        return false;
    }

    public static function checkUpload($folderId, $name = false, $field = 'userfile', $width = false, $height = false, $ratio = false, $allowedTypes = false, $alt = null, $replaceFile = false, $description = null, $upconfig_extend = null)
    {
        if (!$check_dir = self::checkDir(self::$path)) {
            return $check_dir;
        }

        if (!$check_cache_dir = self::checkDir(self::$_cachePath)) {
            return $check_cache_dir;
        }

        if (!$check_ext = self::_checkExt($field)) {
            return $check_ext;
        }

        // this keeps a long running upload from stalling the site
        //session_write_close();

        $folder = ci()->folder_model->get(array('id' => $folderId));
        if ($folder) {
            ci()->load->library('upload');
            $upload_config = array(
                'upload_path' => self::$path,
                'file_name' => $replaceFile ? $replaceFile->filename : self::$_filename,
                'encrypt_name' => (ci()->config->item('file_encrypt_filename', 'the_file') && !$replaceFile) ? true : false
            );

            if ($upconfig_extend) {
                $upload_config = array_merge($upload_config, $upconfig_extend);
            }

            // If we don't have allowed types set, we'll set it to the
            // current file's type.
            $upload_config['allowed_types'] = ($allowedTypes) ? $allowedTypes : self::$_ext;
            ci()->upload->initialize($upload_config);
            if (ci()->upload->check_upload($field)) {
                $file = ci()->upload->data();
                header("Connection: close");
                return self::result(true, lang('the_file::file_file_ready_upload'));
            } else {
                $errors = ci()->upload->display_errors(' ', ' ');
                header("Connection: close");
                return self::result(false, $errors);
            }
        } else {
            header("Connection: close");
            return self::result(false, lang('the_file::file_specify_valid_folder'));
        }
    }

    public static function upload($folderId, $name = false, $field = 'userfile', $width = false, $height = false, $ratio = false, $allowedTypes = false, $alt = NULL, $replaceFile = false, $description = null, $upconfig_extend = null)
    {
        if ( ! $check_dir = self::checkDir(self::$path)) {
            return $check_dir;
        }

        if ( ! $check_cache_dir = self::checkDir(self::$_cachePath)) {
            return $check_cache_dir;
        }

        if ( ! $check_ext = self::_checkExt($field)) {
            return $check_ext;
        }

        // this keeps a long running upload from stalling the site
        //session_write_close();

        $folder = ci()->folder_model->get(array('id' => $folderId));
        if ($folder) {
            ci()->load->library('upload');
            $upload_config = array(
                'upload_path' => self::$path,
                'file_name' => $replaceFile ? $replaceFile->filename : self::$_filename,
                'encrypt_name' => (ci()->config->item('file_encrypt_filename', 'the_file') && ! $replaceFile) ? TRUE : FALSE
            );

            if ($upconfig_extend) {
                $upload_config = array_merge($upload_config, $upconfig_extend);
            }

            // If we don't have allowed types set, we'll set it to the
            // current file's type.
            $upload_config['allowed_types'] = ($allowedTypes) ? $allowedTypes : self::$_ext;
            ci()->upload->initialize($upload_config);
            if (ci()->upload->do_upload($field))
            {
                $file = ci()->upload->data();

                $data = array(
                    'folder_id' => (int) $folderId,
                    'user_id' => (int) ci()->currentUser->id,
                    'type' => self::$_type,
                    'name' => $replaceFile ? $replaceFile->name : $name ? $name : $file['orig_name'],
                    'path' => '{{url_site}}files/large/'.$file['file_name'],
                    'description' => $replaceFile ? $replaceFile->description : '',
                    'alt_attribute' => trim($replaceFile ? $replaceFile->altAttr : $alt),
                    'filename' => $file['file_name'],
                    'extension' => $file['file_ext'],
                    'mimetype' => $file['file_type'],
                    'filesize' => $file['file_size'],
                    'width' => (int) $file['image_width'],
                    'height' => (int) $file['image_height'],
                );

                if ($description) {
                    $data['description'] = $description;
                }

                if ($file['is_image'] and ($width or $height))
                {
                    ci()->load->library('image_lib');
                    $config['image_library'] = 'gd2';
                    $config['source_image'] = self::$path. DIRECTORY_SEPARATOR .$data['filename'];
                    $config['new_image'] = self::$path. DIRECTORY_SEPARATOR .$data['filename'];
                    $config['maintain_ratio'] = (bool) $ratio;
                    $config['width'] = $width ? $width : 0;
                    $config['height'] = $height ? $height : 0;
                    ci()->image_lib->initialize($config);
                    ci()->image_lib->resize();

                    $data['width'] = ci()->image_lib->width;
                    $data['height'] = ci()->image_lib->height;
                }

                if($replaceFile) {
                    $file_id = $replaceFile->id;
                    ci()->file_model->update($data, ['id' => $replaceFile->id]);
                }
                else {
                    $data['id'] = substr(md5(microtime() . $data['filename']), 0, 15);
                    $i = 0;
                    while(ci()->file_model->exists($data['id'])) {
                        $data['id'] = substr(md5(microtime() . $data['filename'] . $i++), 0, 15);
                    }
                    $file_id = $data['id'];
                    ci()->file_model->insert($data);
                }

                if ($data['type'] !== 'i') {
                    ci()->file_model->update(['path' => '{{url_site}}files/download/'.$file_id], ['id' => $file_id]);
                }

                /**
                 * todo - if location != local
                 */
                if ($folder->location !== 'local') {}

                header("Connection: close");
                $return = self::result(true, lang('the_file::file_file_uploaded'), $data['name'], array('id' => $file_id) + $data);
                return $return;
            }
            else {
                $errors = ci()->upload->display_errors(' ',' ');
                header("Connection: close");
                return self::result(false, $errors);
            }
        }
        else {
            header("Connection: close");
            return self::result(false, lang('the_file::file_specify_valid_folder'));
        }
    }

    public static function replaceFile($toReplace, $folderId, $name = false, $field = 'userfile', $width = false, $height = false, $ratio = false, $allowedTypes = false, $altAttribute = NULL, $upconfig_extend = null)
    {
        $fileToReplace = ci()->file_model->get(array('id' => $toReplace));
        if ($fileToReplace) {
            self::_unlinkFile($fileToReplace);
            $result = self::upload($folderId, $name, $field, $width, $height, $ratio, $allowedTypes, $altAttribute, $fileToReplace, null, $upconfig_extend);

            // remove files from cache
            if( $result['status']) {
                //md5 the name like they do it back in the thumb function
                $cached_file_name = md5($fileToReplace->fileName) . $fileToReplace->extension;
                $path = ci()->config->item('cache_dir') . 'image_files/';
                $cached_files = glob( $path . '*_' . $cached_file_name );
                foreach($cached_files as $full_path) {
                    @unlink($full_path);
                }
            }

            return $result;
        }

        return self::result(false, lang('the_file::file_item_not_found'));
    }

    public static function deleteFile($id = 0)
    {
        $file = ci()->file_model->get(array('id' => $id));
        if ($file) {
            //ci()->load->model('keywords/keyword_model');
            //ci()->keyword_m->delete_applied($file->keywords);

            ci()->file_model->force_delete(array('id' => $id));
            self::_unlinkFile($file);
            return self::result(true, lang('the_file::file_item_deleted'), $file->name);
        }

        return self::result(false, lang('the_file::file_item_not_found'), $id);
    }

    public static function renameFile($id = 0, $name)
    {
        ci()->file_model->update(['name' => $name], ['id' => $id]);
        return self::result(true, lang('the_file::file_item_updated'), $name, array('id' => $id, 'name' => $name));
    }
}