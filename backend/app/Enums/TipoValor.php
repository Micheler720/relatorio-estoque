<?php
namespace App\Enums;

enum TipoValor: int {
    case Dinheiro = 1;
    case Perentual = 2;
    case Numero = 3;

    public static function dinheiro(): int {
        return self::Dinheiro->value;
    }

    public static function percentual(): int {
        return self::Perentual->value;
    }

    public static function numero(): int {
        return self::Numero->value;
    }
}
?>