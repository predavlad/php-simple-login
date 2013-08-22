<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
spl_autoload_register('autoloader');

define('CLASS_FOLDER', 'includes/classes');
define('DS', DIRECTORY_SEPARATOR);

define('DB_USER', 'root');
define('DB_PASS', 'inno');
define('DB_NAME', 'cristi');

// defines for hash algorithm
define("PBKDF2_HASH_ALGORITHM", "sha256");
define("PBKDF2_ITERATIONS", 1000);
define("PBKDF2_SALT_BYTE_SIZE", 24);
define("PBKDF2_HASH_BYTE_SIZE", 24);

define("HASH_SECTIONS", 4);
define("HASH_ALGORITHM_INDEX", 0);
define("HASH_ITERATION_INDEX", 1);
define("HASH_SALT_INDEX", 2);
define("HASH_PBKDF2_INDEX", 3);

/**
 * @param $className
 */
function autoloader($className)
{
    include CLASS_FOLDER . DS . $className . '.php';
}