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

    // Application/HelloBundle/Author.php
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
        
If the validation of this method fails, you will see a message similar to this:

::

    Application\HelloBundle\Author.tokenValid:
        This value should not be null
