<?php
/**
 * Created for Amidex IT.
 * User: Munna Khan
 * Date: 8/23/2017
 * Time: 7:37 PM
 */
include ('Engine/server.php');
use \Engine\server as server;
define('WS_HOST', '192.168.199.7');
define('WS_PORT', '9000');
define('WS_SCRIPT', WS_HOST.":".WS_PORT);


$mk=new server(WS_HOST,WS_PORT);
$mk->run();