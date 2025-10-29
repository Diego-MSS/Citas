<?php
require_once __DIR__ . '/../Modelos/CitasModel.php';

class CitasController{
    public function index(){

    }
    public function citas($id){
        $citas = CitasModel::getByUsuario($id);
        require VIEWS_PATH . '/citas.php';
    }
}