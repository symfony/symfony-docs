IsTrue
======

Validates that a value is ``true``. Specifically, this checks if the value is
exactly ``true``, exactly the integer ``1``, or exactly the string ``'1'``.

Also see :doc:`IsFalse <IsFalse>`.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Options     - `groups`_
            - `message`_
            - `payload`_
Class       :class:`Symfony\\Component\\Validator\\Constraints\\IsTrue`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\IsTrueValidator`
==========  ===================================================================

Basic Usage
-----------

This constraint can be applied to properties (e.g. a ``termsAccepted`` property
on a registration model) and methods. It's most powerful in the latter case,
where you can assert that a method returns a true value. For example, suppose
you have the following method::

    // src/Entity/Author.php
    namespace App\Entity;

    class Author
    {
        protected $token;

        public function isTokenValid()
        {
            return $this->token == $this->generateToken();
        }
    }

Then you can validate this method with ``IsTrue`` as follows:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            protected $token;

            /**
             * @Assert\IsTrue(message="The token is invalid.")
             */
            public function isTokenValid()
            {
                return $this->token == $this->generateToken();
            }
        }

    .. code-block:: php-attributes

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            protected $token;

            #[Assert\IsTrue(message: 'The token is invalid.')]
            public function isTokenValid()
            {
                return $this->token == $this->generateToken();
            }
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Author:
            getters:
                tokenValid:
                    - 'IsTrue':
                        message: The token is invalid.

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <getter property="tokenValid">
                    <constraint name="IsTrue">
                        <option name="message">The token is invalid.</option>
                    </constraint>
                </getter>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints\IsTrue;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Author
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addGetterConstraint('tokenValid', new IsTrue([
                    'message' => 'The token is invalid.',
                ]));
            }

            public function isTokenValid()
            {
                return $this->token == $this->generateToken();
            }
        }

If the ``isTokenValid()`` returns false, the validation will fail.

.. include:: /reference/constraints/_null-values-are-valid.rst.inc

Options
-------

.. include:: /reference/constraints/_groups-option.rst.inc

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This value should be true.``

This message is shown if the underlying data is not true.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
``{{ label }}``  Corresponding form field label
===============  ==============================================================

.. versionadded:: 5.2

    The ``{{ label }}`` parameter was introduced in Symfony 5.2.

.. include:: /reference/constraints/_payload-option.rst.inc
