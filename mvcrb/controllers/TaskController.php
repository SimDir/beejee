<?php defined('ROOT') OR die('No direct script access.');

namespace mvcrb;

/**
 * Description of TaskController
 *
 * @author я
 */
use Faker;
class TaskController extends Controller{
//    public function IndexAction() {
//        for($i = 1; $i <= 250; $i++){
//        $faker = Faker\Factory::create('Ru_RU');
//        $Data['text'] = "task $i ".$faker->words(30,true);
//        $Data['email'] = $faker->email();
//        $Data['name'] = $faker->words(3,true);
//        $Model = new TaskModel();
//        $Model->Create($Data);
//        }
//        return $i;
//    }
    
    public function AddAction() {
        $this->View->title = 'Задачник - добавить задачу';
        if ($this->POST) return $this->CreateTask();
        $this->View->content = $this->View->execute('task.html');
        return $this->View->execute('index.html');
    }
    private function CreateTask() {
        $PostData = json_decode($this->REQUEST);
        $Data=[];
        if(isset($PostData->TaskText) && $PostData->TaskText !== ''){
//            $Data['text'] = strip_tags($PostData->TaskText, '<p><a><small>');
            $Data['text'] = preg_replace(["'<script[^>]*?>.*?</script>'si"], '', $PostData->TaskText);
        }else{
            $Error[]='Введите текст задачи';
        }
        if(isset($PostData->TaskEmail) && $PostData->TaskEmail !== ''){
            $email= strip_tags($PostData->TaskEmail);
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $Data['email'] = $email;
            }else{
                $Error[]= "E-mail адрес '$email' указан не верно.\n";
            }
        }else{
            $Error[]='Введите Email';
        }
        if (isset($PostData->TaskName) && $PostData->TaskName !== '') {
            $Data['name'] = strip_tags($PostData->TaskName);
        } else {
            $Error[]='Имя задачи не заполнено';
        }
        if (isset($PostData->TaskStatus)) {
            $Data['status'] = boolval($PostData->TaskStatus);
        }
        if(isset($Error)){
        $RetDada['ErrData']=$Error;
            return $RetDada;
        }
        $Model = new TaskModel();
        return $Model->Create($Data);

    }
    
    public function GetAction() {
        $Model = new TaskModel();
        return $Model->GetList(json_decode($this->REQUEST));
    }
}
