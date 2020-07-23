<?php
$load=[];
function load_library($libraries = []){
    if(is_array($libraries)){
        foreach($libraries as $lib){
            $GLOBALS["load"][] = $lib;
        }
    }else{
        $GLOBALS["load"][] = $libraries;
    }
    
    $GLOBALS["html_library"] =[
        "data_table" => [
            ["dataTables.bootstrap4.min.css"],["jquery.dataTables.js","dataTables.bootstrap4.min.js"]
        ],
        "selectize" => [
            ["css/selectize.css"],["js/standalone/selectize.min.js"]
        ],
        "chart_js"=>
        [
            ["Chart.min.css"],["Chart.min.js"]
        ],
        "options-control"=>[[],["options-control.js"]]
    ];
}
function get_html_library($location){
    if($location=="header"){
        $location = 0;
    }else{
        $location = 1;
    }
    $load = isset($GLOBALS["load"])?$GLOBALS["load"]:[];
    if(!is_array($load)){
        $tmp = $load;
        $load =array();
        $load[] = $tmp;
    }
    $out = "";
   if(!empty($load)){
    $library =  $GLOBALS["html_library"];
       foreach($load as $script){
           $files = isset($library[$script][$location])?$library[$script][$location]:[];
           if(!empty($files) && is_array($files)){
                foreach($files as $header_script){
                    if(preg_match("/\.css$/",$header_script)){
                        $out .= '<link rel="stylesheet" href="'.base_url().'assets/lib/'.$script.'/'.$header_script.'" />
                        ';

                    }elseif(preg_match("/\.js$/",$header_script)){
                        $out .= '<script src="'.base_url().'assets/lib/'.$script.'/'.$header_script.'"></script>
                        ';
                    }

                }
           }
       }
   }
   return $out;
}
?>