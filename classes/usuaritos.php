<?php
require_once('../config.php'); // Incluir la configuración de la base de datos

class Usuaritos extends DBConnection {
    private $settings;
    
    public function __construct() {
        global $_settings;
        $this->settings = $_settings;
        parent::__construct();
    }

    public function __destruct() {
        parent::__destruct();
    }

    // Método para capturar errores de la base de datos
    function capture_err() {
        if (!$this->conn->error)
            return false;
        else {
            $resp['status'] = 'failed';
            $resp['error'] = $this->conn->error;
            if (isset($sql))
                $resp['sql'] = $sql;
            echo json_encode($resp);
            exit;
        }
    }

    // Método para guardar o actualizar un usuario
    function save_user() {
        extract($_POST);
        $data = "";

        // Sanitización de los datos
        $_POST['firstname'] = addslashes(htmlentities($_POST['firstname']));
        $_POST['lastname'] = addslashes(htmlentities($_POST['lastname']));
        $_POST['username'] = addslashes(htmlentities($_POST['username']));

        // Si la contraseña no está vacía, la encriptamos con MD5
        if (!empty($_POST['password'])) {
            $_POST['password'] = md5($_POST['password']);  // Encriptamos la contraseña con MD5
        }

        // Preparamos los datos para la inserción o actualización
        foreach ($_POST as $k => $v) {
            if ($k != 'id') {
                if (!empty($data)) $data .= ", ";
                $data .= " {$k} = '{$v}'";
            }
        }

        // Verificamos si el usuario ya existe
        $check = $this->conn->query("SELECT * FROM `users` WHERE `username` = '{$username}' ".(!empty($id) ? "AND id != {$id}" : ''))->num_rows;
        $this->capture_err();

        if ($check > 0) {
            $resp['status'] = 'failed';
            $resp['msg'] = "El nombre de usuario ya existe.";
        } else {
            // Si es un nuevo usuario, lo insertamos
            if (empty($id)) {
                $sql = "INSERT INTO `users` SET $data";
                $save = $this->conn->query($sql);
            } else {
                // Si es un usuario existente, lo actualizamos
                $sql = "UPDATE `users` SET $data WHERE id = {$id}";
                $save = $this->conn->query($sql);
            }

            $this->capture_err();

            // Respuesta según si la operación fue exitosa o no
            if ($save) {
                $resp['status'] = "success";
                $this->settings->set_flashdata('success', "Usuario guardado correctamente.");
            } else {
                $resp['status'] = "failed";
                $resp['sql'] = $sql;
            }
        }

        echo json_encode($resp);  // Retornar respuesta en formato JSON
    }

    // Método para eliminar un usuario
    function delete_user() {
        extract($_POST);
        $sql = "DELETE FROM `users` WHERE id = '{$id}'";
        $delete = $this->conn->query($sql);
        $this->capture_err();

        if ($delete) {
            $resp['status'] = 'success';
            $this->settings->set_flashdata('success', "Usuario eliminado con éxito.");
        } else {
            $resp['status'] = 'failed';
            $resp['error'] = $this->conn->error;
        }

        echo json_encode($resp);  // Retornar respuesta en formato JSON
    }
}

// Inicializar la clase Usuaritos
$Usuaritos = new Usuaritos();
$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
$sysset = new SystemSettings();

// Procesar la acción solicitada
switch ($action) {
    case 'save_user':
        echo $Usuaritos->save_user();
        break;
    case 'delete_user':
        echo $Usuaritos->delete_user();
        break;
    default:
        // Acción no encontrada, no hacer nada
        break;
}
?>