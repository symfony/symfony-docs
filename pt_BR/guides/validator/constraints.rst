Restrições
==========

O Validator é projetado para validar objetos com as *restrições*.
Na vida real, uma restrição pode ser: "O bolo não deve estar queimado". No 
Symfony2, restrições são similares: Elas são afirmações de que a condição
é verdadeira.

Restrições Suportadas
---------------------

As seguintes restrições são nativamente suportadas pelo Symfony2:

.. toctree::
    :hidden:

    constraints/index

* :doc:`AssertFalse <constraints/AssertFalse>`
* :doc:`AssertTrue <constraints/AssertTrue>`
* :doc:`AssertType <constraints/AssertType>`
* :doc:`Choice <constraints/Choice>`
* :doc:`Collection <constraints/Collection>`
* :doc:`Date <constraints/Date>`
* :doc:`DateTime <constraints/DateTime>`
* :doc:`Email <constraints/Email>`
* :doc:`File <constraints/File>`
* :doc:`Max <constraints/Max>`
* :doc:`MaxLength <constraints/MaxLength>`
* :doc:`Min <constraints/Min>`
* :doc:`MinLength <constraints/MinLength>`
* :doc:`NotBlank <constraints/NotBlank>`
* :doc:`NotNull <constraints/NotNull>`
* :doc:`Regex <constraints/Regex>`
* :doc:`Time <constraints/Time>`
* :doc:`Url <constraints/Url>`
* :doc:`Valid <constraints/Valid>`

Alvos das Restrições
--------------------

Restrições podem ser colocadas em propriedades de uma classe, em getter publicos
e na própria classe. Os beneficios de restrições em classes é que elas podem
valida o estado inteiro de um objeto de uma só vez, com todas as suas propriedades
 e métodos.

Propriedades
~~~~~~~~~~~~

A validação de propriedades de uma classe é a mais básica das técnicas de validação.
O Symfony2 permite que você valide propriedades privadas, protegidas ou públicas. A
lista a seguir mostra como configurar as propriedades ``$firstName`` e ``$lastName``
da classe ``Author`` para ter no mínimo 3 caracteres.

.. configuration-block::

    .. code-block:: yaml

        # Application/HelloBundle/Resources/config/validation.yml
        Application\HelloBundle\Author:
            properties:
                firstName:
                    - NotBlank: ~
                    - MinLength: 3
                lastName:
                    - NotBlank: ~
                    - MinLength: 3

    .. code-block:: xml

        <!-- Application/HelloBundle/Resources/config/validation.xml -->
        <class name="Application\HelloBundle\Author">
            <property name="firstName">
                <constraint name="NotBlank" />
                <constraint name="MinLength">3</constraint>
            </property>
            <property name="lastName">
                <constraint name="NotBlank" />
                <constraint name="MinLength">3</constraint>
            </property>
        </class>

    .. code-block:: php

        // Application/HelloBundle/Author.php
        class Author
        {
            /**
             * @validation:NotBlank()
             * @validation:MinLength(3)
             */
            private $firstName;

            /**
             * @validation:NotBlank()
             * @validation:MinLength(3)
             */
            private $firstName;
        }

Getters
~~~~~~~

A próxima técnica de validação é restringir o valor do retorno de um método.
O Symfony2 permite que você restrinja qualquer método público em que o nome
comece com "get" ou "is". Nesse guia, ele é referenciado como "getter".

O beneficio dessa técnica é que ela permite você validar seu objeto dinamicamente.
Dependendo do estato do seu objeto, o método pode retornar diferentes valores que
então são validados.

A lista a seguir mostra como usar a restrição :doc:`AssertTrue
<constraints/AssertTrue>` para validar se um token gerado dinamicamente está 
correto:

.. configuration-block::

    .. code-block:: yaml

        # Application/HelloBundle/Resources/config/validation.yml
        Application\HelloBundle\Author:
            getters:
                tokenValid:
                    - AssertTrue: { message: "The token is invalid" }

    .. code-block:: xml

        <!-- Application/HelloBundle/Resources/config/validation.xml -->
        <class name="Application\HelloBundle\Author">
            <getter property="tokenValid">
                <constraint name="AssertTrue">
                    <option name="message">The token is invalid</option>
                </constraint>
            </getter>
        </class>

    .. code-block:: php

        // Application/HelloBundle/Author.php
        class Author
        {
            /**
             * @validation:AssertTrue(message = "The token is invalid")
             */
            public function isTokenValid()
            {
                // return true or false
            }
        }

.. note::

    Se você tem uma visão aguçada, deve ter percebido que o prefixo do getter
    ("get" ou "is") foi omitido do mapeamento. Isso permite que você mova a 
    restrição para uma propriedade com o mesmo nome depois (ou vice-versa)
    sem mudar a lógica de validação.

Restrições Customizadas
-----------------------

Você pode criar restrições customizadas estendendo a classe base de restrições
:class:`Symfony\\Component\\Validator\\Constraint`. As opções das suas restrições
são representadas como propriedades públicas na classe de restrição. Por exemplo,
a restrição ``Url`` inclui as propriedades ``message`` e ``protocols``::

    namespace Symfony\Component\Validator\Constraints;

    class Url extends \Symfony\Component\Validator\Constraint
    {
        public $message = 'This value is not a valid URL';
        public $protocols = array('http', 'https', 'ftp', 'ftps');
    }

Como você pode ver, uma classe de restrição é bem minima. A validação mesmo é
feita por outra classe "validadora de restrições". Que validador de restrição
é especificado pelo método ``validatedBy()``, que inclui alguma lógica simples 
por padrão::

    // in the base Symfony\Component\Validator\Constraint class
    public function validatedBy()
    {
        return get_class($this).'Validator';
    }

Validadores de Restrição com Dependencias
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Se o se validador de restrição tiver dependencias, como conexão com o banco,
ele precisará ser configurado como um serviço no container de dependency injection.
Esse serviço deve incluir a tag ``validator.constraint_validator`` e um atributo
``alias``:

.. configuration-block::

    .. code-block:: yaml

        services:
            validator.unique.your_validator_name:
                class: Fully\Qualified\Validator\Class\Name
                tags:
                    - { name: validator.constraint_validator, alias: alias_name }

    .. code-block:: xml

        <service id="validator.unique.your_validator_name" class="Fully\Qualified\Validator\Class\Name">
            <argument type="service" id="doctrine.orm.default_entity_manager" />
            <tag name="validator.constraint_validator" alias="alias_name" />
        </service>

    .. code-block:: php

        $container
            ->register('validator.unique.your_validator_name', 'Fully\Qualified\Validator\Class\Name')
            ->addTag('validator.constraint_validator', array('alias' => 'alias_name'))
        ;

Suas classes de restrição podem agora usar alias para referenciar o validador apropriado::

    public function validatedBy()
    {
        return 'alias_name';
    }
