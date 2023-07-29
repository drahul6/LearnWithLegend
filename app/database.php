<?php


use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule();

$capsule->addConnection([
    'driver'    => $_ENV['DB_CONNECTION'],
    'host'      => $_ENV['DB_HOST'],
    'database'  => $_ENV['DB_DATABASE'],
    'username'  => $_ENV['DB_USERNAME'],
    'password'  => $_ENV['DB_PASSWORD'],
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
    'strict'    => true,
], 'default');

$capsule->addConnection([
    'driver'    => $_ENV['DB_WALLET_CONNECTION'],
    'host'      => $_ENV['DB_WALLET_HOST'],
    'database'  => $_ENV['DB_WALLET_DATABASE'],
    'username'  => $_ENV['DB_WALLET_USERNAME'],
    'password'  => $_ENV['DB_WALLET_PASSWORD'],
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
    'strict'    => true,

], 'wallet');


/* 
$capsule->addConnection([
    'driver'    => $_ENV['WP_DB_CONNECTION'],
    'host'      => $_ENV['WP_DB_HOST'],
    'database'  => $_ENV['WP_DB_DATABASE'],
    'username'  => $_ENV['WP_DB_USERNAME'],
    'password'  => $_ENV['WP_DB_PASSWORD'],
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
    'strict'    => true,

], 'wordpress'); */



$capsule->setAsGlobal();  //this is important
$capsule->bootEloquent();
