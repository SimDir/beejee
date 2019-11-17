<?php defined('ROOT') OR die('No direct script access.');

/**
 *
 * @author ivan kolotilkin
 * 
 */
namespace mvcrb;

class IndexController extends Controller{

    public function IndexAction() {
        $this->View->title ='Задачник';
        $this->View->content = $this->View->execute('tasklist.html');
        return $this->View->execute('index.html');
    }

}
