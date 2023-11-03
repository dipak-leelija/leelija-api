<?php
namespace Src;
require_once "encryption.php";


function checkPOSTData($name){
    if (isset($_POST[$name])) {
        echo $_POST[$name];
        return $_POST[$name];
    }else {
        echo 'Not Set';
        return '';
    }
}

function checkRAWData($rawInput, $name){
    if (isset($rawInput[$name])) {
        echo $rawInput[$name];
        return $rawInput[$name];
    }else {
        echo 'Not Set';
        return '';
    }
}

?>