<?php 
namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\RelatorioDespesaModel;

class RelatorioDespesas extends BaseController {

    private $errors = [];

    private $rules = [
        'dataInicial' => 'required|valid_date[Y-m-d]',
        'dataFinal' => 'required|valid_date[Y-m-d]',
        'filial' => 'required|is_natural_no_zero'
    ];

    public function getRelatorioDespesas(){
        $request = $this->request->getJSON();

        if(!$this->validate($this->rules)){            
            return $this->badrequest_response($this->validator->getErrors());
        }
        $validData = $this->validator->getValidated();

        if(!$this->isDatesValid($validData['dataInicial'], $validData['dataFinal'])){
            return $this->badrequest_response($this->errors);
        }

        $model = model('RelatorioDespesaModel');        
        return  $this->success_response($model->getRelatorio());
    }

    private function isDatesValid($initialDate, $endDate): bool
    {
        if($initialDate > $endDate){

            $this->errors = [
                'dataInicial' => 'Data inicial não pode ser maior que a data final'
            ];

            return false;
        }

        return true;
    }
} 