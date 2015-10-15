IsTrue
======

Validates that a value is ``true``. Specifically, this checks to see if
the value is exactly ``true``, exactly the integer ``1``, or exactly the
string "``1``".

Also see :doc:`IsFalse <IsFalse>`.

+----------------+---------------------------------------------------------------------+
| Applies to     | :ref:`property or method <validation-property-target>`              |
+----------------+---------------------------------------------------------------------+
| Options        | - `message`_                                                        |
|                | - `payload`_                                                        |
+----------------+---------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\True`           |
+----------------+---------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\TrueValidator`  |
+----------------+---------------------------------------------------------------------+

Basic Usage
-----------

This constraint can be applied to properties (e.g. a ``termsAccepted`` property
on a registration model) or to a "getter" method. It's most powerful in
the latter case, where you can assert that a method returns a true value.
For example, suppose you have the following method::

    // src/AppBundle/Entity/Author.php
    namespace AppBundle\Entity;

    class Author
    {
        protected $token;

        public function isTokenValid()
        {
            return $this->token == $this->generateToken();
        }
    }

Then you can constrain this method with ``IsTrue``.

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Author.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            protected $token;

            /**
             * @Assert\IsTrue(message = "The token is invalid")
             */
            public function isTokenValid()
            {
                return $this->token == $this->generateToken();
            }
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\Author:
            getters:
                tokenValid:
                    - 'IsTrue':
                        message: The token is invalid.

    .. code-block:: xml

        <!-- src/Acme/Blogbundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\Author">
                <getter property="tokenValid">
                    <constraint name="IsTrue">
                        <option name="message">The token is invalid.</option>
                    </constraint>
                </getter>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/Author.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints\IsTrue;

        class Author
        {
            protected $token;

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addGetterConstraint('tokenValid', new IsTrue(array(
                    'message' => 'The token is invalid.',
                )));
            }

            public function isTokenValid()
            {
                return $this->token == $this->generateToken();
            }
        }

If the ``isTokenValid()`` returns false, the validation will fail.

Options
-------

message
~~~~~~~

**type**: ``string`` **default**: ``This value should be true.``

This message is shown if the underlying data is not true.

.. include:: /reference/constraints/_payload-option.rst.inc
