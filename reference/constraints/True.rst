True
====

Validates that a value is ``true``.

.. code-block:: yaml

    properties:
        termsAccepted:
            - "True": ~

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

Then you can constrain this method with ``True``.

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/HelloBundle/Resources/config/validation.yml
        Acme\HelloBundle\Author:
            getters:
                tokenValid:
                    - "True": { message: "The token is invalid" }

    .. code-block:: xml

        <!-- src/Acme/HelloBundle/Resources/config/validation.xml -->
        <class name="Acme\HelloBundle\Author">
            <getter name="tokenValid">
                <constraint name="True">
                    <option name="message">The token is invalid</option>
                </constraint>
            </getter>
        </class>

    .. code-block:: php-annotations

        // src/Acme/HelloBundle/Author.php
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            protected $token;

            /**
             * @Assert\True(message = "The token is invalid")
             */
            public function isTokenValid()
            {
                return $this->token == $this->generateToken();
            }
        }

    .. code-block:: php

        // src/Acme/HelloBundle/Author.php
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints\True;
        
        class Author
        {
            protected $token;
            
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addGetterConstraint('tokenValid', new True(array(
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
