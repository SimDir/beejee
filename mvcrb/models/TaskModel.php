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
        if(isset($PostData->data) && $PostData->data !== ''){
            $order['data'] = $PostData->data;
            $order['dir'] = $PostData->dir;
        }else{
            $order = null;
        }
        
        
        if (is_array($order)) {
            
            $List['data'] = $this->findAll($this->TableName, 'ORDER BY ' . $order['data'] . ' ' . $order['dir'] . ' LIMIT ' . $start . ', ' . $limit);
            
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
