<?php
require_once '../config.php';

class Login extends DBConnection {
    private $settings;

    public function __construct(){
        global $_settings;
        $this->settings = $_settings;

        parent::__construct();
        ini_set('display_error', 1);
    }

    public function __destruct(){
        parent::__destruct();
    }

    public function index(){
        echo "<h1>Access Denied</h1> <a href='".base_url."'>Go Back.</a>";
    }

    public function login(){
        extract($_POST);

        // Usamos una consulta preparada para evitar inyección SQL
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ? AND password = md5(?)");
        
        // Vinculamos los parámetros
        $stmt->bind_param("ss", $username, $password);

        // Ejecutamos la consulta
        $stmt->execute();
        
        // Obtenemos el resultado
        $qry = $stmt->get_result();

        if ($qry->num_rows > 0) {
            // Si encontramos el usuario, almacenamos los datos en la sesión
            foreach($qry->fetch_array() as $k => $v){
                if (!is_numeric($k) && $k != 'password') {
                    $this->settings->set_userdata($k, $v);
                }
            }

            // Establecemos el tipo de sesión
            $this->settings->set_userdata('login_type', 1);
            return json_encode(array('status' => 'success'));
        } else {
            return json_encode(array('status' => 'incorrect', 'last_qry' => "SELECT * FROM users WHERE username = '$username' AND password = md5('$password')"));
        }
    }

    public function logout(){
        if ($this->settings->sess_des()) {
            redirect('admin/login.php');
        }
    }
}

$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
$auth = new Login();

switch ($action) {
    case 'login':
        echo $auth->login();
        break;
    case 'logout':
        echo $auth->logout();
        break;
    default:
        echo $auth->index();
        break;
}
?>
