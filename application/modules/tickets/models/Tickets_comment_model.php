<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Tickets_comment_model extends MY_Model
{
    public $table = 'tickets_comments';
    public $primary_key = 'id';
    public $fillable = [
        'ticket_id', 'comments', 'created_by', 'creted_by_ref'
    ];

    public function __construct()
    {
        $this->soft_deletes = FALSE;

        parent::__construct();
    }

    public function getUserEvent($id, $ref)
    {
        $this->load->model($ref);
        $model = explode('/', $ref);
        if ($model[1] == 'user_model') {
            $get = $this->{$model[1]}->fields(array('username', 'email'))
                ->with('profile', array('fields:full_name,nik'))
                // ->with('staff', array('fields:nik'))
                ->get(array('id' => $id));
            if ($get) {
                return array(
                    'id' => $get->id,
                    'nik' => $get->profile->nik,
                    'fullName' => $get->profile->full_name,
                );
            } else {
                return null;
            }
        } else if ($model[1] == 'informer_model') {
            $get = $this->{$model[1]}->fields(array('id','full_name','nik'))->get(array('id' => $id));
            if ($get) {
                return array(
                    'id' => $get->id,
                    'nik' => $get->nik,
                    'fullName' => $get->full_name,
                );
            } else {
                return null;
            }
        }

        return null;
    }

    public function getAllByTicket($ticketId, $lastId = null, $offset = 0, $limit = 10)
    {
        if (!empty($lastId)) {
            $this->_database->where('comment.id <', $lastId);
        }

        $query = $this->_database->select([
                'comment.id',
                'comment.ticket_id',
                'comment.comments',
                'comment.created_at',
                'comment.created_by',
                'comment.creted_by_ref',
                'comment.updated_at',
                'profile.full_name AS created_by_staff',
                'informer.full_name AS created_by_infomer'
            ])
            ->from($this->table . ' comment')
            ->join('user_profile profile', 'comment.created_by = profile.user_id', 'LEFT')
            ->join('informer informer', 'comment.created_by = informer.user_id', 'LEFT')
            ->where('comment.ticket_id', $ticketId)
            ->order_by('comment.id', 'DESC')
            ->limit($limit, $offset)
            ->get();
        
        $totalRows = $this->count_rows(['ticket_id' => $ticketId]);
        return [
            'rows' => $query->result(),
            'total' => $totalRows,
        ];
    }

    public function getById($id)
    {
        $query = $this->_database->select([
                'comment.id',
                'comment.ticket_id',
                'comment.comments',
                'comment.created_at',
                'comment.created_by',
                'comment.creted_by_ref',
                'comment.updated_at',
                'profile.full_name AS created_by_staff',
                'informer.full_name AS created_by_infomer'
            ])
            ->from($this->table . ' comment')
            ->join('user_profile profile', 'comment.created_by = profile.user_id', 'LEFT')
            ->join('informer informer', 'comment.created_by = informer.user_id', 'LEFT')
            ->where('comment.id', $id)
            ->get();
        
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return false;
    }

    public function create($data)
    {
        if (isset(ci()->currentUser)) {
            $data['created_by'] = ci()->currentUser->id;
            $data['creted_by_ref'] = 'users/user_model';
        } else {
            $data['created_by'] = ci()->_currentUserFrontend->id;
            $data['creted_by_ref'] = 'informer/informer_model';
        }

        return $this->insert($data);
    }
}