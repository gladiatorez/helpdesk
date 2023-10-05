<?php

/**
 * Class Closure_table
 *
 *  @link  https://gist.github.com/dazld/2174233
 * sql schema:
 * CREATE TABLE `closures` (
 *   `id` int(11) NOT NULL AUTO_INCREMENT,
 *   `ancestor` int(11) NOT NULL,
 *   `descendant` int(11) NOT NULL,
 *   `lvl` int(11) NOT NULL,
 *   PRIMARY KEY (`id`)
 * );
 */
class Closure_table
{
    public $table;
    public $path_table = 'paths';
    public $primary_key = 'id';
    public $CI;

    public function __construct($config = array())
    {
        $this->CI =& get_instance();

        if (isset($config['table'])) {
            $this->table = $config['table'];
        }

        if (isset($config['primary_key'])) {
            $this->primary_key = $config['primary_key'];
        }

        isset($config['path_table'])
            ? $this->path_table = $config['path_table']
            : $this->path_table = ci()->db->dbprefix($this->table . '_paths');
    }

    private function _afterAdd($newId, $newParentId)
    {
        $sql  = 'INSERT INTO ' . $this->path_table . ' (ancestor_id, descendant_id, path_length) ' .
                'SELECT ancestor_id, ' . $newId . ', path_length + 1 ' .
                'FROM ' . $this->path_table .' WHERE descendant_id = ' . $newParentId . ' ' .
                'UNION ALL SELECT ' . $newId . ', ' . $newId . ',0;';

        $result = $this->CI->db->query($sql);
        return $result;
    }

    public function add($data, $transaction = false)
    {
        /*if (!isset($data['parent_id'])) {
            show_error('$data parameter harus memiliki parent_id');
        }*/

        $transaction && $this->CI->db->trans_start();

        $this->CI->db->insert($this->table, $data);
        $lastId = $this->CI->db->insert_id();
        if (!$lastId) {
            return false;
        }

        $this->_afterAdd($lastId, $data['parent_id'] ? $data['parent_id'] : $lastId);

        if ($transaction) {
            $this->CI->db->trans_complete();
            if ($this->CI->db->trans_status() === FALSE) {
                $this->CI->db->trans_rollback();
                return false;
            }
        }

        return $lastId;
    }

    public function _afterUpdate($id, $newParentId)
    {
        $sqlDelete  = 'DELETE a FROM '. $this->path_table .' AS a '.
            'JOIN '. $this->path_table .' AS d ON a.descendant_id = d.descendant_id '.
            'LEFT JOIN '. $this->path_table .' AS x ON x.ancestor_id = d.ancestor_id AND x.descendant_id = a.ancestor_id '.
            'WHERE d.ancestor_id = '. $id .' AND x.ancestor_id IS NULL;';

        $this->CI->db->query($sqlDelete);

        $sqlInsert = 'INSERT '. $this->path_table .' (ancestor_id, descendant_id, path_length) '.
            'SELECT supertree.ancestor_id, subtree.descendant_id, supertree.path_length + subtree.path_length + 1 '.
            'FROM '. $this->path_table .' AS supertree '.
            'JOIN '. $this->path_table .' AS subtree '.
            'WHERE subtree.ancestor_id = '. $id .' AND supertree.descendant_id = "'. $newParentId .'";';

        $result = $this->CI->db->query($sqlInsert);
        return $result;
    }

    public function update($id, $data = array(), $transaction = false)
    {
        $transaction && $this->CI->db->trans_start();

        $queryOld = $this->CI->db
            ->where(array($this->primary_key => $id))
            ->get($this->table);
        if ($queryOld->num_rows() <= 0) {
            return false;
        }
        $resOld = $queryOld->row();

        $this->CI->db->update($this->table, $data, array($this->primary_key => $id));

        if ($resOld->parent_id != $data['parent_id']) {
            $this->_afterUpdate($id, $data['parent_id']);
        }

        if ($transaction) {
            $this->CI->db->trans_complete();
            if ($this->CI->db->trans_status() === FALSE) {
                $this->CI->db->trans_rollback();
                return false;
            }
        }

        return true;

    }

    /**
     * Delete Method
     * update status on field is_deleted in table
     *
     * @param $id
     * @param int $delete
     * @return mixed
     */
    public function delete($id, $delete = 1)
    {
        $sql = 'UPDATE '. $this->table .' AS d '.
            'JOIN '. $this->path_table .' AS p ON d.`id` = p.`descendant_id` '.
            'JOIN '. $this->path_table .' AS crumbs ON crumbs.`descendant_id` = p.`descendant_id` SET d.`is_deleted` = '. $delete .' '.
            'WHERE p.`ancestor_id` = '. $id .';';

        $query = $this->CI->db->query($sql);
        return $query;
    }

    private function _querySubTreeById($id, $extraSelect = null, $extraJoin = null, $extraWhere = null, $orderPath = 'DESC')
    {
        $this->CI->db->select(array(
            'd.id', 'd.is_deleted', 'd.parent_id', 'CONCAT(REPEAT("-", p.path_length), profile.full_name) AS tree', 'p.path_length',
            'GROUP_CONCAT(crumbs.`ancestor_id` ORDER BY crumbs.path_length '.$orderPath.') AS breadcrumbs'
        ));

        if ($extraSelect) {
            $this->CI->db->select($extraSelect);
        }

        $this->CI->db->from($this->table .' AS d');
        $this->CI->db->join($this->path_table .' AS p', 'd.id = p.descendant_id');
        $this->CI->db->join($this->path_table .' AS crumbs', 'crumbs.descendant_id = p.descendant_id');
        $this->CI->db->join('user_profile' .' AS profile', 'd.user_id = profile.user_id');
        $this->CI->db->where('p.ancestor_id', $id);
        $this->CI->db->where('d.is_deleted', '0');
        $this->CI->db->group_by('d.id');
        $this->CI->db->order_by('breadcrumbs');

        if ($extraWhere) {
            $this->CI->db->where($extraWhere);
        }

        if ($extraJoin) {
            $this->CI->db->join($extraJoin['table'], $extraJoin['condition'], $extraJoin['type']);
        }

        $query = $this->CI->db->get();
        if ($query->num_rows() <= 0) {
            return false;
        }

        return $query;
    }

    public function getAllNode($id, $extraSelect = null, $extraJoin = null, $extraWhere = null, $orderPath = 'DESC')
    {
        $query = $this->_querySubTreeById($id, $extraSelect, $extraJoin, $extraWhere, $orderPath);
        if (!$query) {
            return false;
        }

        return $query->result();
    }

    public function getNode($id, $orderPath = 'DESC')
    {
        $query = $this->_querySubTreeById($id, $orderPath);
        if (!$query) {
            return false;
        }

        return $query->row();
    }
}