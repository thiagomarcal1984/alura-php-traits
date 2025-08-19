# Corrigindo a modelagem
## Removendo a implementação
O método `duracaoEmMinutos` da classe `Titulo` tem uma implementação inicial que não faz muito sentido (título com duração de zero minutos).

Passos tomados nesta aula: 
1. remover essa implementação inicial da classe `Titulo`;
2. marcar o método `duracaoEmMinutos` da classe `Titulo` como `abstract`; e 
3. forçar as subclasses de `Titulo` a implementar o método `duracaoEmMinutos` com a propriedade `#[\Override]`.

> 1. Marcar o método abstrato com `abstract` não basta para fazer o código funcionar. A aula ainda não deixa explícito, mas a declaração da classe precisa ser prefixada com `abstract`, assim: `abstract class Titulo {}`.
> 2. `Override` é uma anotação que serve para garantir que os métodos da classe abstrata sejam realmente sobrescritos - afinal pode haver erros na definição do método abstrato na subclasse e a anotação traz essa prevenção. 
> 3. A anotação `Override` está no namespace raiz. Para prevenir falhas na referência a esta anotação (caso a classe implementada esteja dentro de um outro namespace), prefixamos a anotação com a contrabarra.

```PHP
// src/Modelo/Titulo.php
<?php

class Titulo
{
    // Resto do código
    abstract public function duracaoEmMinutos(): int;
}
```

```PHP
// src/Modelo/Filme.php
<?php

class Filme extends Titulo
{
    // Resto do código
    #[\Override]
    public function duracaoEmMinutos(): int
    {
        return $this->duracaoEmMinutos;
    }
}
```

```PHP
// src/Modelo/Serie.php
<?php

class Serie extends Titulo
{
    // Resto do código

    #[\Override]
    public function duracaoEmMinutos(): int
    {
        return $this->temporadas * $this->episodiosPorTemporada * $this->minutosPorEpisodio;
    }
}
```
## Nova abstração
Não há concretamente um Título: ele sempre é concretizado em outros modelos (Série e Filme). Assim como não há concretamente uma Conta: ela sempre é concretizada em tipos específicos de conta (Corrente, Poupança, Investimento etc.).

```PHP
// src/Modelo/Titulo.php
<?php

abstract class Titulo
{
    // Resto do código
    abstract public function duracaoEmMinutos(): int;
}
```
> 1. Classes abstratas **podem** ter métodos abstratos (não é obrigatório ter métodos abstratos);
> 2. Classes abstratas **não** podem ser instanciadas.

# De notas para estrelas
## Modelando Episódio
Um episódio não pode ser modelado como uma subclasse de `Titulo`.

Vamos criar o modelo da classe `Episodio` da seguinte forma (apenas o construtor com propriedades `public readonly`):
```PHP
<?php

class Episodio
{
    public function __construct(
        public readonly Serie $serie,
        public readonly string $nome,
        public readonly int $numero,
    ) {
    }
}
```
## Conversor em estrelas
Vamos criar um conversor de notas em estrelas (transformar uma nota de 0 a 10 em um pontuação de 1 a 5), conforme a classe abaixo:
```PHP
<?php

class ConversorNotaEstrela 
{
    public static function converte(Avaliavel $avaliavel): float
    {
        $nota = $avaliavel->media();
        return round($nota) / 2;
    }
}
```
`Avaliavel` será uma interface a ser definida e implementada nas próximas aulas.

## Extraindo uma interface
Definição da interface `Avaliavel`:
```PHP
<?php

interface Avaliavel
{
    public function avalia(float $nota): void;
    public function media(): float;
}
```

Considere onde implementar uma interface: se todas as subclasses da classe abstrata devem implementar a interface, então "implemente" a interface com o método abstrato.

Veja como vai ficar a classe `Titulo`:
```PHP
<?php

abstract class Titulo implements Avaliavel
{
    // Resto do código
}
```

Agora a implementação concreta da interface na classe `Episodio` (as classes `Serie` e `Filme` não precisaram de mudanças):
```PHP
<?php

class Episodio implements Avaliavel
{
    private array $notas;
    
    public function __construct(
        // Resto do código
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
```

Cuidado com a ordem dos imports! Em `index.php`, as interfaces devem ser importadas **antes** das classes que as implementam!

```PHP
// index.php
<?php

require __DIR__ . "/src/Modelo/Genero.php";

// Importe a interface antes das classes que a implementam!
require __DIR__ . "/src/Modelo/Avaliavel.php"; 

require __DIR__ . "/src/Modelo/Titulo.php";
// Resto do código
require __DIR__ . "/src/Calculos/ConversorNotaEstrela.php";

// Resto do código
$filme->avalia(10);
$filme->avalia(10);
$filme->avalia(5);
$filme->avalia(5);

$serie->avalia(8);

// Resto do código
$conversor = new ConversorNotaEstrela($serie);
echo "Nota em estrelas: " . $conversor->converte($serie) . "\n";
echo "Nota em estrelas: " . $conversor->converte($filme) . "\n";
```
# Mais organização
## Traits
Traits são um mecanismo para reutilizar código sem duplicação. Elas permitem uma espécie de "herança horizontal".

Trait não é um tipo: é um código para reúso. Ela não pode servir como retorno de um método, por exemplo.

Vamos criar a trait `ComAvaliacao`:
```PHP
// src/Modelo/ComAvaliacao.php
<?php

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
```

O uso da trait é simples: basta inserir na classe o comando `use ComAvaliacao`:
```PHP
// src/Modelo/Titulo.php
<?php

abstract class Titulo implements Avaliavel
{
    use ComAvaliacao;
    
    public function __construct(
        public readonly string $nome,
        public readonly int $anoLancamento,
        public readonly Genero $genero,
    ) {
    }

    abstract public function duracaoEmMinutos(): int;
}
```

> As classes `Filme` e `Serie` não precisam do comando `use ComAvaliacao`, já que elas herdam de `Titulo`.

```PHP
// src/Modelo/Episodio.php
<?php

class Episodio implements Avaliavel
{
    use ComAvaliacao;
    
    public function __construct(
        public readonly Serie $serie,
        public readonly string $nome,
        public readonly int $numero,
    ) {
    }
}
```
> Como a classe `Episodio` não herda de `Titulo`, é necessário inserir o comando para usar a trait.

Finalmente, é necessário importar a trait `ComAvaliacao` **antes** das classes que a utilizam:

```PHP
// index.php
<?php

require __DIR__ . "/src/Modelo/Genero.php";
require __DIR__ . "/src/Modelo/ComAvaliacao.php"; // Aqui está a trait.
require __DIR__ . "/src/Modelo/Avaliavel.php";
require __DIR__ . "/src/Modelo/Titulo.php";
// Resto do código
```
## Namespaces
Namespaces são como diretórios lógicos. A distribuição dos arquivos no sistema de arquivos não precisa ser idêntica à dos namespaces.

Para cada diretório dentro de `src` vamos criar um namespace com o padrão `namespace ScreenMatch\{Nome do diretório}`:
```PHP
// src/Modelo/Titulo.php
<?php
namespace ScreenMatch\Modelo;

abstract class Titulo implements Avaliavel
{
    // Resto do código
}

// src/Calculos/ConversorNotaEstrela.php
<?php
namespace ScreenMatch\Calculos;

class ConversorNotaEstrela 
{
    // Resto do código
}
```

Dentro de `index.php` o uso dos namespaces (e de várias classes do namespace) pode ser feito da seguinte forma:

```PHP
// index.php
<?php
use ScreenMatch\Modelo\{
    Genero, Episodio, Serie, Filme
};

// use ScreenMatch\Calculos\{
//     CalculadoraDeMaratona, ConversorNotaEstrela
// };

use ScreenMatch\Calculos\CalculadoraDeMaratona;
use ScreenMatch\Calculos\ConversorNotaEstrela;

// Resto do código
```
> Note que, para importar várias classes de um mesmo namespace, basta informar o namespace e em seguida, entre chaves, informar as classes que você quer importar desse namespace.
