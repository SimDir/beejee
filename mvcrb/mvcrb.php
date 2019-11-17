<?php defined('ROOT') OR die('No direct script access.');
/**
 * Главный класс всего приложения
 * 
 */
namespace mvcrb;
function dd($str){
    var_dump($str);
    die();
}

class mvcrb {
    public static $ErrorMessage;
    
    Private static $globalConfig = [];
    private static $ExecRetVal;
    /*
     * Переменные роутинга 
     */
    Private static $URI = ''; // Строка УРЛ запроса  site.com/Controller/Action/Param1/Param2/Param3/ и так далее
    Private static $ControllerName; // Имя выполняемого контроллера <Controller>
    Private static $ActionName; // Имя выполняемого метода <Action>
    Private static $ControllerFile; // подключаемый фаил контроллера <...\ControllerPath\*Name*Controller.php>
    Private static $ParametersArray; // массив параметров которые пришли в УРЛ строке
    
    public static function ErrorMessage(){return self::$ErrorMessage;}
    public static function Config(){return self::$globalConfig;}
    
    public static function initWhoops(){
        $whoops = new \Whoops\Run;
        $whoops_pretty_page_handler = new \Whoops\Handler\PrettyPageHandler();
        $whoops_pretty_page_handler->setEditor('sublime');
        $whoops->pushHandler($whoops_pretty_page_handler);
        $monolog_multiline_formatter = new \Monolog\Formatter\LineFormatter(null, null, true);
        $monolog_error_log_handler = new \Monolog\Handler\ErrorLogHandler();
        $monolog_error_log_handler->setFormatter($monolog_multiline_formatter);
        $monolog_logger_error_log = new \Monolog\Logger('whoops_logger', [$monolog_error_log_handler]);
        $whoops_plain_text_handler = new \Whoops\Handler\PlainTextHandler();
        $whoops_plain_text_handler->loggerOnly(true);
        $whoops_plain_text_handler->setLogger($monolog_logger_error_log);
        $whoops->pushHandler($whoops_plain_text_handler);
        $monolog_browser_console_handler = new \Monolog\Handler\BrowserConsoleHandler();
        $monolog_browser_console_handler->setFormatter($monolog_multiline_formatter);
        $monolog_browser_console_logger = new \Monolog\Logger('whoops_browser_console_logger', [$monolog_browser_console_handler]);
        $whoops_plain_text_handler2 = new \Whoops\Handler\PlainTextHandler();
        $whoops_plain_text_handler2->loggerOnly(true);
        $whoops_plain_text_handler2->setLogger($monolog_browser_console_logger);
        $whoops->pushHandler($whoops_plain_text_handler2);
        $whoops->register();
    }
    /**
     * Основной метод запускает все приложение. так называемая точка входа
     * 
     */
    public static function Run() {
        self::initWhoops();
        
        self::SetupConfig();
        
        spl_autoload_register(__CLASS__ . '::AutoLoadClassFile');

        self::GetControllerAndAction();

        self::$ExecRetVal = self::Exec(self::$ControllerName, self::$ActionName, self::$ParametersArray);

        if (is_string(self::$ExecRetVal)) {
            echo self::$ExecRetVal;
        }else{ 
            if (!headers_sent())
                header('Content-type: application/json');
            echo json_encode(self::$ExecRetVal);
        }

    }
    public static function Exec($Controller ='' ,$Action='',$Param=[]) {
        
        $ctrl = 'mvcrb\\'.$Controller;
        
        if (class_exists($ctrl)) {
            $objectCtrl = new $ctrl();

            if (method_exists($objectCtrl, $Action)) {
                
                if (count($Param)) {
                    return call_user_func_array(array($objectCtrl, $Action), $Param);
                } else {
                    return call_user_func(array($objectCtrl, $Action));
                }
            }
            if (!headers_sent()) { 
                header("HTTP/1.1 405 Method Not Allowed");
                header("Status: 405 Method Not Allowed");
            }
            
            return "<h1>404 Method Not Allowed</h1><hr><h5>". __METHOD__ ." <b style=\"color: red;\">$Controller::$Action()</b> Не имеет метода</h5>";
        }
        if (!headers_sent()) { 
            header("HTTP/1.1 523 Origin Is Unreachable");
            header("Status: 523 Origin Is Unreachable");
        }
        
        return "<h1>523 Not Found</h1><hr><h5>Exec:: нет исполнительного контроллера $Controller</h5>";
        
    }
    /**
     * функция получения запроса который пришел от пользователя приложением
     * @return String
     */
    private static function GetURI() {
//        $ee=2/0;
        $pathInfo = filter_input(INPUT_SERVER, 'PATH_INFO');

        if ($pathInfo) {
            $path = $pathInfo;
        } else {
            $requestURI = filter_input(INPUT_SERVER, 'REQUEST_URI');
    //            var_dump($requestURI);
            if (strpos($requestURI, '?') !== false) {
                $requestURI = substr($requestURI, 0, strpos($requestURI, '?'));
            }
            $base = filter_input(INPUT_SERVER, 'BASE');
            if ($base) {
                $path = substr($requestURI, strlen($base));
            } else {
                $path = $requestURI;
            }
        }

        $path = trim($path);
        if (!$path) {
            $path = '/';
        }
//        var_dump($requestURI);
        $path = parse_url($path);
        self::$URI = trim($path['path'], '/');
        return self::$URI;
    }
    /**
     * Получаем контроллер и метод.
     * данная функция находит в УРЛ тот контроллер и метот на который пршол запрос
     * заполныет переменные роутинга
     * и если необходимо настраевает пришедший в УРЛ запрос
     * @return Boolean
     */
    private static function GetControllerAndAction() {
        $access = false;
        self::GetURI();
        $cfg = self::$globalConfig['App_Config_Dir'].self::$globalConfig['App_Router_Config_File'];
        if (file_exists($cfg)) {
            $routes = include($cfg);
        }else{
//            throw new \Exception(__METHOD__ . " Конфигурационный фаил роутинга $cfg не найден. продолжить невозможно");
//            return false;
            $routes = [];
        }
        
        // проверяю запрос на соответствие регулярному выражению
        foreach ($routes as $uriPattern => $path) {
            if (!preg_match("~$uriPattern~", self::$URI)) {
                continue;
            }
            // получаем внутренний путь из внешнего согласно правилам маршрутизации
            $access = preg_replace("~$uriPattern~", $path, self::$URI);
        }

        if (!$access) {
            if (empty(self::$URI)) {
                $access = self::$globalConfig['Router_Default_Controller'] . "/" . self::$globalConfig['Router_Default_Action']; //
            } else {
                $access = self::$URI;
            }
        }
        $segments = explode('/', $access);

        $dirForControllers = self::$globalConfig['App_Controllers_Dir'];
        $controlerName = ucfirst(array_shift($segments)).'Controller';
        self::$ControllerName = $controlerName;

        $controllerFile = $dirForControllers . $controlerName . '.php';


        $action = ucfirst(array_shift($segments));

        if (empty($action)) {
            $action = ucfirst(self::$globalConfig['Router_Default_Action']);
//            var_dump($this->globalConfig['Router_Default_Action']);
        }
        $actionName =  $action.'Action';
        self::$ActionName = $actionName;

        self::$ParametersArray = $segments;

        if (!file_exists($controllerFile)) {
//            if (file_exists($dirForControllers) && is_dir($dirForControllers)) {
//                die('Директория с контроллерами не найдена');
//            }
            $dirArray = scandir($dirForControllers);
            foreach ($dirArray as $da) {
                if (is_dir($dirForControllers . $da)) {

                    $controllerFile = $dirForControllers . $da . DIRECTORY_SEPARATOR . $controlerName . '.php';

                    if (file_exists($controllerFile)) { // если в текущей подпапке есть контроллер
                        self::$ControllerFile = $controllerFile;
                        return $controllerFile; // и выходим из функции
                    }
                }
            }
            
//            header('HTTP/1.0 404 Not Found');
//            exit('Нет контроллера '.$controlerName);
            throw new \Exception(__METHOD__ . ' [ERROR:404] фаил Контроллера ' . $controlerName . '.php не найден');
//            return FALSE;
        } else {
            self::$ControllerFile = $controllerFile;
            return $controllerFile;
        }
//        require_once ($controllerFile);
        throw new \Exception(__METHOD__ . ' [ERROR:404] фаил Контроллера ' . $controlerName . '.php не найден');
//        return FALSE;
    }
    /**
     * настраиваем основную конфигурацию ядра системы
     */
    private static function SetupConfig(){
        self::$globalConfig['App_Name'] = 'mvcrb';
        self::$globalConfig['App_Dir'] = APP;
        self::$globalConfig['App_lib_Dir'] = self::$globalConfig['App_Dir'] . 'libs' . DIRECTORY_SEPARATOR;
        self::$globalConfig['App_Config_Dir'] = CONFIG_DIR;
        self::$globalConfig['App_Controllers_Dir'] = self::$globalConfig['App_Dir'] . 'controllers' . DIRECTORY_SEPARATOR;
        self::$globalConfig['App_Models_Dir'] = self::$globalConfig['App_Dir'] . 'models' . DIRECTORY_SEPARATOR;
        
//        $accept_languages = filter_input(INPUT_SERVER, "HTTP_ACCEPT_LANGUAGE", FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
//        $locale = locale_accept_from_http($accept_languages);
//        echo $locale;
        self::$globalConfig['App_Templates_Dir'] = TEMPLATE_DIR;// . 'default' . DIRECTORY_SEPARATOR;
        
        if (!file_exists(self::$globalConfig['App_Config_Dir'] . 'AutoLoader.php')) {
//            exit('AutoLoaderConfig.php не найден');
            self::$globalConfig['App_Clas_Loader_Dir_Array'] = ['libs','models','controllers'];
        } else {
            self::$globalConfig['App_Clas_Loader_Dir_Array'] = include_once self::$globalConfig['App_Config_Dir'] . 'AutoLoader.php';
        }
        
        self::$globalConfig['App_Router_Config_File'] = 'Routes.php';
        self::$globalConfig['Router_Default_Controller'] = 'index';
        self::$globalConfig['Router_Default_Action'] = 'index';
//        $str_value = serialize(self::$globalConfig);
//        echo $str_value;
    }
    
    /**
     * Автоматическая загрузка классов
     * @param type $className
     * @return boolean
     */
    private static function AutoLoadClassFile($className) {
        $parts = explode('\\', $className);
        $class = end($parts);
//        file_put_contents(SITE_DIR.'alog.txt', $class.PHP_EOL,FILE_APPEND);
        $classFile = self::$globalConfig['App_lib_Dir'] . $class . '.php';
        if (file_exists($classFile)) {
            include_once $classFile;
            return $classFile;
        }
        return self::LoadClassFileForAllDir($class);
    }

    private static function LoadClassFileForAllDir($className) {
//        echo $className;
//        self::$loadClassArray[] = $className;
        $dirArr = self::$globalConfig['App_Clas_Loader_Dir_Array'];
        $appDir = self::$globalConfig['App_Dir'];
        foreach ($dirArr as $value) {

            $classFile = self::SearchFile($className . '.php', $appDir . $value);

            if (file_exists($classFile)) {
                include_once $classFile;
                return $classFile;
            }
        }
        return FALSE;
    }

    /**
     * Поиск файла по имени во всех папках и подпапках
     * @param string $fileName - искомый файл
     * @param string $folderName - пусть до папки
     */
    public static function SearchFile($fileName, $folderName) {
        // перебираем пока есть файлы
        if(!is_dir($folderName)){
            return false;
        }
        $dirArray = scandir($folderName);

        foreach ($dirArray as $file) {

            if ($file != "." && $file != "..") {

                // если файл проверяем имя
                if (is_file($folderName . DIRECTORY_SEPARATOR . $file)) {
                    // если имя файла искомое,
                    // то вернем путь до него
                    if ($file == $fileName) {
                        return $folderName . DIRECTORY_SEPARATOR . $file;
                    }
//                    echo $folderName.'\\'.$file.'<br>';
                }
                // если папка, то рекурсивно
                // вызываем SearchFile
                if (is_dir($folderName . DIRECTORY_SEPARATOR . $file)) {
                    $retVal = self::SearchFile($fileName, $folderName . DIRECTORY_SEPARATOR . $file);
                    if ($retVal) { // если фуекция что-то вернула то выходим
                        return $retVal;
                    }
                }
            }
        }
    }

    public static function Redirect($url, $permanent = false) {
        if (headers_sent() === false) {
            header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
        }
//        echo '<script type="text/javascript">window.location = "http://www.google.com/"</script>';
//        exit();
    }

}