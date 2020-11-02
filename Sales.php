<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sales extends AppController {
	var $table = ["list"=>
	[
		"exclude" => ["_trash","bill_data"],
		"rename" => ["quantity" => "Qty","id"=>"#","phone_no"=>"phone"],
		"join" => ["accounts","customer","name"]
	],
];
	public function __construct(){
		parent::__construct();
		if(!$this->session->has_userdata("user")){
			redirect(base_url()."admin/login");
		}
		$this->load->database();
		$this->load->model("Db_model");

		 set_library(["sales"=>[["sales.css"],["sales.js"]]]);
	}
	public function index()
	{
		redirect(base_url().$this->router->class."/new");
	}
	public function new($edit=0){
		load_library("sales");
		if(is_numeric($edit) && $edit!=0){
			$sql = $this->db->get_where($this->router->class,["_trash"=>"0","id"=>$edit]);
			if($sql->num_rows()>0){
				$data["edit"] = $sql->result()[0];
				$data["edit"]->products = json_encode($this->db->get_where("invoice_items",["type"=>"sale","invoice_id"=>$edit,"_trash"=>0])->result_array());
			}else{
				show_404();
			}
		}
		if($this->input->method()=="post"){
			$fv = $this->form_validation;
			$fv->set_rules("account","ccount","trim|numeric");
			$fv->set_rules("description","Description","trim");
			$fv->set_rules("id","id","numeric");
			$fv->set_rules("action","Action","required|in_list[save,draft,print]");
			$_POST["user"] = $this->session->userdata("user")["id"];
			$_POST["print_options"] = json_encode($_POST["print_options"]);
			$products = $this->input->post("products");
			$type = "sale";
			unset($_POST["products"]);
			unset($_POST["type"]);
			if($fv->run()){
				$action = $this->input->post("action");
				$_POST["draft"] = $action == "draft"?1:0;
				unset($_POST["action"]);

				$to_print = "?";
				if($this->input->post("id")){
					if( $this->Db_model->update($this->router->class,$this->input->post())){
						$this->Db_model->update("invoice_items",["_trash"=>1],["invoice_id"=>$this->input->post("id")]);

						if($action == "print"){
							$to_print .= "print=".$this->input->post("id");
						}
						$to_print.="&last_added=".$this->input->post("id");
						foreach($products as $product){
							$this->Db_model->save("invoice_items",[
								"invoice_id"=>$this->input->post("id"),
								"product_id"=>$product["id"],
								"product_name"=>$product["name"],
								"quantity"=>$product["quantity"],
								"price"=>$product["price"],
								"type"=>$type
							]);
						}
						alert($this->router->class." has been update","success",base_url().$this->router->class."/edit/".$this->input->post("id").$to_print);
					}else{
						alert("System Error,<br>Could not update","danger",base_url().$this->router->class."/".$this->router->method);
					}
				}else{
					if($out_id = $this->Db_model->save($this->router->class,$this->input->post())){

						if($action == "print"){
							$to_print .= "print=".$out_id;
						}
						$to_print.="&last_added=".$out_id;
						foreach($products as $product){
							$this->Db_model->save("invoice_items",[
								"invoice_id"=>$out_id,
								"product_id"=>$product["id"],
								"product_name"=>$product["name"],
								"quantity"=>$product["quantity"],
								"price"=>$product["price"],
								"type"=>$type
							]);
						}
						alert($this->router->class." has been saved","success",$this->router->class."/".$this->router->method.$to_print );
					}else{
						alert("System Error,<br>Could not save","danger",$this->router->class."/".$this->router->method."?". http_build_query($_POST) );
					}
				}
			}else{
				alert(validation_errors(),"danger",$this->router->class."/".$this->router->method."?". http_build_query($_POST) );
			}
		}
		if(!$this->input->is_ajax_request()){
			set_library(["selectize"=>[["css/selectize.bootstrap4.css"],["js/standalone/selectize.min.js"]]]);
			load_library(["selectize"]);

			$data["main"]="new";
			$this->load->view("main",$data);
		}
	}
	public function list(){
		load_library("data_table");
		prime_mover_list();
	}
	public function edit($id){
		$this->new($id);
	}
	public function delete($id){
		if(is_numeric($id)){
			if($this->Db_model->update($this->router->class,["_trash"=>1,"id"=>$id])){
				alert($this->router->class." has been deleted","warning",base_url().$this->router->class."/list");
			}else{
				alert("System Error,<br>Can't delete","danger",base_url().$this->router->class."/".$this->router->method);
			}
		}
	}

	public function print($id=0){
		if(is_numeric($id) && $id!=0){
			$this->db->select(["s.id","s.extra_charge extra_charge","s.print_options options","s._created","s.description notes","s.discount discount","CONCAT_WS(' ',a.first_name, a.last_name) name","GROUP_CONCAT(c.value) contact"]);
			$this->db->from("{$this->router->class} as s");
			$this->db->join("accounts as a","a.id = s.account","left");
			$this->db->join("contacts as c","c.account = a.id AND c.type = 'phone'","left");
			$sql = $this->db->get_where("",["s._trash"=>"0","s.id"=>$id]);
			if($sql->num_rows()>0){
				$this->Db_model->update("sales",["id"=>$id,"is_printed"=>1]);

				$data["print"] = $sql->result()[0];
				$this->db->join("products p","in.product_id = p.id  ","LEFT");
				$data["print"]->products = $this->db->get_where("invoice_items in",["in.type"=>"sale","in.invoice_id"=>$id,"in._trash"=>0])->result_array();
				$this->load->view("sales/print",$data);
			}else{
				show_404();
			}
		}
	}

	public function get_accounts(){
		if($this->input->is_ajax_request() && $this->input->method() == "post"){
			$out = $this->db->get_where("accounts")->result();
            $this->output->set_content_type('application/json')->set_status_header(200)->set_output(json_encode($out));	
		}
		else{
			echo "<h1>Forbidden</h1>";
		}
	}
	public function get_products(){
		if($this->input->is_ajax_request() && $this->input->method() == "post"){
			
		}
		else{
			echo "<h1>Forbidden</h1>";
		}
	}
}
