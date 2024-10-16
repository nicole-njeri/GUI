<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require "includes/constants.php";
require "includes/dbConnection.php";
require "lang/en.php";

// Autoload classes from specified directories
function classAutoLoad($classname){
    $directories = ["contents", "layouts", "menus", "forms", "processes", "global"];
    foreach($directories as $dir){
        $filename = dirname(__FILE__) . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $classname . ".php";
        if(file_exists($filename) && is_readable($filename)){
            require_once $filename;
        }
    }
}

spl_autoload_register('classAutoLoad');

// Create instances of all required classes
$ObjGlob = new fncs();
$ObjSendMail = new SendMail();
$ObjLayouts = new layouts();
$ObjMenus = new menus();
$ObjHeadings = new headings();
$ObjCont = new contents();
$ObjForm = new user_forms();

// Establish database connection using constants
try {
    $conn = new dbConnection(DBTYPE, HOSTNAME, DBPORT, HOSTUSER, HOSTPASS, DBNAME);
} catch (Exception $e) {
    die("Error: Could not connect to the database. " . $e->getMessage());
}

// Create process instances and call methods
$ObjAuth = new auth();
$ObjAuth->signup($conn, $ObjGlob, $ObjSendMail, $lang, $conf);
$ObjAuth->verify_code($conn, $ObjGlob, $ObjSendMail, $lang, $conf);
