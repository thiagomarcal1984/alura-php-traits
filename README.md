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
## Autoload
SPL = Standard PHP Library = Biblioteca Padrão do PHP.
A função `spl_autoload_register` coloca um código para autoload na aplicação.

Ela está descrita no novo arquivo a seguir:

```PHP
// autoload.php
<?php
spl_autoload_register(function (string $classe) {
    $caminho = str_replace('ScreenMatch', 'src', $classe) . '.php';
    $caminho = str_replace('\\', DIRECTORY_SEPARATOR, $caminho);

    $caminhoCompleto = __DIR__ . DIRECTORY_SEPARATOR . $caminho;

    if (file_exists($caminhoCompleto)) {
        require_once $caminhoCompleto;
    }
});
```
O algoritmo consiste em:
1. pegar o nome da classe;
2. substituir o primeiro nome do namespace e substituir por `src`;
3. sufixar com `.php`; e
4. fazer o carregamento com `require_once`.

> Há também alguns códigos para substituir as barras e contra-barras pela constante `DIRECTORY_SEPARATOR`. Também há um código para testar se o caminho existe - afinal, o autoload **NUNCA** pode falhar.

É fácil obter esse código via IA ou na internet.

Para usar o autoload, basta inserir o seguinte código na página PHP inicial:
```PHP
// index.php
<?php
require_once 'autoload.php';
// Resto do código
```
# Lidando com falta de notas
## Média sem avaliações / Tratamento de exceções
Exceção x Erro: Exceção permite mudança no fluxo do programa; Erro (em versões anteriores à 7 do PHP) impede a continuação do fluxo do programa.

Vamos criar um arquivo chamado `erro.php` para testar as exceções:

```PHP
// erro.php
<?php

use ScreenMatch\Calculos\ConversorNotaEstrela;
use ScreenMatch\Modelo\Episodio;
use ScreenMatch\Modelo\Genero;
use ScreenMatch\Modelo\Serie;

require 'autoload.php';

$serie = new Serie('Nome da série', 2024, Genero::Acao, 7, 20, 30);
$episodio = new Episodio($serie, 'Piloto', 1);
$episodio->avalia(10);

$conversor = new ConversorNotaEstrela();

echo $conversor->converte($episodio);
```

E vamos mudar o método `converte` que é invocado do objeto `ConversorNotaEstrela`:
```PHP
// src/Calculos/ConversorNotaEstrela.php
<?php
namespace ScreenMatch\Calculos;

class ConversorNotaEstrela 
{
    public static function converte(\ScreenMatch\Modelo\Avaliavel $avaliavel): float
    {
        try {
            $nota = $avaliavel->media();
            return round($nota) / 2;
        } catch (\DivisionByZeroError) { 
            // O PHP não força a declaração da variável $erro.
            return 0.0;
        }
    }
}
```
Note que a função chama objetos que implementam a interface `Avaliavel`. A superclasse `Titulo` usa a trait `ComAvaliacao`, a qual contém a implementação do método `media` abrangido pelo try/catch do método `converte` de `ConversorNotaEstrela`.

A implementação antiga já retornava zero para evitar este erro. Então mudamos a implementação da trait `ComAvaliacao::media` apenas para exemplificar o uso do try/catch:
```PHP
// src/Modelo/ComAvaliacao.php
<?php
namespace ScreenMatch\Modelo;

trait ComAvaliacao
{
    // Resto do código

    public function media(): float
    {
        // if (count($this->notas) === 0) {
        //     return 0.0;
        // }
        return array_sum($this->notas) / count($this->notas);
    }
}
```
## Lidando com múltiplos tipos
Caso o tratamento seja igual para várias exceções/erros, você pode encadear essas exceções/erros com pipe:
```PHP
try {
    $nota = $avaliavel->media();
    return round($nota) / 2;
} catch (\DivisionByZeroError | \ArgumentCountError) { 
    return 0.0;
}
```

Se o tratamento for diferente para cada throwable (erro/exceção), crie blocos separados:

```PHP
try {
    $nota = $avaliavel->media();
    return round($nota) / 2;
} catch (\DivisionByZeroError ) { 
    return 0.0;
} catch (\ArgumentCountError $e) { 
    echo e->getMessage() . '\n';
    return -1.0;
}
```
> Apenas por curiosidade, as classes `Error` e `Exception` implementam a interface `Throwable`. Então, podemos fazer um try/catch completamente amplo capturando qualquer implementação de `Throwable`:
> ```PHP
> <?php
> // src/Calculos/ConversorNotaEstrela.php
> namespace ScreenMatch\Calculos;
> 
> class ConversorNotaEstrela 
> {
>     public static function converte(\ScreenMatch\Modelo\Avaliavel $avaliavel): float
>     {
>         try {
>             $nota = $avaliavel->media();
>             return round($nota) / 2;
>         } catch (\Throwable) { 
>             // O PHP não força a declaração da variável $erro.
>             return 0.0;
>         }
>     }
> }
> ```

# Validando as avaliações
## Avaliação negativa
Vamos mudar a implementação da trait `ComAvaliacao` para limitar as notas que serão permitidas nas avaliações:
```PHP
// src/Modelo/ComAvaliacao.php
<?php
namespace ScreenMatch\Modelo;

trait ComAvaliacao
{
    private array $notas = [];

    /**
     * @throws \InvalidArgumentException Se a nota for negativa ou maior que 10.
     */
    public function avalia(float $nota): void
    {
        if ($nota < 0 || $nota > 10) {
            throw new \InvalidArgumentException('A nota deve estar entre 0 e 10.');
        }   
        $this->notas[] = $nota;
    }

    // Resto do código
}
```
> Note o lançamento da exceção `InvalidArgumentException`.

Vamos testar o try/catch dessa exceção da trait no arquivo `erro.php`:
```PHP
// erro.php
<?php

use ScreenMatch\Calculos\ConversorNotaEstrela;
use ScreenMatch\Modelo\Episodio;
use ScreenMatch\Modelo\Genero;
use ScreenMatch\Modelo\Serie;

require 'autoload.php';

$serie = new Serie('Nome da série', 2024, Genero::Acao, 7, 20, 30);
$episodio = new Episodio($serie, 'Piloto', 1);
try {
    $episodio->avalia(45);
    $episodio->avalia(-35);
    
    $conversor = new ConversorNotaEstrela();
    
    echo $conversor->converte($episodio);
} catch (Exception $e) {
    echo 'Um problema aconteceu: ' . $e->getMessage();
}
```
