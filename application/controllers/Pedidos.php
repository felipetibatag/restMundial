<?php
require_once (APPPATH . '/libraries/Rest_Controller.php');
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

class Pedidos extends REST_Controller
{

    public function __construct()
    {
        header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
        header("Access-Control-Allow-Origin: *");
        parent::__construct();
        $this->load->database();
    }

    public function realizar_orden_post($token = "0", $id_usuario = "0")
    {
        $data = $this->post();
        if ($token == "0" || $id_usuario == "0") {
            $respuesta = array(
                'error' => TRUE,
                'mensaje' => 'error de validacion y/o token'
            );
            $this->response($respuesta, REST_Controller::HTTP_BAD_REQUEST);
            return;
        }
        
        if (! isset($data['items']) || strlen($data['items']) == 0) {
            $respuesta = array(
                'error' => TRUE,
                'mensaje' => 'Faltan items en la solicitud'
            );
            $this->response($respuesta, REST_Controller::HTTP_BAD_REQUEST);
            return;
        }
        
        // si se llega ac� es por que tiene items, usuario y token en el post
        $condiciones = array(
            'id' => $id_usuario,
            'token' => $token
        );
        $this->db->where($condiciones);
        $query = $this->db->get('login');
        $existe = $query->row();
        if (! $existe) {
            $respuesta = array(
                'error' => TRUE,
                'mensaje' => 'usuario y token inconrrectos'
            );
            $this->response($respuesta, REST_Controller::HTTP_UNAUTHORIZED);
            return;
        }
        
        // si llega ac� es por que usuario y token son correctos
        $this->db->reset_query();
        $insertar = array(
            'usuario_id' => $id_usuario
        );
        $this->db->insert('ordenes', $insertar);
        $orden_id = $this->db->insert_id();
        
        // crear detalle de la orden
        $this->db->reset_query();
        $items = explode(',', $data['items']);
        foreach ($items as &$producto_id) {
            $data_insertar = array(
                'producto_id' => $producto_id,
                'orden_id' => $orden_id
            );
            $this->db->insert('ordenes_detalle', $data_insertar);
        }
        
        $respuesta = array(
            'error' => FALSE,
            'orden_id' => $orden_id
        );
        
        $this->response($respuesta);
    }

    public function obtener_pedidos_get($token = "0", $id_usuario = "0")
    {
        if ($token == "0" || $id_usuario == "0") {
            $respuesta = array(
                'error' => TRUE,
                'mensaje' => 'error de validacion y/o token'
            );
            $this->response($respuesta, REST_Controller::HTTP_BAD_REQUEST);
            return;
        }
        
        // si se llega ac� es por que tiene items, usuario y token en el post
        $condiciones = array(
            'id' => $id_usuario,
            'token' => $token
        );
        $this->db->where($condiciones);
        $query = $this->db->get('login');
        $existe = $query->row();
        if (! $existe) {
            $respuesta = array(
                'error' => TRUE,
                'mensaje' => 'usuario y token inconrrectos'
            );
            $this->response($respuesta, REST_Controller::HTTP_UNAUTHORIZED);
            return;
        }
        
        // si llega ac� es por que usuario y token son correctos, ahora retorna TODAS las ordenes del usuario
        $query = $this->db->query("select* from ordenes where usuario_id=" . $id_usuario);
        $ordenes = array();
        foreach ($query->result() as $row) {
            $consulta_detalle = $this->db->query("select a.orden_id, b.* from ordenes_detalle a inner join productos b on a.producto_id=b.codigo where orden_id=" . $row->id);
            $orden = array(
                'id' => $row->id,
                'creado en' => $row->creado_en,
                'detalle' => $consulta_detalle->result()
            );
            array_push($ordenes, $orden);
        }
        $respuesta = array(
            'error' => FALSE,
            'ordenes' => $ordenes
        );
        
        $this->response($respuesta);
    }

    public function borrar_pedido_delete($token = "0", $id_usuario = "0", $orden_id = "0"){
        if ($token == "0" || $id_usuario == "0"||$orden_id=="0") {
            $respuesta = array(
                'error' => TRUE,
                'mensaje' => 'error de validacion, token y/o orden'
            );
            $this->response($respuesta, REST_Controller::HTTP_BAD_REQUEST);
            return;
        }
        
        // si se llega ac� es por que tiene items, usuario y token en el post
        $condiciones = array(
            'id' => $id_usuario,
            'token' => $token
        );
        $this->db->where($condiciones);
        $query = $this->db->get('login');
        $existe = $query->row();
        if (! $existe) {
            $respuesta = array(
                'error' => TRUE,
                'mensaje' => 'usuario y token inconrrectos'
            );
            $this->response($respuesta, REST_Controller::HTTP_UNAUTHORIZED);
            return;
        }
     //si llega �ca es por que el usuario y token existe en la base de datos.
    //verificar si la orden a borrar es del  usuario que esta activo
        $this->db->reset_query();
        $condiciones=array('id'=>$orden_id,'usuario_id'=>$id_usuario);
        $this->db->where($condiciones);
        $query=$this->db->get('ordenes');
        $existe=$query->row();
        if(!$existe){
            $respuesta = array(
                'error' => TRUE,
                'ordenes' => 'Esa orden no puede ser borrada'
            );
            $this.response($respuesta);
            return;
        }
        
        //si llega �ca es por que si es del usuario la orden a borrar, y se procede a borrar
        $condiciones=array('id'=>$orden_id);
        $this->db->delete('ordenes',$condiciones);
        $condiciones=array('orden_id'=>$orden_id);
        $this->db->delete('ordenes_detalle',$condiciones);
        $respuesta=array('error'=>FALSE,'mensaje'=>'orden eliminada');
        
        $this->response($respuesta);

        
    }
}
?>