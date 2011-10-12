Collection
==========

Valida registros de um array contra diferentes restrições.

.. code-block:: yaml

    - Collection:
        fields:
            key1:
                - NotNull: ~
            key2:
                - MinLength: 10

Options
-------

* ``fields`` (requirido): Um array associativo com chaves de array e uma ou mais
  restrições
* ``allowMissingFields``: Se alguma das chaves podem não estar presentes no array.
  Padrão: ``false``
* ``allowExtraFields``: Se o array pode conter chaves que não esteja na opção
  ``fields``. Padrão: ``false``
* ``missingFieldsMessage``: A mensagem de erro se a validação ``allowMissingFields``
  falhar
* ``allowExtraFields``: A mensagem de erro se a validação ``allowExtraFields`` 
  falhar

Exemplo:
--------

Vamos validar um array com dois índices ``firstName`` e ``lastName``. O valor de
``firstName`` não pode ser em branco e o valor de ``lastName`` também não pode ser 
branco e deve ter um tamanho mínimo de quatro caracteres. Além disso, ambas as chaves
não podem existir no array.

.. configuration-block::

    .. code-block:: yaml

        # Application/HelloBundle/Resources/config/validation.yml
        Application\HelloBundle\Author:
            properties:
                options:
                    - Collection:
                        fields:
                            firstName:
                                - NotBlank: ~
                            lastName:
                                - NotBlank: ~
                                - MinLength: 4
                        allowMissingFields: true

    .. code-block:: xml

        <!-- Application/HelloBundle/Resources/config/validation.xml -->
        <class name="Application\HelloBundle\Author">
            <property name="options">
                <constraint name="Collection">
                    <option name="fields">
                        <value key="firstName">
                            <constraint name="NotNull" />
                        </value>
                        <value key="lastName">
                            <constraint name="NotNull" />
                            <constraint name="MinLength">4</constraint>
                        </value>
                    </option>
                    <option name="allowMissingFields">true</option>
                </constraint>
            </property>
        </class>

    .. code-block:: php

        // Application/HelloBundle/Author.php
        class Author
        {
            /**
             * @validation:Collection(
             *   fields = {
             *     "firstName" = @validation:NotNull,
             *     "lastName" = { @validation:NotBlank, @validation:MinLength(4) }
             *   },
             *   allowMissingFields = true
             * )
             */
            private $options = array();
        }

O seguinte objeto falharia na validação.

.. code-block:: php

    $author = new Author();
    $author->options['firstName'] = null;
    $author->options['lastName'] = 'foo';

    print $validator->validate($author);

Você deve ver a seguinte mensagem de erro:

.. code-block:: text

    Application\HelloBundle\Author.options[firstName]:
        This value should not be null
    Application\HelloBundle\Author.options[lastName]:
        This value is too short. It should have 4 characters or more
