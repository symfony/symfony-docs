AssertTrue
==========

Valida se o valor é ``true``.

.. code-block:: yaml

    properties:
        termsAccepted:
            - AssertTrue: ~

Opções
------

* ``message``: A mensagem de erro se a validação falhar

Exemplo
-------

Essa restriçção é muito útil para executar uma validação lógica personalizada.
Você pode colocar a lógica em um método que retorna ``true`` ou ``false``.

.. code-block:: php

    // Application/HelloBundle/Author.php
    class Author
    {
        protected $token;

        public function isTokenValid()
        {
            return $this->token == $this->generateToken();
        }
    }

Você pode restringir esse método com ``AssertTrue``.

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
            <getter name="tokenValid">
                <constraint name="True">
                    <option name="message">The token is invalid</option>
                </constraint>
            </getter>
        </class>

    .. code-block:: php

        // Application/HelloBundle/Author.php
        namespace Application\HelloBundle;

        class Author
        {
            protected $token;

            /**
             * @validation:AssertTrue(message = "The token is invalid")
             */
            public function isTokenValid()
            {
                return $this->token == $this->generateToken();
            }
        }

Se a validação desse método falhar, você vai ver uma mensagem semelhante a 
essa:

.. code-block:: text

    Application\HelloBundle\Author.tokenValid:
        This value should not be null
