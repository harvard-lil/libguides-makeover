<?php

$f3=require('lib/base.php');

$f3->config('api/config.ini');

$f3->set('AUTOLOAD','api/; web/;');

$f3->route('GET /api/guide', 'Grab->guide');
$f3->route('GET /api/built', 'Grab->guideBuilt');

$f3->route('GET /', function($f3) {
    $view=new View;
    echo $view->render('web/index.html');
});

$f3->route('GET /guide/@name', function($f3, $params) {
    $f3->set('name',$params['name']);
    $f3->set('www_root',$f3->get('RESEARCH_HOME'));

    $view=new View;
    echo $view->render('web/guide.html');
});

$f3->run();

?>