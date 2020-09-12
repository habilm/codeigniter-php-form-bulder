<?php 

function db_table_create($name=""){
    $attr = ["ENGINE"=>"InnoDB", "DEFAULT CHARSET"=>"utf8mb4"];
    $required_tables = [
        "users" =>
            '`id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `user_name` varchar(255) NOT NULL,
            `avatar` varchar(255) NOT NULL,
            `email` varchar(255) NOT NULL,
            `phone` varchar(255) NOT NULL,
            `ip` varchar(255) NOT NULL,
            `password` varchar(255) NOT NULL,
            `_created` timestamp NOT NULL DEFAULT current_timestamp(),
            `_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            `_trash` int(1) NOT NULL DEFAULT 0, PRIMARY KEY (`id`)',
        "options"=>'
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `name` varchar(255) NOT NULL,
            `value` text NOT NULL,
            `nav` varchar(255) NOT NULL,
            `form` text NOT NULL,
            `session` int(1) NOT NULL DEFAULT 0,
            `_created` timestamp NOT NULL DEFAULT current_timestamp(),
            `_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            `_trash` int(1) NOT NULL DEFAULT 0'
    ];
    $insert_data = [
        "users"=>"INSERT INTO `users` (`id`, `name`, `user_name`, `avatar`, `email`, `phone`, `ip`, `password`, `_trash`, `_created`, `_updated`) VALUES (NULL, 'borrow', 'admin', '', 'habil@gmail.com', '8714500950', '', MD5('admin##'), '0', '2020-08-26 22:01:16', '2020-08-26 22:01:16')",
        "options"=>"INSERT INTO `options` (`id`, `name`, `value`, `nav`, `form`, `session`, `_created`, `_updated`, `_trash`) VALUES (NULL, 'logo', '', 'app', '', '1', '2020-08-27 00:46:17', '2020-08-27 00:54:06', '0'), (NULL, 'use_image', '0', 'app', '[\"type\":\"select\",\"values\":[\"No\",\"Yes\"]]', '1', '2020-08-27 00:48:31', '2020-08-27 00:54:10', '0'), (NULL, 'app_icon', 'far fa-car', 'app', '', '1', '2020-08-27 00:49:27', '2020-08-27 00:54:13', '0'), (NULL, 'app_name', 'MY APP', 'app', '', '1', '2020-08-27 00:49:56', '2020-08-27 00:54:17', '0')"
    ];
    $ci =& get_instance();
    if(!empty($name) && !in_array($name,$required_tables)){
        show_error("The table data $name - not font");
    }
    $tables = empty($name)?$required_tables:$required_tables[$name];
    foreach($tables as $talbe_name => $data){
        if($ci->db->table_exists($talbe_name)){
            echo " 🆗 Table <b>$talbe_name</b> already exsist <br>\n";
            continue;
        }
        $columns = preg_split("/,\s+\n|,$/",$data);
        foreach($columns as $column){
            $ci->dbforge->add_field($column);
                
        }
        $ci->dbforge->add_key("id");
        if($ci->dbforge->create_table($talbe_name,true,$attr)){
            echo " ✅ Table <b>$talbe_name</b> has been creaed <br>\n";
        }
        if(isset($insert_data[$talbe_name])){
            $ci->db->query($insert_data[$talbe_name]);
        }
        
    }
    die();
}