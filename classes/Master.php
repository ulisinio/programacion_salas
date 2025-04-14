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
            if(isset($sql))
                $resp['sql'] = $sql;
            return json_encode($resp);
            exit;
        }
    }

    function save_assembly(){
        extract($_POST);
        $data = "";
        $_POST['description'] = addslashes(htmlentities($_POST['description']));
        foreach($_POST as $k => $v){
            if($k != 'id'){
                if(!empty($data)) $data .= ", ";
                $data .= " {$k} = '{$v}'";
            }
        }

        $check = $this->conn->query("SELECT * FROM `assembly_hall` WHERE `room_name` = '{$room_name}' ".(!empty($id) ? "AND id != {$id}" : ''))->num_rows;
        $this->capture_err();
        
        if($check > 0){
            $resp['status'] = 'failed';
            $resp['msg'] = "La Sala ya existe."; // Cambio aquí
        } else {
            if(empty($id)){
                $sql = "INSERT INTO `assembly_hall` SET $data";
                $save = $this->conn->query($sql);
            } else {
                $sql = "UPDATE `assembly_hall` SET $data WHERE id = {$id}";
                $save = $this->conn->query($sql);
            }
            $this->capture_err();

            if($save){
                $resp['status'] = "success";
                $this->settings->set_flashdata('success', "Sala guardada exitosamente."); // Cambio aquí
            } else {
                $resp['status'] = "failed";
                $resp['sql'] = $sql;
            }
        }
        return json_encode($resp);
    }

	function delete_assembly_hall(){
		// Verificar si la sala tiene reservas actuales o futuras
		$assembly_hall_id = $_POST['id'];
		$current_datetime = date('Y-m-d H:i:s');  // Fecha y hora actual
	
		// Consultar si existen reservas actuales o futuras para la sala
		$chk_reservations = $this->conn->query("SELECT * FROM `schedule_list` 
												 WHERE assembly_hall_id = '{$assembly_hall_id}' 
												 AND datetime_start >= '{$current_datetime}'")->num_rows;
	
		// Si existen reservas activas o futuras, no se elimina la sala
		if ($chk_reservations > 0) {
			$resp['status'] = 'failed';
			$resp['err_msg'] = "No se puede eliminar la sala porque tiene reservas actuales o futuras.";
		} else {
			// Proceder a eliminar la sala si no tiene reservas
			$sql = "DELETE FROM `assembly_hall` WHERE id = '{$assembly_hall_id}' ";
			$delete = $this->conn->query($sql);
			$this->capture_err();
	
			if($delete){
				$resp['status'] = 'success';
				$this->settings->set_flashdata('success', "Sala eliminada exitosamente.");
			} else {
				$resp['status'] = "failed";
				$resp['sql'] = $sql;
			}
		}
	
		return json_encode($resp);
	}
	
	
	
	
	

	function save_schedule(){
		extract($_POST);
		$data = "";
		
		foreach($_POST as $k => $v){
			if($k != 'id'){
				if(!empty($data)) $data .= ", ";
				$data .= " {$k} = '{$v}'";
			}
		}
	
		if(strtotime($datetime_end) < strtotime($datetime_start)){
			$resp['status'] = 'failed';
			$resp['err_msg'] = "La fecha y hora del horario no son válidas.";
		} else {
			// Convertir las fechas a formato timestamp UNIX
			$d_start = strtotime($datetime_start);
			$d_end = strtotime($datetime_end);
			
			// Consulta para verificar si hay conflicto en las fechas
			$chk = $this->conn->query("SELECT * FROM `schedule_list` 
				WHERE ( 
					(UNIX_TIMESTAMP(datetime_start) < '{$d_end}' AND UNIX_TIMESTAMP(datetime_end) > '{$d_start}')
				) " . (($id > 0) ? " AND id !='{$id}' " : ""))->num_rows;
	
			// Si hay un conflicto de horarios, no se guarda la reserva
			if($chk > 0){
				$resp['status'] = 'failed';
				$resp['err_msg'] = "El horario entra en conflicto con otros horarios.";
			} else {
				if(empty($id)){
					$sql = "INSERT INTO `schedule_list` SET {$data}";
				} else {
					$sql = "UPDATE `schedule_list` SET {$data} WHERE id = '{$id}'";
				}
	
				$save = $this->conn->query($sql);
				if($save){
					$resp['status'] = 'success';
					$this->settings->set_flashdata('success', "Horario guardado exitosamente.");
				} else {
					$resp['status'] = 'failed';
					$resp['sql'] = $sql;
					$resp['qry_error'] = $this->conn->error;
					$resp['err_msg'] = "Hubo un error al enviar los datos.";
				}
			}
		}
	
		return json_encode($resp);
	}
	

    function delete_sched(){
        extract($_POST);
        $delete = $this->conn->query("DELETE FROM `schedule_list` WHERE id = '{$id}'");
        if($delete){
            $resp['status'] = 'success';
            $this->settings->set_flashdata('success', "Reserva eliminada con éxito."); // Cambio aquí
        } else {
            $resp['status'] = 'failed';
            $resp['error'] = $this->conn->error;
        }
        return json_encode($resp);
    }
}

$Master = new Master();
$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
$sysset = new SystemSettings();
switch ($action) {
    case 'save_assembly':
        echo $Master->save_assembly();
        break;
    case 'delete_assembly_hall':
        echo $Master->delete_assembly_hall();
        break;
    case 'save_schedule':
        echo $Master->save_schedule();
        break;
    case 'delete_sched':
        echo $Master->delete_sched();
        break;
    default:
        // echo $sysset->index();
        break;
}
?>
