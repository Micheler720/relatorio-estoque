<?php
namespace App\Models;
use App\Models\ModelBase;

class EmpresaModel extends ModelBase {
    protected $table = 'empresa';
    protected $allowedFields  = [ 'filial', 'empresa' ];

    public function getEmpresas() {
        $sql = "SELECT emp_codigo as filial, emp_id as id from empresa";

        $query = $this->db->query($sql);
        return $this->obterResultado($query);
    }


}

?>