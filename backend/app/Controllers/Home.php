<?php

namespace App\Controllers;

use App\Models\RelatorioDespesaModel;

class Home extends BaseController
{
    public function index(): string
    {
        return view("welcome_message");
    }

    public function getRelatorioDespesas()
    {
        $request = $this->request->getJSON();

        return $this->success_response("");

        if(!isRequestValid($request)){
            return $this-> badrequest_response($erros);
        }

        $model = model('RelatorioDespesaModel');        
        return  $this->success_response($model->obterRelatorio());
    }
}
