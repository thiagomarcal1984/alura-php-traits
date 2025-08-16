<?php

class ConversorNotaEstrela 
{
    public static function converte(Avaliavel $avaliavel): float
    {
        $nota = $avaliavel->media();
        return round($nota) / 2;
    }
}