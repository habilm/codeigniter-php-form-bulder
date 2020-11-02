<?php
function prime_mover_nav($default_menu){
   
    $GLOBALS["depth"] = 0;
    function menu_builder($that,$menu_items=[],$parent=""){
        $li = "";
        if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'){
        $url = "https://";
        }
        else{
        $url = "http://";   
        }
        $url.= $_SERVER['HTTP_HOST'];   
        $url.= $_SERVER['REQUEST_URI']; 
        $GLOBALS["depth"]++;
        foreach($menu_items as $method => $item){
 
            if(!isset($item["class"]) && !isset($item["method"]) && $GLOBALS["depth"] == 1 && !check_access(["class"=>$method]) ) continue;
            
            if(!isset($item["class"]) && !isset($item["method"]) && $GLOBALS["depth"] == 2 && !check_access(["class"=>trim($parent,"/"),"method"=>is_numeric($method)?$item:$method]) ) continue;

            if(isset($item["class"]) && isset($item["method"]) && !check_access($item)) continue;
                
            $method = is_array($item)?$method:$item;
            
            $tree = isset($item["tree"])?true:false;
            $active = ($that->router->class == $method)?"active":($that->router->class."/" == $parent && $that->router->method == $method)?"active":(isset($item["href"]) && $url==$item["href"])?"active":"";
            $a_attributes = isset($item["a_attributes"])?$item["a_attributes"]:"";
            $li .=  '
            <li id="menu-'.$method.'" class="nav-item dropdown '.$active.'">
                <a '.$a_attributes.' class="nav-link '.( $tree && !isset($item["href"])?"dropdown-toggle":"" ).'" href="'.( isset($item["href"])?$item["href"]:($tree?"#":base_url().$parent.$method ) ).'" '. ( $tree && !isset($item["href"])?' role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"':"" ).'>
                <i class="fa '.( isset($item["icon"])?$item["icon"]:( $tree?"fa-th-large":"fa-circle-o" )  ).'"></i> <span>'.( isset($item["rename"])?$item["rename"]: ucfirst($method)  ).'</span>
                '.(
                    $tree?'<span class="pull-right-container">
                </span>':''
                ).'
                </a>
                '.($tree?'
                <ul class="dropdown-menu " aria-labelledby="navbarDropdown" >
                '.( menu_builder($that,$item["tree"],$parent.$method."/") ).'
                </ul>':"").'
            </li>';
        }
    $GLOBALS["depth"]--;
    return $li;
    }
    $ci =& get_instance();
    $items = menu_builder($ci,$default_menu);;
    return '<ul class="navbar-nav mr-auto">'.$items.'</ul>';
}
?>
