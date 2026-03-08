<?php
require_once('../config.php');
Class Master extends DBConnection {
	private $settings;
	public function __construct(){
		global $_settings;
		$this->settings = $_settings;
		parent::__construct();
	}
	public function __destruct(){
		parent::__destruct();
	}
	function capture_err(){
		if(!$this->conn->error)
			return false;
		else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
			return json_encode($resp);
			exit;
		}
	}
	function save_department(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k =>$v){
			if(!in_array($k,array('id'))){
				$v = addslashes($v);
				if(!empty($data)) $data .=",";
				$data .= " `{$k}`='{$v}' ";
			}
		}
		$check = $this->conn->query("SELECT * FROM `department_list` where `name` = '{$name}' ".(!empty($id) ? " and id != {$id} " : "")." ")->num_rows;
		if($this->capture_err())
			return $this->capture_err();
		if($check > 0){
			$resp['status'] = 'failed';
			$resp['msg'] = " Departement déjà existant.";
			return json_encode($resp);
			exit;
		}
		if(empty($id)){
			$sql = "INSERT INTO `department_list` set {$data} ";
			$save = $this->conn->query($sql);
		}else{
			$sql = "UPDATE `department_list` set {$data} where id = '{$id}' ";
			$save = $this->conn->query($sql);
		}
		if($save){
			$resp['status'] = 'success';
			if(empty($id))
				$this->settings->set_flashdata('success',"Nouveau département sauvegardé avec succès.");
			else
				$this->settings->set_flashdata('success',"Le dépatement a été mis à jour.");
		}else{
			$resp['status'] = 'failed';
			$resp['err'] = $this->conn->error."[{$sql}]";
		}
		return json_encode($resp);
	}
	function delete_department(){
		extract($_POST);
		$del = $this->conn->query("DELETE FROM `department_ist` where id = '{$id}'");
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success',"Le département supprimé avec succès.");
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);

	}
	function save_designation(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k =>$v){
			if(!in_array($k,array('id'))){
				$v = addslashes($v);
				if(!empty($data)) $data .=",";
				$data .= " `{$k}`='{$v}' ";
			}
		}
		$check = $this->conn->query("SELECT * FROM `designation_list` where `name` = '{$name}' ".(!empty($id) ? " and id != {$id} " : "")." ")->num_rows;
		if($this->capture_err())
			return $this->capture_err();
		if($check > 0){
			$resp['status'] = 'failed';
			$resp['msg'] = " Poste déjà existant.";
			return json_encode($resp);
			exit;
		}
		if(empty($id)){
			$sql = "INSERT INTO `designation_list` set {$data} ";
			$save = $this->conn->query($sql);
		}else{
			$sql = "UPDATE `designation_list` set {$data} where id = '{$id}' ";
			$save = $this->conn->query($sql);
		}
		if($save){
			$resp['status'] = 'success';
			if(empty($id))
				$this->settings->set_flashdata('success',"Nouveau poste sauvegardé avec succès.");
			else
				$this->settings->set_flashdata('success',"Le poste a été mis à jour.");
		}else{
			$resp['status'] = 'failed';
			$resp['err'] = $this->conn->error."[{$sql}]";
		}
		return json_encode($resp);
	}
	function delete_designation(){
		extract($_POST);
		$del = $this->conn->query("DELETE FROM `designation_ist` where id = '{$id}'");
		if($del){
			$resp['status'] = 'success';
			$this->settings->set_flashdata('success',"Le poste a été supprimé avec succès.");
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = $this->conn->error;
		}
		return json_encode($resp);

	}
	function generate_string($input, $strength = 10) {
		
		$input_length = strlen($input);
		$random_string = '';
		for($i = 0; $i < $strength; $i++) {
			$random_character = $input[mt_rand(0, $input_length - 1)];
			$random_string .= $random_character;
		}
	 
		return $random_string;
	}
	function upload_files(){
		extract($_POST);
		$data = "";
		if(empty($upload_code)){
			while(true){
				$chk = $this->conn->query("SELECT * FROM `uploads` where dir_code ='{$code}' ")->num_rows;
				if($chk <= 0){
					$upload_code = $code;
					$resp['upload_code'] =$upload_code;
					break;
				}
			}
		}

		if(!is_dir(base_app.'uploads/blog_uploads/'.$upload_code))
			mkdir(base_app.'uploads/blog_uploads/'.$upload_code);
		$dir = 'uploads/blog_uploads/'.$upload_code.'/';
		$images = array();
		for($i = 0;$i < count($_FILES['img']['tmp_name']); $i++){
			if(!empty($_FILES['img']['tmp_name'][$i])){
				$fname = $dir.(time()).'_'.$_FILES['img']['name'][$i];
				$f = 0;
				while(true){
					$f++;
					if(is_file(base_app.$fname)){
						$fname = $f."_".$fname;
					}else{
						break;
					}
				}
				$move = move_uploaded_file($_FILES['img']['tmp_name'][$i],base_app.$fname);
				if($move){
					$this->conn->query("INSERT INTO `uploads` (dir_code,user_id,file_path)VALUES('{$upload_code}','{$this->settings->userdata('id')}','{$fname}')");
					$this->capture_err();
					$images[] = $fname;
				}
			}
		}
		$resp['images'] = $images;
		$resp['status'] = 'success';
		return json_encode($resp);
	}
	function save_employee(){
		foreach($_POST as $k =>$v){
			$_POST[$k] = addslashes($v);
		}
		extract($_POST);
		$chk = $this->conn->query("SELECT * FROM `employee_meta` where meta_field ='employee_id' and  meta_value = '{$employee_id}' ".($id>0? " and user_id!= '{$id}' " : ""))->num_rows;
		$this->capture_err();
		if($chk > 0){
			$resp['status'] = 'failed';
			$resp['msg'] = "ID de l'employé existe déjà dans la base de donnée. Vérifiez et réesayez à nouveau.";
			return json_encode($resp);
			exit;
		}
		$chk2 = $this->conn->query("SELECT * FROM `users` where username ='{$username}' ".($id>0? " and id!= '{$id}' " : ""))->num_rows;
		$this->capture_err();
		if($chk2 > 0){
			$resp['status'] = 'failed';
			$resp['msg'] = "Nom d'utilisateur indisponible. Vérifiez et réesayez à nouveau.";
			return json_encode($resp);
			exit;
		}
		$data = "";
		foreach($_POST as $k =>$v){
			if(in_array($k,array('firstname','lastname','middlename','username','type'))){
				if(!empty($data)) $data.=" , ";
				$data .= " `{$k}` = '{$v}' ";
			}
		}
		if(empty($id))
		$data .= ", `password` = md5('{$employee_id}') ";
		if(empty($id))
			$sql1 = "INSERT INTO `users` set {$data} ";
		else
			$sql1 = "UPDATE `users` set {$data}' where id = '{$id}' ";
		
		$save1 = $this->conn->query($sql1);
		$this->capture_err();
		if(!$save1){
			$resp['status'] = 'failed';
			$resp['error_sql'] = $sql1;
		}
		$user_id = empty($id) ? $this->conn->insert_id : $id ;
		$this->conn->query("DELETE FROM `employee_meta` where user_id = '{$user_id}' and meta_field not in ('leave_type_ids','leave_type_credits') ");
		$this->capture_err();
		$data = "";
		foreach($_POST as $k =>$v){
			if(!in_array($k,array('id','avatar'))){
				if(!empty($data)) $data .=",";
				$v = addslashes($v);
				$data .= " ('{$user_id}','{$k}','{$v}') ";
			}
		}
		if(!isset($approver)){
			$data .= ", ('{$user_id}','approver','off') ";
		}
		
		$sql = "INSERT INTO `employee_meta` (`user_id`,`meta_field`,`meta_value`) VALUES {$data} ";
		$save = $this->conn->query($sql);
		$this->capture_err();
		if($save){
			$resp['status'] = 'success';
			$resp['id'] = $user_id;
			if(empty($id))
				$this->settings->set_flashdata('success',"Nouveau employé sauvegardé avec succès.");
			else
				$this->settings->set_flashdata('success',"Les détails de l'employé ont été mis à jour.");
			$dir = 'uploads/';
			if(!is_dir(base_app.$dir))
				mkdir(base_app.$dir);
			if(isset($_FILES['img'])){
				if(!empty($_FILES['img']['tmp_name']) && isset($_SESSION['userdata']) && isset($_SESSION['system_info'])){
					$fname = $dir.$user_id."_user.".(pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION));
					$move =  move_uploaded_file($_FILES['img']['tmp_name'],base_app.$fname);
					if($move){
						$this->conn->query("UPDATE `users` set `avatar` = '{$fname}' where id ='{$user_id}' ");
						if(!empty($avatar) && is_file(base_app.$avatar))
							unlink(base_app.$avatar);
					}
				}
			}
		}else{
			$resp['status'] = 'failed';
			$resp['err'] = $this->conn->error."[{$sql}]";
		}
		return json_encode($resp);
	}
	function reset_password(){
		extract($_POST);
		$employee_id = $this->conn->query("SELECT meta_value FROM `employee_meta` where meta_field = 'employee_id' and user_id = '{$id}'")->fetch_array()['meta_value'];
		$this->capture_err();
		$update = $this->conn->query("UPDATE `users` set `password` = md5('{$employee_id}') where id = '{$id}'");
		$this->capture_err();
		$resp['status']='success';
		$this->settings->set_flashdata('success',"Mot de passe de l'utilisateur mis à jour avec succès");
		return json_encode($resp);
	}
	function delete_img(){
		extract($_POST);
		if(is_file(base_app.$path)){
			if(unlink(base_app.$path)){
				$del = $this->conn->query("DELETE FROM `uploads` where file_path = '{$path}'");
				$resp['status'] = 'success';
			}else{
				$resp['status'] = 'failed';
				$resp['error'] = 'failed to delete '.$path;
			}
		}else{
			$resp['status'] = 'failed';
			$resp['error'] = 'Unkown '.$path.' path';
		}
		return json_encode($resp);
	}
	function update_status(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k =>$v){
			if(!in_array($k,array('id'))){
				$v = addslashes($v);
				if(!empty($data)) $data .=",";
				$data .= " `{$k}`='{$v}' ";
			}
		}
		$sql = "UPDATE `leave_applications` set {$data} where id = '{$id}' ";
		$save = $this->conn->query($sql);
		$this->capture_err();
		$resp['status'] = 'success';
		return json_encode($resp);
	}

	// Sauvegarde ou mise à jour des heures de travail
	function save_work_hours() {
    extract($_POST);
    $user_id = intval($user_id);
    $work_date = $this->conn->real_escape_string($work_date);
    $start_hour = $this->conn->real_escape_string($start_hour);
    $end_hour = $this->conn->real_escape_string($end_hour);

    // Vérifier si une entrée existe déjà pour ce user/date
    $chk = $this->conn->query("SELECT id FROM work_hours WHERE user_id = {$user_id} AND work_date = '{$work_date}'");
    if ($chk->num_rows > 0) {
        // Mise à jour
        $row = $chk->fetch_assoc();
        $sql = "UPDATE work_hours SET start_hour = '{$start_hour}', end_hour = '{$end_hour}' WHERE id = {$row['id']}";
    } else {
        // Insertion
        $sql = "INSERT INTO work_hours (user_id, start_hour, end_hour, work_date) VALUES ({$user_id}, '{$start_hour}', '{$end_hour}', '{$work_date}')";
    }
    $save = $this->conn->query($sql);
    if($save){
        $resp['status'] = 'success';
        $this->settings->set_flashdata('success',"Heures de travail sauvegardées.");
    }else{
        $resp['status'] = 'failed';
        $resp['err'] = $this->conn->error."[{$sql}]";
    }
    return json_encode($resp);
}

	// Récupérer les heures par employé et période
	function get_work_hours() {
    extract($_POST);
    $user_id = intval($user_id);
    $date_start = $this->conn->real_escape_string($date_start);
    $date_end = $this->conn->real_escape_string($date_end);

    $qry = $this->conn->query("SELECT work_date, start_hour, end_hour FROM work_hours WHERE user_id = {$user_id} AND work_date BETWEEN '{$date_start}' AND '{$date_end}' ORDER BY work_date ASC");
    $data = [];
    while($row = $qry->fetch_assoc()) {
        $data[] = $row;
    }
    return json_encode(['status'=>'success', 'data'=>$data]);
}
}

$Master = new Master();
$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
$sysset = new SystemSettings();
switch ($action) {
	case 'save_department':
		echo $Master->save_department();
	break;
	case 'delete_department':
		echo $Master->delete_department();
	break;
	case 'save_designation':
		echo $Master->save_designation();
	break;
	case 'delete_designation':
		echo $Master->delete_designation();
	break;
	case 'upload_files':
		echo $Master->upload_files();
	break;
	case 'save_employee':
		echo $Master->save_employee();
	break;
	case 'reset_password':
		echo $Master->reset_password();
	break;
	case 'update_status':
		echo $Master->update_status();
	break;
	case 'delete_img':
		echo $Master->delete_img();
	break;
	case 'save_work_hours':
    echo $Master->save_work_hours();
    break;
	case 'get_work_hours':
    echo $Master->get_work_hours();
    break;
	default:
		// echo $sysset->index();
	break;
}