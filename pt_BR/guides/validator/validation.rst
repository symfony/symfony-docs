Validação de Restrições
=======================

Objetos com restrições são validados pela classe 
:class:`Symfony\\Component\\Validator\\Validator`. Se você usa o Symfony2, essa
classe já esta registrada como um serviço do Container Dependency Injection. 
Para habilitar o serviço, adicione as seguintes linhas na sua configuração:

.. code-block:: yaml

    # hello/config/config.yml
    app.config:
        validation:
            enabled: true

Então você pode pegar o validador do container e começar a validar seus 
objetos::

    $validator = $container->getService('validator');
    $author = new Author();

    print $validator->validate($author);

O método ``validate()`` retorna um objeto
:class:`Symfony\\Component\\Validator\\ConstraintViolationList`. Esse objeto
se comporta exatamente como um array. Você pode percorre-lo e até mesmo 
imprimi-lo formatado de uma maneira bem agradável. Todo elemento da lista 
corresponde a um erro de validação. Se a lista estiver vazia, é hora de dançar,
porque a validação foi bem sucedida.

A chamada assima vai mostrar algo similar a isso:

.. code-block:: text

    Application\HelloBundle\Author.firstName:
        This value should not be blank
    Application\HelloBundle\Author.lastName:
        This value should not be blank
    Application\HelloBundle\Author.fullName:
        This value is too short. It should have 10 characters or more

Se o preencher o objeto com os valores corretoes, os erros de validação desaparecem.
