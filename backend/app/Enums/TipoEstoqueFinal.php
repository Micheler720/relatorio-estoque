<?php
namespace App\Enums;

enum TipoEstoqueFinal: int {
    case Data = 1;
    case Valor = 2;

    public static function data(): int {
        return self::Data->value;
    }

    public static function valor(): int {
        return self::Valor->value;
    }
}
?>