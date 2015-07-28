<?php
$Module = array( 'name' => 'Consiglio' );

$ViewList = array();

$ViewList['test'] = array(
    'functions' => array( 'admin' ),
    'script' => 'test.php',
    'params' => array(),
    'unordered_params' => array()
);

$ViewList['move'] = array(
    'functions' => array( 'admin' ),
    'script' => 'move.php',
    'params' => array( 'FactoryIdentifier', 'ID' ),
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

$ViewList['cruscotto_seduta'] = array(
    'functions' => array( 'cruscotto_seduta' ),
    'script' => 'cruscotto_seduta.php',
    'params' => array( 'SedutaID', 'Action', 'ActionParameters' ),
    'unordered_params' => array()
);

$ViewList['monitor_sala'] = array(
    'functions' => array( 'monitor_sala' ),
    'script' => 'monitor_sala.php',
    'params' => array( 'SedutaID' ),
    'unordered_params' => array()
);




$FunctionList = array();
$FunctionList['use'] = array();
$FunctionList['admin'] = array();
$FunctionList['cruscotto_seduta'] = array();
$FunctionList['monitor_sala'] = array();


