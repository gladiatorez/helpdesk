<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Faq_model extends MY_Model
{
    public $table = 'faq';
    public $primary_key = 'id';
    public $fillable = [
        'category_id', 'title', 'description', 'slug', 'keywords', 'is_headline', 'active',
        'created_by', 'updated_by', 'deleted_by'
    ];

    public function __construct()
    {
        $this->soft_deletes = FALSE;

        parent::__construct();
    }

    public function create($data)
    {
        if (!array_key_exists('title', $data) || !array_key_exists('slug', $data) ||
            !array_key_exists('description', $data) || !array_key_exists('category_id', $data) ||
            !array_key_exists('active', $data) || !array_key_exists('is_headline', $data)) {
            return false;
        }

        if ($this->with_trashed()->check_unique_field('title', $data['title'])) {
            return false;
        }

        if ($this->with_trashed()->check_unique_field('slug', $data['slug'])) {
            $originalSlug = $data['slug'];
            for ($i = 0; $this->with_trashed()->check_unique_field('slug', $originalSlug); $i++) {
                if ($i > 0) {
                    $data['slug'] = $originalSlug . '-' . $i;
                }
            }
        }

        $this->set_before_create('add_creator');
        $this->set_before_create('add_updater');

        return $this->insert($data);
    }

    public function edit($id, $data)
    {
        if (!array_key_exists('title', $data) || !array_key_exists('slug', $data) ||
            !array_key_exists('description', $data) || !array_key_exists('category_id', $data) ||
            !array_key_exists('active', $data) || !array_key_exists('is_headline', $data)) {
            return false;
        }

        if ($this->with_trashed()->check_unique_field('title', $data['title'], $id)) {
            return false;
        }

        if ($this->with_trashed()->check_unique_field('slug', $data['slug'], $id)) {
            $originalSlug = $data['slug'];
            for ($i = 0; $this->with_trashed()->check_unique_field('slug', $originalSlug, $id); $i++) {
                if ($i > 0) {
                    $data['slug'] = $originalSlug . '-' . $i;
                }
            }
        }

        $this->set_before_update('add_updater');
        return $this->update($data, ['id' => $id]);
    }
}