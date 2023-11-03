<?php
namespace Src;
require_once "encryption.php";


function checkPOSTData($name){
    if (isset($_POST[$name])) {
        return $_POST[$name];
    }else {
        return '';
    }
}

?>