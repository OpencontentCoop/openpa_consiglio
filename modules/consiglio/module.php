<?php
$Module = array( 'name' => 'Consiglio' );

$ViewList = array();

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


$FunctionList = array();
$FunctionList['use'] = array();

//moduli fake solo per i permessi in visualizzazione
$FunctionList['persone_coinvolte'] = array();
$FunctionList['presenze'] = array();
$FunctionList['votazioni'] = array();


