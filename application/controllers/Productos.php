<?php
require_once (APPPATH.'/libraries/Rest_Controller.php');
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') OR exit('No direct script access allowed');

class Productos extends REST_Controller {
    
    public function __construct(){
        header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
        header("Access-Control-Allow-Origin: *");
        parent::__construct();
        $this->load->database();
    }
    
    public function todos_get($pagina=0){
        $pagina=$pagina*10;
        $query=$this->db->query('select * from productos limit '.$pagina.',10');
        $respuesta=array(
            'error'=>FALSE,
            'productos'=>$query->result_array()
        );
        $this->response($respuesta);
    }
    
    public function por_tipo_get($tipo=0,$pagina=0){
        //si es 0 informar error
        $respuesta=array();
        if($tipo<=0){
            $respuesta=array(
                'error'=>TRUE,
                'productos'=>'Tipo invalido'
            );
        }else{
            $pagina=$pagina*10;
            $c_sql='select * from productos where linea_id='.$tipo.' limit '.$pagina.',10';
            $query=$this->db->query($c_sql);
            $respuesta=array(
                'error'=>FALSE,
                'productos'=>$query->result_array()
            );
        }
       $this->response($respuesta);
        
        
    }
    public function buscar_get($termino="Sin especificar"){
        $c_query="select * from productos where producto like '% $termino%'";
        $query=$this->db->query($c_query);
        $respuesta=array(
            'error'=>FALSE,
            'termino'=>$termino,
            'productos'=>$query->result_array()
        );
        $this->response($respuesta);
    }
    
}
?>