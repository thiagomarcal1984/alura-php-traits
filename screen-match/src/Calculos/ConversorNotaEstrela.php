<?php
namespace ScreenMatch\Calculos;

class ConversorNotaEstrela 
{
    public static function converte(\ScreenMatch\Modelo\Avaliavel $avaliavel): float
    {
        $nota = $avaliavel->media();
        return round($nota) / 2;
    }
}