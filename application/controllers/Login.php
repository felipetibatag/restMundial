<?php
require_once (APPPATH.'/libraries/Rest_Controller.php');
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends REST_Controller {
    
    public function __construct(){
        header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
        header("Access-Control-Allow-Origin: *");
        parent::__construct();
        $this->load->database();
    }
    
    public function index_post(){        
        $data=$this->post();
        if(!isset($data['correo'])OR !isset($data['contrasena'])){
            $respuesta=array(
                'error'=>TRUE,
                'mensaje'=>'Usuario no valido'
            );
            $this->response($respuesta,REST_Controller::HTTP_BAD_REQUEST);
            return;
        }
        // si llega después de acá es por que hay algo en el POST
        $condiciones=array('correo'=>$data['correo'],'contrasena'=>$data['contrasena']);
        $query=$this->db->get_where('login',$condiciones);
        $usuario=$query->row();
        if(!isset($usuario)){
            $respuesta=array(
                'error'=>TRUE,
                'mensaje'=>'Usuario y/o clave invalido'
            );
            $this->response($respuesta);
            return;
        }
        
        //si llega acá es por que el usuario y clave están bien.
        //generar token
        //$token=bin2hex(openssl_random_pseudo_bytes(20));//aleatorio
          $token=hash('ripemd160',$data['correo']);//siempre será el mismo
        
        //guardar ahora en base dedatos el token
        $this->db->reset_query();//limpiar cualquier consulta que este por ahí en caché
        $actualizar_token=array( 'token'=>$token );
        $this->db->where('id', $usuario->id);
        $hecho=$this->db->update('login',$actualizar_token);
        $respuesta=array(
            'error'=>FALSE,
            'token'=>$token,
            'id_usuario'=> $usuario->id
        );
        
        $this->response($respuesta);
    }
}
?>