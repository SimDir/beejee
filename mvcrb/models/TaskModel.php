<?php defined('ROOT') OR die('No direct script access.');

/**
 * Description of UserModel
 * bjad!G(yKauV1jSYFqvB
 * @author ivank
 */
namespace mvcrb;
class TaskModel extends Model{
    private $TableName;
    public function __construct() {
        parent::__construct();
        $this->TableName = 'task';
    }
    public function GetList($PostData=null) {
        $start = $PostData->start ? $PostData->start :0; 
        $limit = $PostData->limit ? $PostData->limit :10; 
        $List['count'] = $this->count($this->TableName);
        $order=null;
        if (is_array($order)) {
            if ($order['search'] == '') {
                $List['data'] = $this->findAll($this->TableName, 'ORDER BY ' . $order['data'] . ' ' . $order['dir'] . ' LIMIT ' . $start . ', ' . $limit);
            } else {
                $Farr = array(
                    ':find' => '%' . $order['search'] . '%',
                    ':start' => $start,
                    ':limit' => $limit
                );
                $List['data'] = $this->find($this->TableName, ' name LIKE :find OR middlename LIKE :find OR surname LIKE :find OR login LIKE :find OR email LIKE :find LIMIT :start, :limit ', $Farr);
            }
        } else {
            $List['data'] = $this->findAll($this->TableName, 'LIMIT ' . $start . ', ' . $limit);
            
        }
        if ($List) {
            return $List;
        }
        return FALSE;
    }
    public function Create($Data=null) {
        
        if(is_null($Data)) return false;
        
        $task = $this->Dispense($this->TableName);
        $task->import($Data);
        $task->addtaskdatetime = date('Y-m-d H:i:s');

        return $this->store($task);
    }

    public function Edit($Data=null) {
        if(is_null($Data)) return false;
//        $user = $this->dispense($this->TableName);
        $user = $this->findOne($this->TableName, 'id = ?', array($id));


        return $this->store($user);
    }

}
