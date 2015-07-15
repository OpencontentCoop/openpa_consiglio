<?php
$Module = array( 'name' => 'Consiglio' );

$ViewList = array();

$ViewList['test'] = array(
    'functions' => array( 'admin' ),
    'script' => 'test.php',
    'params' => array(),
    'unordered_params' => array()
);

$ViewList['dashboard'] = array(
    'functions' => array( 'use' ),
    'script' => 'dashboard.php',
    'params' => array( 'FactoryIdentifier' ),
    'unordered_params' => array()
);

$ViewList['data'] = array(
    'functions' => array( 'use' ),
    'script' => 'data.php',
    'params' => array( 'FactoryIdentifier', 'ID', 'TemplatePath' ),
    'unordered_params' => array()
);

$ViewList['like'] = array(
    'functions' => array( 'use' ),
    'script' => 'like.php',
    'params' => array(),
    'unordered_params' => array()
);


$FunctionList = array();
$FunctionList['use'] = array();
$FunctionList['admin'] = array();

