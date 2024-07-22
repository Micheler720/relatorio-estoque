<?php
namespace App\Models;
use CodeIgniter\Model;

abstract class ModelBase extends Model{

  function obterResultado($query)
  {
    $result = [];
    $queryResult = $query->getResult('array');
    foreach ($queryResult as $row)
    {
      $result [] = $row;
    }
    return $result;
  }
}