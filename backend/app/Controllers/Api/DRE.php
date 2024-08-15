<?php 
namespace App\Controllers\Api;

use App\Controllers\BaseController;
class DRE extends BaseController {

    private $errors = [];

    private $rules = [
        'dataInventarioEstoqueInicial' => 'required|valid_date[Y-m-d]',
        'dataInventarioEstoqueFinal' => 'required|valid_date[Y-m-d]',
        'empresa' => 'required|is_natural_no_zero',
        'dataInicial' => 'required|valid_date[Y-m-d]',
        'dataFinal' => 'required|valid_date[Y-m-d]',
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
        $dataInicial = $validData['dataInicial'];
        $dataFinal = $validData['dataFinal'];

        if(!$this->isDatesValid($dataInventarioEstoqueInicial, $dataInventarioEstoqueFinal)){
            $this->errors = [
                'dataInventarioEstoqueInicial' => 'A data do inventário inicial não pode ser maior que a data final do inventário.'
            ];
            return $this->badrequest_response($this->errors);
        }

        if(!$this->isDatesValid($dataInicial, $dataFinal)){
            $this->errors = [
                'dataFinal' => 'A data do inicial não pode ser maior que a data final.'
            ];
            
            return $this->badrequest_response($this->errors);
        }

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
            return false;
        }

        return true;
    }
} 