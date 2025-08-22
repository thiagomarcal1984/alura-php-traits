<?php
namespace ScreenMatch\Calculos;

class ConversorNotaEstrela 
{
    public static function converte(\ScreenMatch\Modelo\Avaliavel $avaliavel): float
    {
        try {
            $nota = $avaliavel->media();
            return round($nota) / 2;
        } catch (\Throwable) { 
            // O PHP não força a declaração da variável $erro.
            return 0.0;
        }
    }
}