<?php 
namespace App\Controllers\Api;

use App\Controllers\BaseController;
class DRE extends BaseController {

    private $errors = [];

    private $rules = [
        'dataInventarioEstoqueInicial' => 'required|valid_date[Y-m-d]',
        'dataInventarioEstoqueFinal' => 'required|valid_date[Y-m-d]',
        'empresa' => 'required|is_natural_no_zero',
        'mes' => 'required',
        'ano' => 'required',
    ];

    public function getDRE(){
        $request = $this->request->getJSON();

        if(!$this->validate($this->rules)){            
            return $this->badrequest_response($this->validator->getErrors());
        }
        $validData = $this->validator->getValidated();

        $dataInventarioEstoqueInicial = $validData['dataInventarioEstoqueInicial'];
        $dataInventarioEstoqueFinal = $validData['dataInventarioEstoqueFinal'];
        $empresa = $validData['empresa'];
        $mes = $validData['mes'];
        $ano = $validData['ano'];

        if(!$this->isDatesValid($dataInventarioEstoqueInicial, $dataInventarioEstoqueFinal)){
            return $this->badrequest_response($this->errors);
        }

        $dataInicial = $ano."-".$mes."-01";
        $dataFinal = $ano."-".$mes."-31";

        $model = model('DREModel');        
        return  $this->success_response($model->getRelatorio(
            $dataInicial, 
            $dataFinal, 
            $empresa, 
            $dataInventarioEstoqueInicial, 
            $dataInventarioEstoqueFinal));
    }

    private function isDatesValid($initialDate, $endDate): bool
    {
        if($initialDate > $endDate){

            $this->errors = [
                'dataInventarioEstoqueInicial' => 'Data do invventário inicial não pode ser maior que a data final do inventário.'
            ];

            return false;
        }

        return true;
    }
} 