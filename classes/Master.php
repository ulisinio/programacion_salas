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

    // ==================== SALAS ====================
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
            $resp['msg'] = "La Sala ya existe.";
        } else {
            if(empty($id)){
                $sql = "INSERT INTO `assembly_hall` SET $data";
            } else {
                $sql = "UPDATE `assembly_hall` SET $data WHERE id = {$id}";
            }

            $save = $this->conn->query($sql);
            $this->capture_err();

            if($save){
                $resp['status'] = "success";
                $this->settings->set_flashdata('success', "Sala guardada exitosamente.");
            } else {
                $resp['status'] = "failed";
                $resp['sql'] = $sql;
            }
        }

        return json_encode($resp);
    }
function delete_assembly_hall(){
    $assembly_hall_id = $_POST['id'];

    // Elimina las reservas asociadas
    $this->conn->query("DELETE FROM `schedule_list` WHERE assembly_hall_id = '{$assembly_hall_id}'");

    // Elimina la sala
    $sql = "DELETE FROM `assembly_hall` WHERE id = '{$assembly_hall_id}'";
    $delete = $this->conn->query($sql);
    $this->capture_err();

    if($delete){
        $resp['status'] = 'success';
        $this->settings->set_flashdata('success', "Sala eliminada exitosamente.");
    } else {
        $resp['status'] = "failed";
        $resp['msg'] = "No se pudo eliminar la sala.";
    }

    return json_encode($resp);
}


    // ==================== VEHÍCULOS ====================
    function save_vehicle(){
        extract($_POST);
        $data = "";
        foreach($_POST as $k => $v){
            if($k != 'id'){
                if(!empty($data)) $data .= ", ";
                $data .= " {$k} = '{$this->conn->real_escape_string($v)}'";
            }
        }

        $check = $this->conn->query("SELECT * FROM `vehicles` WHERE `no_serie` = '{$no_serie}' ".(!empty($id) ? "AND id != {$id}" : ''))->num_rows;
        $this->capture_err();

        if($check > 0){
            $resp['status'] = 'failed';
            $resp['msg'] = "Ya existe un vehículo con este número de serie.";
        } else {
            if(empty($id)){
                $sql = "INSERT INTO `vehicles` SET {$data}";
            } else {
                $sql = "UPDATE `vehicles` SET {$data} WHERE id = '{$id}'";
            }

            $save = $this->conn->query($sql);
            $this->capture_err();

            if($save){
                $resp['status'] = 'success';
                $this->settings->set_flashdata('success', "Vehículo guardado exitosamente.");
            } else {
                $resp['status'] = 'failed';
                $resp['sql'] = $sql;
            }
        }

        return json_encode($resp);
    }

    function delete_vehicle(){
        extract($_POST);

        $delete = $this->conn->query("DELETE FROM `vehicles` WHERE id = '{$id}'");
        $this->capture_err();

        if($delete){
            $resp['status'] = 'success';
            $this->settings->set_flashdata('success', "Vehículo eliminado exitosamente.");
        } else {
            $resp['status'] = 'failed';
            $resp['error'] = $this->conn->error;
        }

        return json_encode($resp);
    }

    // ==================== RESERVAS ====================
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
            $d_start = strtotime($datetime_start);
            $d_end = strtotime($datetime_end);

            $chk = $this->conn->query("SELECT * FROM `schedule_list` 
                WHERE assembly_hall_id = '{$assembly_hall_id}' AND 
                (
                    (UNIX_TIMESTAMP(datetime_start) < '{$d_end}' AND UNIX_TIMESTAMP(datetime_end) > '{$d_start}')
                )
                " . (($id > 0) ? " AND id !='{$id}' " : ""))->num_rows;

            if($chk > 0){
                $resp['status'] = 'failed';
                $resp['err_msg'] = "El horario entra en conflicto con otros horarios en la misma sala.";
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
            $this->settings->set_flashdata('success', "Reserva eliminada con éxito.");
        } else {
            $resp['status'] = 'failed';
            $resp['error'] = $this->conn->error;
        }
        return json_encode($resp);
    }
    // Guardar reserva de vehículo
function save_vehicle_reservation() {
    extract($_POST);
    $data = "";

    foreach($_POST as $k => $v){
        if($k != 'id'){
            if(!empty($data)) $data .= ", ";
            $data .= " {$k} = '{$this->conn->real_escape_string($v)}'";
        }
    }

    // Validar solapamiento de reservas del mismo vehículo
    $d_start = strtotime($datetime_start);
    $d_end = strtotime($datetime_end);

    $chk = $this->conn->query("SELECT * FROM `vehicle_reservations` 
        WHERE vehicle_id = '{$vehicle_id}' AND 
        (
            (UNIX_TIMESTAMP(datetime_start) < '{$d_end}' AND UNIX_TIMESTAMP(datetime_end) > '{$d_start}')
        )
        " . (($id > 0) ? " AND id !='{$id}' " : ""))->num_rows;

    if($chk > 0){
        $resp['status'] = 'failed';
        $resp['err_msg'] = "El vehículo ya está reservado en ese horario.";
    } else {
        if(empty($id)){
            $sql = "INSERT INTO `vehicle_reservations` SET {$data}";
        } else {
            $sql = "UPDATE `vehicle_reservations` SET {$data} WHERE id = '{$id}'";
        }

        $save = $this->conn->query($sql);
        if($save){
            $resp['status'] = 'success';
            $this->settings->set_flashdata('success', "Reserva guardada exitosamente.");
        } else {
            $resp['status'] = 'failed';
            $resp['sql'] = $sql;
            $resp['qry_error'] = $this->conn->error;
            $resp['err_msg'] = "Error al guardar la reserva.";
        }
    }

    return json_encode($resp);
}

// Eliminar reserva de vehículo
function delete_vehicle_reservation() {
    extract($_POST);
    $delete = $this->conn->query("DELETE FROM `vehicle_reservations` WHERE id = '{$id}'");
    if($delete){
        $resp['status'] = 'success';
        $this->settings->set_flashdata('success', "Reserva eliminada exitosamente.");
    } else {
        $resp['status'] = 'failed';
        $resp['error'] = $this->conn->error;
    }
    return json_encode($resp);
}
}


// ========== Enrutador ==========

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
    case 'save_vehicle':
        echo $Master->save_vehicle();
        break;
    case 'delete_vehicle':
        echo $Master->delete_vehicle();
        break;
    case 'save_schedule':
        echo $Master->save_schedule();
        break;
    case 'delete_sched':
        echo $Master->delete_sched();
        break;
        case 'save_vehicle_reservation':
    echo $Master->save_vehicle_reservation();
    break;
case 'delete_vehicle_reservation':
    echo $Master->delete_vehicle_reservation();
    break;

    default:
        // echo $sysset->index(); // puede activarse si es necesario
        break;
}
?>
