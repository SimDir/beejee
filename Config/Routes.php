<?php defined('ROOT') OR die('No direct script access.');

return array(
    
    
//    '([0-9]+)' => 'main/index/$1',
//    'product/([0-9]+)' => 'main/product/0/$1',
//    'category/([0-9]+)' => 'main/category/$1',
//    'category/([0-9]+)/product/([0-9]+)' => 'main/product/$1/$2',
//    
    'im' => 'user/index/',
//    'cart/addAjax/([0-9]+)' => 'main/addAjax/$1',
//    'cart/delete/([0-9]+)' => 'main/delete/$1',
//    'cart/checkout' => 'main/checkout',
//    'sitemap' => 'main/sitemap',
//    '404' => 'main/eror404',
//    'index.php' => 'main/redirect',
    '\bpage\b/([0-9]+)' => 'page/page/$1',
    '\bpage\b/([-_a-z0-9]+).html' => 'page/page/$1',
//    '\bpage\b/([-_a-z0-9]+)' => 'site/sefurl/$1',
//    '^id' => 'main/index/$1',
    'forums' => 'index/forums/'
//    '\bmoney\b/' => 'site/money/',
//    'logout' => 'user/logout'
);
