<?php
namespace ScreenMatch\Modelo;

trait ComAvaliacao
{
    private array $notas = [];

    public function avalia(float $nota): void
    {
        $this->notas[] = $nota;
    }

    public function media(): float
    {
        if (count($this->notas) === 0) {
            return 0.0;
        }
        return array_sum($this->notas) / count($this->notas);
    }
}