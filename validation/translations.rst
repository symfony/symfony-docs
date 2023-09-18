How to Translate Validation Constraint Messages
===============================================

The validation constraints used in forms can translate their error messages by
creating a translation resource for the ``validators``
:ref:`translation domain <translation-resource-locations>`.

First of all, install the Symfony translation component (if it's not already
installed in your application) running the following command:

.. code-block:: terminal

    $ composer require symfony/translation

Suppose you've created a plain-old-PHP object that you need to use somewhere in
your application::

    // src/Entity/Author.php
    namespace App\Entity;

    class Author
    {
        public string $name;
    }

Add constraints through any of the supported methods. Set the message option
to the translation source text. For example, to guarantee that the ``$name``
property is not empty, add the following:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            #[Assert\NotBlank(message: 'author.name.not_blank')]
            public string $name;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Author:
            properties:
                name:
                    - NotBlank: { message: 'author.name.not_blank' }

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping
                https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

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
        namespace App\Entity;

        // ...
        use Symfony\Component\Validator\Constraints\NotBlank;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Author
        {
            public string $name;

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('name', new NotBlank([
                    'message' => 'author.name.not_blank',
                ]));
            }
        }

Now, create a ``validators`` catalog file in the ``translations/`` directory:

.. configuration-block::

    .. code-block:: xml

        <!-- translations/validators/validators.en.xlf -->
        <?xml version="1.0" encoding="UTF-8" ?>
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

        # translations/validators/validators.en.yaml
        author.name.not_blank: Please enter an author name.

    .. code-block:: php

        // translations/validators/validators.en.php
        return [
            'author.name.not_blank' => 'Please enter an author name.',
        ];

You may need to clear your cache (even in the dev environment) after creating
this file for the first time.

Custom Translation Domain
-------------------------

The default translation domain can be changed globally using the
``FrameworkBundle`` configuration:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/validator.yaml
        framework:
            validation:
                translation_domain: validation_errors

    .. code-block:: xml

        <!-- config/packages/validator.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony
                https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:validation
                    translation-domain="validation_errors"
                />
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/validator.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework) {
            // ...
            $framework
                ->validation()
                    ->translationDomain('validation_errors')
            ;
        };

Or it can be customized for a specific violation from a constraint validator::

    public function validate($value, Constraint $constraint): void
    {
        // validation logic

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ string }}', $value)
            ->setTranslationDomain('validation_errors')
            ->addViolation();
    }
