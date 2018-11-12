<?php
    
	/*		
		CREATE TABLE IF NOT EXISTS `users` (
		  `user_id` int(11) NOT NULL AUTO_INCREMENT,
		  `user_fullname` varchar(25) NOT NULL,
		  `user_email` varchar(50) NOT NULL,
		  `user_password` varchar(50) NOT NULL,
		  `user_status` tinyint(1) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`user_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
   
               insert into users (user_fullname, user_email, user_password, user_status) values ("root","afdmoraes@gmail.com", "root", 1);
               insert into users (user_fullname, user_email, user_password, user_status) values ("admin","afdmoraes@gmail.com", "admin", 0);
			   insert into users (user_fullname, user_email, user_password, user_status) values ("andre","afdmoraes@gmail.com", "12345", 1);
			   insert into users (user_fullname, user_email, user_password, user_status) values ("root","afdmoraes@gmail.com", "root", 1);
               insert into users (user_fullname, user_email, user_password, user_status) values ("admin","afdmoraes@gmail.com", "admin", 0);
			   insert into users (user_fullname, user_email, user_password, user_status) values ("andre","afdmoraes@gmail.com", "12345", 1);
			   insert into users (user_fullname, user_email, user_password, user_status) values ("root","afdmoraes@gmail.com", "root", 1);
               insert into users (user_fullname, user_email, user_password, user_status) values ("admin","afdmoraes@gmail.com", "admin", 0);
               insert into users (user_fullname, user_email, user_password, user_status) values ("andre","afdmoraes@gmail.com", "12345", 1);
 	*/
	
	require_once("Rest.inc.php");
	
	class API extends REST {
	
		public $data = "";
		
		// Dados do banco
		const DB_SERVER = "localhost";
		const DB_USER = "root";
		const DB_PASSWORD = "";
		const DB = "restful";
		
		private $db = NULL;
	
		public function __construct(){
			parent::__construct();				// Init parent contructor
			$this->dbConnect();					// Initiate Database connection
		}
		
		/*
		 *	Conexão ao banco
		*/
		private function dbConnect(){
			$this->db = mysqli_connect(self::DB_SERVER,self::DB_USER,self::DB_PASSWORD,self::DB);
			if($this->db)
				mysqli_select_db($this->db,self::DB);				
		}
		
		/*
		 * Esse método chama a função de acordo com o método
		 */
		public function processApi(){
			$func = strtolower(trim(str_replace("/","",$_REQUEST['rquest'])));
			if((int)method_exists($this,$func) > 0)
				$this->$func();
			else
				$this->response('',404);
		}
		
		/* 
		 *	Simple login API
		 *  Login must be POST method
		 *  email : <USER EMAIL>
		 *  pwd : <USER PASSWORD>
		 */
		
		private function login(){
			// Cross validation if the request method is POST else it will return "Not Acceptable" status
			if($this->get_request_method() != "POST"){
				$this->response('',406);
			}

			$email = $this->_request['email'];		
			$password = $this->_request['pwd'];
			
			// Input validations
			if(!empty($email) and !empty($password)){
				if(filter_var($email, FILTER_VALIDATE_EMAIL)){
					$sql = mysql_query("SELECT user_id, user_fullname, user_email FROM users WHERE user_email = '$email' AND user_password = '".md5($password)."' LIMIT 1", $this->db);
					if(mysql_num_rows($sql) > 0){
						$result = mysql_fetch_array($sql,MYSQL_ASSOC);
						
						// If success everythig is good send header as "OK" and user details
						$this->response($this->json($result), 200);
					}
					$this->response('', 204);	// If no records "No Content" status
				}
			}
			
			// If invalid inputs "Bad Request" status message and reason
			$error = array('status' => "Failed", "msg" => "Invalid Email address or Password");
			$this->response($this->json($error), 400);
		}

		private function atualizar(){			
			if($this->get_request_method() != "PUT"){
				$this->response('', 406);
			}

			$name = $this->_request['name'];

			if(!empty($name)){
				$sql = mysqli_query($this->db, "SELECT * FROM user");
			}

		}
		
		private function users(){	
			// Cross validation if the request method is GET else it will return "Not Acceptable" status
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			$sql = mysqli_query($this->db, "SELECT user_id, user_fullname, user_email FROM users WHERE user_status = 1");
			if(mysqli_num_rows($sql) > 0){
				$result = array();
				while($rlt = mysqli_fetch_array($sql,MYSQLI_ASSOC)){
					$result[] = $rlt;
				}
				// If success everythig is good send header as "OK" and return list of users in JSON format
				$this->response($this->json($result), 200);
			}
			$this->response('',204);	// If no records "No Content" status
		}
		
		private function deleteUser(){
			// Cross validation if the request method is DELETE else it will return "Not Acceptable" status
			if($this->get_request_method() != "DELETE"){
				$this->response('',406);
			}
			$id = (int)$this->_request['id'];
			if($id > 0){				
				mysqli_query($this->db, "DELETE FROM users WHERE user_id = $id");
				$success = array('status' => "Success", "msg" => "Successfully one record deleted.");
				$this->response($this->json($success),200);
			}else
				$this->response('',204);	// If no records "No Content" status
		}
		
		/*
		 *	Encode array into JSON
		*/
		private function json($data){
			if(is_array($data)){
				return json_encode($data);
			}
		}
	}
	
	// Initiiate Library
	
	$api = new API;
	$api->processApi();
?>
