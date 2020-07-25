<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if(!function_exists("form_builder")){
/**
 * $table => form : array(
 *  )
 * $table => form_filter_hooks : array(<hook_name>: array( <field_name/column_name> ) ) 
 * eg:"before_submit"=>["category_id"], the function will be before_submit_category_id
 */
    function form_builder($edit=[],$options=[]){
        if(empty($edit)){
            $edit = $_GET;
        }
        $edit = (array)$edit;
        echo isset($edit["id"])?"<input type='hidden' name='id' value='{$edit['id']}' >":"";
        $cl =& get_instance();
        if(isset($cl->table["form"]) || isset($options["is_custom_form_data"])){
            echo '<div class="box-body">';
            $form_data = isset($options["is_custom_form_data"])?$edit:$cl->table["form"];
            foreach($form_data as $frow){
                ?>
                <div class="row">

                <?php 
                foreach($frow as $key => $col){
                    $attributes=array("type"=>"text","label"=>$col,"pattern"=>"","rows"=>"3","class"=>"form-control form-control-sm","maxlength"=>255);
                    if(is_array($col)){
                        $fm_control = str_replace(" ","",$key);
                        $attributes["placeholder"] = $attributes["label"] = ucfirst(str_replace("_"," ",$key));
                        $attributes = array_merge($attributes,$col);
                        $attributes["value"] = (!empty($edit[$fm_control]))?$edit[$fm_control]:(  isset($col["value"])?$col["value"]:"");
                        if(isset($col["rules"])){
                            $rules = preg_split('/\|(?![^\[]*\])/', $col["rules"]);
                            foreach($rules as $rule){
                                $attributes = array_merge($attributes,to_html_attr($rule));
                            }
                            unset($attributes["rules"],$attributes["colspan"]);
                        }
                        if(isset($attributes["name"])){
                            $fm_control= str_replace(" ","",trim($attributes["name"]));
                        }
                        $attributes["placeholder"] = str_replace("_"," ",$attributes["placeholder"]);
                    }else{
                        $attributes["value"] = (!empty($edit[$col]))?$edit[$col]:(  isset($col["value"])?$col["value"]:"");
                        $fm_control = str_replace(" ","",trim($col));
                    }

                    //filter_hooks 
                    if(isset($cl->table["form_filter_hooks"]["before_create_input"]) && in_array($fm_control,$cl->table["form_filter_hooks"]["before_create_input"])){
                        if(function_exists($hook_function_name = "before_create_input_".$fm_control)){
                            $attributes = call_user_func($hook_function_name,$cl,$attributes);
                        }else{
                            echo '<h4 class="text-danger">function not found</h4>';
                        }
                    }
                    $html_attr = $parent_facts = "";
                    foreach($attributes as $attr_name => $attribute){
                        if(is_array($attribute) && $attr_name=="parent"){
                            $parent_facts.="data-parent-name='{$attribute[0]}' data-parent-value='".(isset($attribute[1])?$attribute[1]:"")."'";
                            $html_attr.="disabled='disabled'";
                        }
                        if(!is_array($attribute) && !in_array($attr_name,["label"]) && !empty(trim($attribute)) ){
                            $html_attr.="$attr_name='$attribute' ";
                        }
                    }

                    if($attributes["type"]=="hidden"){
                        ?>
                            <input type="hidden" <?= $html_attr ?> name="<?=$fm_control ?>" >
                        <?php
                    }elseif($attributes["type"]=="checkbox"){
                        ?>
                            <div <?= $parent_facts ?> class="col-sm-<?=isset($attributes["colspan"])?$attributes["colspan"]:( floor( 12/count($frow)) ) ?>">
                                <div class="form-group">
                                    <label> <input type="<?=$attributes["type"] ?>" <?= $html_attr ?> <?php if(function_exists($attributes["pattern"])){ echo call_user_func($attributes["pattern"]);} ?> value="<?= isset($edit[$fm_control])?$edit[$fm_control]:"" ?>" name="<?=$fm_control ?>" > <?=ucfirst($attributes["label"])?></label>
                                </div>
                            </div>
                        <?php
                    }
                    else{
                        ?>
                        <div <?= $parent_facts ?> class="col-sm-<?=isset($attributes["colspan"])?$attributes["colspan"]:( floor( 12/count($frow)) ) ?>">
                            <div class="form-group">
                                <label><?=ucfirst($attributes["label"])?></label>
                                <?php if($attributes["type"]=="textarea"){ ?>
                                    <textarea name="<?=$fm_control ?>" <?= $html_attr ?>><?= isset($edit[$fm_control])?$edit[$fm_control]:"" ?></textarea>
                                <?php }elseif($attributes["type"]=="select"){?>
                                    <select name="<?=$fm_control.(isset($attributes["multiple"]) && $attributes["multiple"]=="multiple"?"[]":"") ?>"  <?= $html_attr ?> >
                                        <?php
                                            if(isset($attributes["table"])){
                                                $value_key = isset($attributes["key"])?$attributes["key"]:"id";
                                                $result = $cl->db->get_where($attributes["table"],["_trash"=>0])->result();
                                                // $value_array = (array)json_decode($edit[$fm_control]);
                                                $value_array = explode(",",$edit[$fm_control]);
                                                foreach($result as $row){
                                                    $selected="";
                                                    if(isset($edit[$fm_control]) && ($edit[$fm_control]==$row->{$value_key} || (is_array($value_array) && in_array($row->{$value_key}, $value_array ) ) ) ){
                                                        $selected="selected";
                                                    }
                                                    echo "<option value='".($row->{$value_key})."' $selected>$row->name</option>";
                                                }
                                            }elseif(isset($attributes["values"])){
                                                foreach($attributes["values"] as $key => $option){
                                                    $selected = "";
                                                    if((isset($edit[$fm_control]) && ($edit[$fm_control]==$key || strpos($edit[$fm_control],$key) ))|| $attributes["value"] == $key){
                                                        $selected = "selected";
                                                    }
                                                    echo "<option value='$key' $selected>$option</option>";
                                                }
                                            }
                                        ?>
                                    </select>
                                <?php }else if($attributes["type"]=="file") { ?>
                                    <?php echo "<span class='badge badge-info'>".(isset($edit[$fm_control])?$edit[$fm_control]:"")."</span>" ?>
                                  
                                    <?php  $is_multiple =  isset($attributes["multiple"])?"[]":"" ?>
                                    <input  type="<?=$attributes["type"] ?>"  <?= $html_attr ?>name="<?=$fm_control.$is_multiple  ?>">
                                   <?php 
                                }else{?>
                                    <input type="<?=$attributes["type"] ?>"  <?= $html_attr ?> name="<?=$fm_control ?>">
                                <?php } ?>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
                </div>
                <?php
            }
            
        }   ?>
            </div>
            <?= isset($options["custom_fields"])?$options["custom_fields"]:"" ?>
            <?= isset($options["submit_button"])?$options["submit_button"]:'
            <div class="box-footer">
                <input type="submit" class="btn btn-primary" value="Submit">
            </div>' ?>
            
            <?php
    }
}

if(!function_exists("to_html_attr")){
    function to_html_attr($rule){
        $rule = str_replace("]","",$rule);
        $case = explode("[",$rule);
        switch($case[0]){
            case "required":{
                return ["required"=>"required"];
            break;
            }
            case "numeric":
            case "decimal":{
                return ["type"=>"number"];
            break;
            }
            case "max_length":{
                return ["maxlength"=>$case[1]];
            break;
            }
            case "min_length":{
                return ["minlength"=>$case[1]];
            break;
            }
            case "valid_email":{
                return ["type"=>"email"];
            break;
            }
            case "regex_match":{
                return ["pattern"=>trim($case[1],'/')];
            break;
            }
            default:{
                return [];
            break;
            }
        }
    }
}

/**
 * prime_mover form manager
 * 
 * values
 * $edit = the id of row from the table to edit
 * 
 * 
 */
if(!function_exists("prime_mover_new")){
    function prime_mover_new($edit = 0){
        
        $url_prefix = "";
        $ci =& get_instance();
        $base= ($base = $ci->config->item("prime_mover_base") )?$base."/":"";
        if(is_numeric($edit) && $edit!=0){  
			$sql = $ci->db->get_where($ci->router->class,["_trash"=>"0","id"=>$edit]);
			if($sql->num_rows()>0){
                $data["edit"] = $sql->result()[0];
                $data["main"] = ( $view_file = $ci->config->item("prime_mover_view_file_create"))?$view_file:"new";
			}else{
				show_404();
			}
		}
		if($ci->input->method()=="post"){
            if(isset($ci->table["form_filter_hooks"]["before_validation"])){
                call_user_func($ci->table["form_filter_hooks"]["before_validation"]);
            }
			$fv = $ci->form_validation;
			foreach($ci->table["form"] as $row){
				foreach($row as $key => $element){
					if(is_array($element)){
						$field_name = isset($element["label"])?$element["label"]: $key;
                        $rules = isset($element["rules"])?$element["rules"]:"trim";
                        $fv->set_rules($key,$field_name,$rules);
					}else{
						$fv->set_rules($element,$element,"trim");
					}
				}
            }
            $after_submit_function_name = isset($ci->table["form_filter_hooks"]["submit_message"])?$ci->table["form_filter_hooks"]["submit_message"]:"alert";
			if($ci->form_validation->run()){
                $edit_url = $ci->input->post("id")?"/".$ci->input->post("id"):"";
                if(!empty($_FILES)){
                    foreach($_FILES as $name => $property ){
                        $config = [
                            "upload_path"=>"./assets/uploads",
                            "max_size"=>3000
                        ];
                        foreach($ci->table["form"] as $row){
                            if(isset($row[$name]) && isset($row[$name]["allowed_types"])){
                                if(isset($row[$name]["upload_path"])){
                                    $config["upload_path"] = $row[$name]["upload_path"];
                                }
                                $config["allowed_types"]=$row[$name]["allowed_types"];
                                $ci->load->library("upload",$config);
                                
                                if(is_array($property["name"]) && count($property["name"])>0){
                                    foreach($property["name"] as $key => $value){
                                        $_FILES['_dd_file']['name']= $_FILES[$name]['name'][$key];
                                        $_FILES['_dd_file']['type']= $_FILES[$name]['type'][$key];
                                        $_FILES['_dd_file']['tmp_name']= $_FILES[$name]['tmp_name'][$key];
                                        $_FILES['_dd_file']['error']= $_FILES[$name]['error'][$key];
                                        $_FILES['_dd_file']['size']= $_FILES[$name]['size'][$key];
                                        if($ci->upload->do_upload("_dd_file")){
                                            $_POST[$name] .= $ci->upload->data("file_name")."|";
                                        }else{
                                            if(isset($row[$name]["required"]) && $row[$name]["required"]=="required" ){
                                                call_user_func($after_submit_function_name,"<b>File upload error</b> <br>".$ci->upload->display_errors(),"danger",$base.$url_prefix.$ci->router->class."/".$ci->router->method.$edit_url."?". http_build_query($_POST) );
                                            }
                                        }
                                    }
                                }else{
                                    if($ci->upload->do_upload($name)){
                                        $_POST[$name] = $ci->upload->data("file_name");
                                    }else{
                                        if(isset($row[$name]["required"]) && $row[$name]["required"]=="required" ){
                                            call_user_func($after_submit_function_name,"<b>File upload error</b> <br>".$ci->upload->display_errors(),"danger",$base.$url_prefix.$ci->router->class."/".$ci->router->method.$edit_url."?". http_build_query($_POST) );
                                        }
                                    }
                                }
                            }
                        }
                    }

                }
                $_POST = isset($ci->table["form_filter_hooks"]["before_save"])?call_user_func($ci->table["form_filter_hooks"]["before_save"],$_POST):$_POST;
				if($ci->input->post("id")){
					if($ci->Db_model->update($ci->router->class,$ci->input->post())){
                        call_user_func($after_submit_function_name,$ci->router->class." has been update","success",base_url($base.$url_prefix).$ci->router->class."/edit/".$ci->input->post("id"),$ci->input->post("id"));
                        
					}else{
						call_user_func($after_submit_function_name,"System Error,<br>Could not update","danger",base_url($base.$url_prefix).$ci->router->class."/".$ci->router->method);
					}
				}else{
                    if($id = $ci->Db_model->save($ci->router->class,$ci->input->post())){
                        call_user_func($after_submit_function_name,$ci->router->class." has been saved","success",$base.$url_prefix.$ci->router->class."/".$ci->router->method,$id);
                        
					}else{
						call_user_func($after_submit_function_name,"System Error,<br>Could not save","danger",$base.$url_prefix.$ci->router->class."/".$ci->router->method."?". http_build_query($_POST) );
					}
				}
			}else{
				call_user_func($after_submit_function_name,validation_errors(),"danger",$base.$url_prefix.$ci->router->class."/".$ci->router->method."?". http_build_query($_POST) );
			}
        }

		$ci->load->view($base.'main', ( isset($data)?$data:"" ) );
    }
}

if(!function_exists("prime_mover_list")){
 /*
 *  var $table = list:array(
 *                          exclude : array(<column names >)
 *                          rename : array(<column_name>=><custom name>)
 *                          join : array(<table name>, <join id column-name>, <column name which is to show instead of id>)
 *                          filter_hooks : array("<hooks_name>" => "<function name>")
 *                                         available hooks: 
 *                                              col_<column_name>:function($data,$id)), 
 *                                              db_where(to add extra where condition):function()
 *                                          
 *                     )
 */   function prime_mover_list(){
        $ci =& get_instance();
        $base= ($base = $ci->config->item("prime_mover_base") )?$base."/":"";
        $fields = $ci->db->list_fields($ci->router->class);
		if(isset($ci->table["list"]["exclude"]) && is_array($ci->table["list"]["exclude"])){
			$fields = array_diff($fields,$ci->table["list"]["exclude"]);
        }
        
		if($ci->input->is_ajax_request()){
            // print_r($_GET);
            $filter_hooks = isset($ci->table["list"]["filter_hooks"])?$ci->table["list"]["filter_hooks"]:[];

            $ci->db->from("{$ci->router->class} as me");
			$out["draw"] = $ci->input->get("draw");
			if(isset($ci->table["list"]["join"])){
				$join = $ci->table["list"]["join"][0];
				$field = $ci->table["list"]["join"][1];
                $select = $ci->table["list"]["join"][2];
				$ci->db->select(" me.*,$join.$select");
				$ci->db->join($join,"me.$field = $join.id","left");
            }
            key_exists("db_where",$filter_hooks)?$ci->db->where(call_user_func($filter_hooks["db_where"]) ):"";
			$ci->db->where(["me._trash"=>"0"]);
			$like = [];
			if(!empty($ci->input->get("search")["value"])){
				foreach($fields as $field){
					$like[$field] = $ci->input->get("search")["value"];
				}
				$ci->db->or_like($like);
			}
			
			$out["recordsTotal"] = $out["recordsFiltered"]  = $ci->db->count_all_results(); 

			if($ci->input->get("order") && isset($fields[$ci->input->get("order")[0]["column"]]) ){
				$ci->db->order_by($fields[$ci->input->get("order")[0]["column"]],$ci->input->get("order")[0]["dir"]);
			}
			$ci->db->from("{$ci->router->class} as me"); 	
			if(isset($ci->table["list"]["join"])){
				$join = $ci->table["list"]["join"][0];
				$field = $ci->table["list"]["join"][1];
				$select = $ci->table["list"]["join"][2];
				$ci->db->select(" me.*,$join.$select");
				$ci->db->join($join,"me.$field = $join.id","left");
            }
            
            key_exists("db_where",$filter_hooks)?$ci->db->where(call_user_func($filter_hooks["db_where"]) ):"";
			$ci->db->or_like($like);
			$ci->db->limit($ci->input->get("length"),$ci->input->get("start"));
			$list = $ci->db->get_where("",["me._trash"=>"0"])->result();
			$out["data"]=[];
			foreach($list as $row){
					$column = [];
						foreach($fields as $field){
							if(isset($ci->table["list"]["join"])){
								if($field == $ci->table["list"]["join"][1]){
									$field = $ci->table["list"]["join"][2];
								}
							}
                            if(array_key_exists("col_".$field,$filter_hooks)){
                                $column[] = call_user_func($filter_hooks["col_".$field],$row->{$field},$row->id);
                            }else{
                                $column[] = $row->{$field};
                            }
						}
                        $action = " ";
                        $action.= '<a href="'.base_url($base."/").$ci->router->class."/edit/".$row->id.'" class="fa fa-edit fa-2x"></a>';
                        $action.='<a href="'.base_url($base."/").$ci->router->class."/delete/".$row->id.'" class="fa fa-trash fa-2x text-danger"></a>';
                        if(array_key_exists("col_action",$filter_hooks)){
                           $action = call_user_func($filter_hooks["col_action"],$action,$row->id);
                        }
                        $column[] = $action;
						$out["data"][] = $column;
					}
					$ci->output
					->set_status_header(200)
					->set_content_type('application/json', 'utf-8')
					->set_output(json_encode($out))
					->_display();
			die();
		}
        $data["body"]["fields"] = $fields;
        $base= ($base = $ci->config->item("prime_mover_base") )?$base."/":"";
		$ci->load->view($base.'main', ( isset($data)?$data:"" ) );
    }
}