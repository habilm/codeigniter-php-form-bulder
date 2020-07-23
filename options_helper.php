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
                    <a class="nav-link <?= $active;$active="" ?>" id="tab-<?= $key ?>" data-toggle="tab" href="#options-<?= $key ?>" role="tab" aria-controls="<?= $key ?>" aria-selected="true"><?= ucfirst($key) ?></a>
                </li>
            <?php
        }
        echo '</ul><div class="tab-content">';
        $active="active";
        foreach($options_table_sorted as $key => $nav){
            echo '<div class="options-form-controls tab-pane fade show bg-white p-4 '.$active.'" id="options-'.$key.'" role="tabpanel" aria-labelledby="tab-'.$key.'">';
            $active="";
            foreach($nav as $app_option){
                $opt_form=[];
                
                if(empty(trim($app_option->form))){
                    $opt_form[]=[$app_option->name=>["data-id"=>$app_option->id,"value"=>$app_option->value]];
                }else{
                    $json_array = (array)json_decode($app_option->form);
                    $json_array["data-id"] = $app_option->id;
                    $json_array["value"] = $app_option->value;
                    if(is_array($json_array)){
                        $opt_form[]=[$app_option->name => $json_array];
                    }
                }
                form_builder($opt_form, ["is_custom_form_data"=>true,"submit_button"=>" "]);
            }
            echo '</div>';
        }
        echo '</div>';
}

function options_controller($that){
    if($that->input->is_ajax_request()){
        if($that->Db_model->update($that->router->class,$that->input->post())){
            $that->Db_model->update_app_options_session();
            $out=["status"=>"success","message"=>"Changes has been saved"];
        }else{
            $out=["status"=>"error","message"=>"System error. Can't save right now"];
        }
        $that->output
                ->set_status_header(200)
                ->set_content_type('application/json', 'utf-8')
                ->set_output(json_encode($out))
                ->_display();
                die();
    }
}
?>