<?php

/**
 * Description of UserController
 *  
 * @author ivan kolotilkin
 */
namespace mvcrb;
class UserController extends Controller{
    
    private $User;
    
    public function __construct() {
        parent::__construct();
        $this->User = new UserModel();
    }
    
    public function IndexAction() {
        $UserVars = $this->User->GetCurrentUser();
        if($UserVars['role']==0){
            return mvcrb::Redirect('/user/login');
        }

        $this->View->VarSetArray($UserVars);
       
        $email = $UserVars['email'];
        $default = "http://rusodality.ru/img/gerbrr.png";
        $size = 256;
        $this->View->GravUrl = "https://www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "?d=" . urlencode( $default ) . "&s=" . $size;

        $this->View->content = $this->View->execute('usercard.html');
        return $this->View->execute('index.html');
    }
    public function LogoutAction() {
        Session::destroy();
        return mvcrb::Redirect('/user/login');
    }
    public function LoginAction() {
        if ($this->User->GetCurrentUser()['role'] > 0) {
            return mvcrb::Redirect('/user');
        }
        $this->View->title ='Вход пользователя';
        if ($this->POST) {
            $user= json_decode($this->REQUEST);
            return $this->User->login($user->email, $user->password);;
        } 
        $this->View->content =  $this->View->execute('FormLogin.html');
        return $this->View->execute('index.html');
    }
    public function GetAction() {
        if ($this->POST)
            return $this->User->GetCurrentUser();
        return mvcrb::Redirect('/user');
    }
    
}
