<?php
namespace ScreenMatch\Calculos;

class CalculadoraDeMaratona
{
    private int $duracaoMaratona = 0;

    public function inclui(\ScreenMatch\Modelo\Titulo $titulo): void
    {
        $this->duracaoMaratona += $titulo->duracaoEmMinutos();
    }

    public function duracao(): int
    {
        return $this->duracaoMaratona;
    }
}
