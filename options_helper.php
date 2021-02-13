<?php
function options_view($options_table){
        $options_table_sorted=[];
        foreach($options_table as $app_option){
            $options_table_sorted[$app_option->nav][] = $app_option;
        }
        echo '<ul class="nav nav-tabs" id="myTab" role="tablist">';
        $active="active";
        foreach($options_table_sorted as $key => $nav){
            ?>
                <li class="nav-item">
                    <a class="nav-link <?= $active;$active="" ?>" id="tab-<?= $key ?>" data-toggle="tab" href="#options-<?= $key ?>" role="tab" aria-controls="<?= $key ?>" aria-selected="true"><?= empty($key)?"X":ucfirst($key) ?></a>
                </li>
            <?php
        }
        echo '</ul><div class="tab-content ">';
        $active="active";
        foreach($options_table_sorted as $key => $nav){
            echo '<div class="options-form-controls tab-pane fade show bg-white '.$active.'" id="options-'.$key.'" role="tabpanel" aria-labelledby="tab-'.$key.'">';
            $active="";
            foreach($nav as $app_option){
                $opt_form=[];
                if(empty(trim($app_option->form))){
                    $opt_form[]=[$app_option->name=>["data-id"=>$app_option->id,"value"=>$app_option->value]];
                }else{
                    $json_array = (array)json_decode($app_option->form);
                    $json_array["data-id"] = $app_option->id;
                    $json_array["value"] = $app_option->value;
                    $json_array["data-value"] = $app_option->value;
                
                    $opt_form[]=[$app_option->name => $json_array];
        
                }
                echo "<form action='' method='post' id='options-field-$app_option->id'>";
                echo "<input type='hidden' value='{$app_option->id}' name='id'>";
                form_builder($opt_form, ["is_custom_form_data"=>true,"submit_button"=>" "]);
                echo "</form>";
            }
            echo '</div>';
        }
        echo '</div>';
}

function options_controller($that){
    if($that->input->is_ajax_request()){
        if(isset($_FILES) && isset($_FILES["value"])){
            $config = [
                "upload_path"=>("./assets/images"),
                "max_size"=>3000,
                "allowed_types" => "jpg|gif|png|jpeg|JPG|PNG|JPEG|GIF",
            ];
            $that->load->library("upload",$config);
            if($that->upload->do_upload("value")){
                $_POST["value"] = $that->upload->data("file_name");
            }else{
                $out = ["status"=>"error", "message"=>$that->upload->display_errors()];
            }
        }

        if(isset($_POST["value"])){
            if($that->Db_model->update($that->router->class,$that->input->post(null,true))){
                $that->Db_model->update_app_options_session();
                $out=["status"=>"success","message"=>"Changes has been saved"];
            }else{
                $out=["status"=>"error","message"=>"System error. Can't save right now"];
    
            }
        }else{
            $out=["status"=>"error","message"=>"value not set"];
        }
        $that->output
                ->set_status_header(200)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($out))
                ->_display();
                die();
    }
}
function get_options($key,$full_data=false) {
    $ci =& get_instance();
    if(is_array($key)){
        foreach($key as $name){
            $ci->db->or_where("name",$name);
        }
    }else{
        $ci->db->where(["name"=>$key]);
        if(isset($ci->session->userdata("app_options")[$key])){
            $ci->db->reset_query();
            return $full_data?[$key=>$ci->session->userdata("app_options")[$key]]:$ci->session->userdata("app_options")[$key]->value;
        }
    }
    $option = $ci->db->get_where("options");
    if($option->num_rows()<=0){
        show_error("the $key setting not found");
    }else{
        if(is_array($key)){
            $out = $option->result();
            $return = [];
            foreach($out as $row){
                $return[$row->name] = $full_data? $row : $row->value;
            }

            return $return;
        }else{
            return $full_data ? $option->result()[0] : $option->result()[0]->value;
        }
    }
}
?>