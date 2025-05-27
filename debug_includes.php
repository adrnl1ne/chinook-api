<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Included Files</h1>";
echo "<pre>";
var_dump(get_included_files());
echo "</pre>";