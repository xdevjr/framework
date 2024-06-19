<?php
session_start();

require realpath("../vendor/autoload.php");

use core\library\router\Router;

require root("/app/routes/web.php");
require root("/app/routes/api.php");

Router::start();