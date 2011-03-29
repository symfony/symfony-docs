AssertTrue
==========

Validates that a value is ``true``.

.. code-block:: yaml

    properties:
        termsAccepted:
            - AssertTrue: ~

Options
-------

* ``message``: The error message if validation fails

Example
-------

This constraint is very useful to execute custom validation logic. You can
put the logic in a method which returns either ``true`` or ``false``.

.. code-block:: php

    // Acme/HelloBundle/Author.php
    class Author
    {
        protected $token;

        public function isTokenValid()
        {
            return $this->token == $this->generateToken();
        }
    }

Then you can constrain this method with ``AssertTrue``.

.. configuration-block::

    .. code-block:: yaml

        # Acme/HelloBundle/Resources/config/validation.yml
        Acme\HelloBundle\Author:
            getters:
                tokenValid:
                    - AssertTrue: { message: "The token is invalid" }

    .. code-block:: xml

        <!-- Acme/HelloBundle/Resources/config/validation.xml -->
        <class name="Acme\HelloBundle\Author">
            <getter name="tokenValid">
                <constraint name="True">
                    <option name="message">The token is invalid</option>
                </constraint>
            </getter>
        </class>

    .. code-block:: php-annotations

        // Acme/HelloBundle/Author.php
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

    .. code-block:: php

        // Acme/HelloBundle/Author.php
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints\AssertTrue;
        
        class Author
        {
            protected $token;
            
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addGetterConstraint('tokenValid', new AssertTrue(array(
                    'message' => 'The token is invalid',
                )));
            }

            public function isTokenValid()
            {
                return $this->token == $this->generateToken();
            }
        }

If the validation of this method fails, you will see a message similar to
this:

.. code-block:: text

    Acme\HelloBundle\Author.tokenValid:
        This value should not be null
