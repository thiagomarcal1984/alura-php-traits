<?php
namespace ScreenMatch\Exception;

class NotaInvalidaException extends \InvalidArgumentException
{
    // Podemos manter a classe vazia, pois ela herda de InvalidArgumentException.
    public function __construct()
    {
        parent::__construct("A nota precisa estar entre 0 e 10.");
    }
}
