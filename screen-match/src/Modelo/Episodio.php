<?php

class Episodio implements Avaliavel
{
    private array $notas;
    
    public function __construct(
        public readonly Serie $serie,
        public readonly string $nome,
        public readonly int $numero,
    ) {
        $this->notas = [];
    }

    public function avalia(float $nota): void
    {
        $this->notas[] = $nota;
    }

    public function media(): float
    {
        $somaNotas = array_sum($this->notas);
        $quantidadeNotas = count($this->notas);
        return $quantidadeNotas > 0 ? $somaNotas / $quantidadeNotas : 0.0;
    }
}
