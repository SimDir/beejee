<?php defined('ROOT') OR die('No direct script access.');

/**
 * Description of UserModel
 * bjad!G(yKauV1jSYFqvB
 * @author ivank
 */
namespace mvcrb;
class UserModel extends Model{
    private $TableName;
    public function __construct() {
        parent::__construct();
        Session::init();
        $this->TableName = 'user';
    }
    public function GetCurrentUser() {
        $var = Session::get('LoggedUser');
        $AnonimUser=['Name'=>'anonim','login'=>'anonim','role'=>0];

        if($var){
            $user = $this->findOne($this->TableName, 'email = ?', array($var['email']));

            if($user){
                $ret = $user->export();
                unset($ret["password"]);
                return $ret;
            }
            return $AnonimUser;
        }
        
        return $AnonimUser;
    }
    public function login($email, $password) {
        $user = $this->findOne($this->TableName, 'email = ?', array($email));
        if (!$user) {
            $user = $this->findOne($this->TableName, 'login = ?', array($email));
        }
        if ($user) {
            //логин существует
            if (password_verify($password, $user->password)) {
                //если пароль совпадает, то нужно авторизовать пользователя

                $user->lastlogin = date('Y-m-d H:i:s');
                $this->store($user);
                $VarUser = $user->export();
                unset($VarUser['password']); // убираем хеш пароля.
                Session::set('LoggedUser', $VarUser);
                return $VarUser;
            } 
        }
        
        return FALSE;
    }
    


}
