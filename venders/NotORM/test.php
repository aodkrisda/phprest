<?php 
require('lib.php');
$orm=NotORM::getInstance();

$t=$orm->user()->where('user_id','SSS22')->fetch();

$t->fetch();
var_dump($t->toArray());
