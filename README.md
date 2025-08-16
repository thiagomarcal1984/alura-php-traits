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
