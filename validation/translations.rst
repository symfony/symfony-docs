.. index::
    single: Validation; Translation

How to Translate Validation Constraint Messages
===============================================

If you're using validation constraints with the Form component, then translating
the error messages is easy: simply create a translation resource for the
``validators`` :ref:`domain <using-message-domains>`.

To start, suppose you've created a plain-old-PHP object that you need to
use somewhere in your application::

    // src/Entity/Author.php
    namespace App\Entity;

    class Author
    {
        public $name;
    }

Add constraints through any of the supported methods. Set the message option
to the translation source text. For example, to guarantee that the ``$name``
property is not empty, add the following:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Author.php
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\NotBlank(message = "author.name.not_blank")
             */
            public $name;
        }

    .. code-block:: yaml

        # src/Resources/config/validation.yml
        App\Entity\Author:
            properties:
                name:
                    - NotBlank: { message: 'author.name.not_blank' }

    .. code-block:: xml

        <!-- src/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping
                http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <property name="name">
                    <constraint name="NotBlank">
                        <option name="message">author.name.not_blank</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Author.php

        // ...
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints\NotBlank;

        class Author
        {
            public $name;

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('name', new NotBlank(array(
                    'message' => 'author.name.not_blank',
                )));
            }
        }

Now, create a ``validators`` catalog file in the ``app/Resources/translations`` directory:

.. configuration-block::

    .. code-block:: xml

        <!-- app/Resources/translations/validators.en.xlf -->
        <?xml version="1.0"?>
        <xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
            <file source-language="en" datatype="plaintext" original="file.ext">
                <body>
                    <trans-unit id="author.name.not_blank">
                        <source>author.name.not_blank</source>
                        <target>Please enter an author name.</target>
                    </trans-unit>
                </body>
            </file>
        </xliff>

    .. code-block:: yaml

        # app/Resources/translations/validators.en.yml
        author.name.not_blank: Please enter an author name.

    .. code-block:: php

        // app/Resources/translations/validators.en.php
        return array(
            'author.name.not_blank' => 'Please enter an author name.',
        );

You may need to clear your cache (even in the dev environment) after creating this
file for the first time.
