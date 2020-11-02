<?php
function check_access(array $data,object $that = null){
    if( is_Null($that)){
        $that =& get_instance();
    }
    $permissions = $that->session->userdata("user")["permissions"];
    if($permissions == "admin"){
        return true;
    }
    if(!isset($data["class"] ) ){
        return false;
    }
    
    if(isset($data["class"]) && !isset($data["method"]) && isset($permissions[ $data["class"] ]) ){
        return true;
    }
    if(isset($data["class"]) && isset($data["method"]) && isset($permissions[ $data["class"] ]) && in_array($data["method"],$permissions[ $data["class"] ])){
        return true;
    }else{
        return false;
    }
}
function show_403($message=""){
    header('HTTP/1.0 403 Forbidden');
    echo empty($message)?'<h1>Forbidden</h1>':$message;
    die();
}
