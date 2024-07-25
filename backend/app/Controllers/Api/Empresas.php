<?php 
namespace App\Controllers\Api;

use App\Controllers\BaseController;

class Empresas extends BaseController {
    public function get(){
        $model = model('EmpresaModel');        
        return  $this->success_response($model->getEmpresas());
    }
}

?>