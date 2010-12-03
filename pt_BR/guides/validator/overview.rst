O Validator
===========

Validação é uma tarefa muito comum em aplicações web. O conteúdo informado
nos formulários precisa ser validado. Esse conteúdo também precisa ser validado
antes de ser gravado em banco de dados ou encaminhado para um web service;

O Symfony2 vem com um componente embarcado chamado Validator, que faz essa 
tarefa ser muito simples. Esse componente é baseado na 
`JSR303 Bean Validation specification`_. O que?? Uma especificação Java no PHP?
Você ouviu certo, mas não é tão ruim quanto parece. Vamos ver como nós usamos isso
em PHP.

O validator valida os objetos com as :doc:`restrições <constraints>`.
Vamos começar com uma restrição simples que a propriedade ``$name`` da classe
``Author`` não deve ser vazia::

    // Application/HelloBundle/Author.php
    class Author
    {
        private $name;
    }

A próxima lista, mostra a configuração que conecta as propriedades da classe com
as restrições; esse processo é chamado de "mapeamento":

.. configuration-block::

    .. code-block:: yaml

        # Application/HelloBundle/Resources/config/validation.yml
        Application\HelloBundle\Author:
            properties:
                name:
                    - NotBlank: ~

    .. code-block:: xml

        <!-- Application/HelloBundle/Resources/config/validation.xml -->
        <class name="Application\HelloBundle\Author">
            <property name="name">
                <constraint name="NotBlank" />
            </property>
        </class>

    .. code-block:: php

        // Application/HelloBundle/Author.php
        class Author
        {
            /**
             * @validation:NotBlank()
             */
            private $name;
        }

Finalmente, nós pomos usar a classe :class:`Symfony\\Component\\Validator\\Validator`
para :doc:`validar <validation>`. Para usar a validator padrão do Symfony2, adapte 
sua aplicação assim:

.. code-block:: yaml

    # hello/config/config.yml
    app.config:
        validation:
            enabled: true

Agora chame o método ``validate()`` no serviço, que entrega uma lista de erros caso
a validação falhe.

.. code-block:: php

    $validator = $container->get('validator');
    $author = new Author();

    print $validator->validate($author);

Por a propriedade ``$name`` estar vazia, você vai ver a seguinte mensagem de erro:

.. code-block:: text

    Application\HelloBundle\Author.name:
        This value should not be blank

Insira um valor na propriedade e a mensagem de erro vai sumir.

.. _JSR303 Bean Validation specification: http://jcp.org/en/jsr/detail?id=303
