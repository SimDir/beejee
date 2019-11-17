<?php defined('ROOT') OR die('No direct script access.');

/**
 * Класс для работы с шаблонами
 * 
 * view
 * 
 */
namespace mvcrb;
class View {

    private $vars = array();
    public $TplDir = TEMPLATE_DIR;
    public function __construct() {
        $this->vars['headcssjs']='';
        $this->vars['bodycssjs']='';
    }
    public function __invoke($val,$TplDir=false) {
        return $this->execute($val,$TplDir);
    }
    public function __set($name, $value) {
        $this->vars[$name] = $value;
//        var_dump($this->vars);
    }

    public function __get($name) {
        if (isset($this->vars[$name])) {

            return $this->vars[$name];
        }
//        return array();
        return FALSE;
    }
    public function __isset($name) {
        if (isset($this->vars[$name]) && !empty($this->vars[$name])) {
            return true;
        }
        return false;
    }
    public function AddCss($stylesheet,$OnTop=true) {
        if($OnTop){
            $this->vars['headcssjs'].="<link rel=\"stylesheet\" href=\"$stylesheet\">".PHP_EOL;
        }else{
            $this->vars['bodycssjs'].="<link rel=\"stylesheet\" href=\"$stylesheet\">".PHP_EOL;
        }
    }
    public function VarGet($name) {
        if (isset($this->vars[$name])) {

            return $this->vars[$name];
        }
        return FALSE;
    }
    public function VarSetArray($Array) {
        if(is_array($Array)){
            foreach ($Array as $key => $value) {
                $this->vars[$key] = $value;
            }
        }
        
    }


    public function assign($name, $value) {
        if (isset($this->vars[$name]) && is_array($this->vars[$name])) {
            $this->vars[$name] = array_merge($this->vars[$name], (array) $value);
        } else {
            $this->vars[$name] = $value;
        }
    }
    /**
     * дописать регулярок!!!!!
     */
    public function compress($code) {
        //,'#/\*(?:[^*]*(?:\*(?!/))*)*\*/#','/[\s]+/' ,'/\/\/(.*)[\r\n]/'
        return  preg_replace(array( '/<!--(.*)-->/Uis','#/\*(?:[^*]*(?:\*(?!/))*)*\*/#'), '', $code); // '/<!--(.*)-->/Uis','\<![ \r\n\t]*(--([^\-]|[\r\n]|-[^\-])*--[ \r\n\t]*)\>','/[\s]+/'  |,'#/\*(?:[^*]*(?:\*(?!/))*)*\*/#'|
        
    }
    public function execute($path,$TplDir=false) {
        if($TplDir){
            $this->TplDir=$TplDir;
        }
        if (!file_exists($this->TplDir . $path)) {
            $code = '<p><b>Error: </b>'.__METHOD__. "('$path')</p>Нет файла <strong>$path</strong> для подключения в <b>$this->TplDir</b>";
            return $code;
        }
        ob_start();
        include $this->TplDir . $path;
        $code = ob_get_contents();
        ob_end_clean();
//        return $code;
        $code = $this->Code($code);
        $code = $this->compress($code);
//        echo $code;
        return $code;
    }

    public function Code($code) {
        preg_match_all('/<{(.*?)}>/', $code, $varibles, PREG_SET_ORDER);

        foreach ($varibles as $value) {
            
            if (preg_match("/Controller(\(.*\))/i", $value[1],$matches)) {
//                dd($matches);
                $ControllerAction = trim($matches[1], '()');
                $ArrCtrlAct = explode(':', $ControllerAction);
                $code = str_replace($value[0], mvcrb::Exec(ucfirst($ArrCtrlAct[0]).'Controller', ucfirst($ArrCtrlAct[1]).'Action'), $code);
            }elseif(preg_match("/View(\(.*\))/i", $value[1],$matches)){
                $ViewHtml = trim($matches[1], '()');
                $ViewHtml =trim($ViewHtml);
                $tmpDirView = $this->TplDir;
                $this->TplDir = TEMPLATE_DIR;
                $code = str_replace($value[0], $this->execute($ViewHtml), $code);
                $this->TplDir =$tmpDirView;
            }else {
                $tplVar = $this->VarGet(trim($value[1], ' '));
                $code = preg_replace("/<{($value[1])}>/", $tplVar, $code);
            }

        }

        return $code;
    }

    public function SetWivePath($path) {
        $this->TplDir = $path;
//        echo $path;
    }

}
