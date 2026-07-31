.. _reference-validation:

validation
~~~~~~~~~~

.. _reference-validation-auto-mapping:

auto_mapping
............

**type**: ``array`` **default**: ``[]``

Defines the Doctrine entities that will be introspected to add
:ref:`automatic validation constraints <automatic_object_validation>` to them:

.. configuration-block::

    .. code-block:: yaml

        framework:
            validation:
                auto_mapping:
                    # an empty array means that all entities that belong to that
                    # namespace will add automatic validation
                    'App\Entity\': []
                    'Foo\': ['Foo\Some\Entity', 'Foo\Another\Entity']

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:validation>
                    <framework:auto-mapping>
                        <framework:service namespace="App\Entity\"/>

                        <framework:service namespace="Foo\">Foo\Some\Entity</framework:service>
                        <framework:service namespace="Foo\">Foo\Another\Entity</framework:service>
                    </framework:auto-mapping>
                </framework:validation>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->validation()
                ->autoMapping()
                    ->paths([
                        'App\\Entity\\' => [],
                        'Foo\\' => ['Foo\\Some\\Entity', 'Foo\\Another\\Entity'],
                    ]);
        };

.. _reference-validation-disable_translation:

disable_translation
...................

**type**: ``boolean`` **default**: ``false``

Validation error messages are automatically translated to the current application
locale. Set this option to ``true`` to disable translation of validation messages.
This is useful to avoid "missing translation" errors in applications that use
only a single language.

.. versionadded:: 7.3

    The ``disable_translation`` option was introduced in Symfony 7.3.

.. _reference-validation-email_validation_mode:

email_validation_mode
.....................

**type**: ``string`` **default**: ``html5``

Sets the default value for the
:ref:`"mode" option of the Email validator <reference-constraint-email-mode>`.

.. _reference-validation-enable_annotations:

enable_attributes
.................

**type**: ``boolean`` **default**: ``true``

If this option is enabled, validation constraints can be defined using `PHP attributes`_.

.. _reference-validation-enabled:

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

Whether or not to enable validation support.

This option will automatically be set to ``true`` when one of the child
settings is configured.

.. _reference-validation-mapping:

mapping
.......

.. _reference-validation-mapping-paths:

paths
"""""

**type**: ``array`` **default**: ``['config/validation/']``

This option allows you to define an array of paths with files or directories where
the component will look for additional validation files:

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            validation:
                mapping:
                    paths:
                        - "%kernel.project_dir%/config/validation/"

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config>
                <framework:validation>
                    <framework:mapping>
                        <framework:path>%kernel.project_dir%/config/validation/</framework:path>
                    </framework:mapping>
                </framework:validation>
            </framework:config>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->validation()
                ->mapping()
                    ->paths(['%kernel.project_dir%/config/validation/']);
        };

.. _reference-validation-not-compromised-password:

not_compromised_password
........................

The :doc:`NotCompromisedPassword </reference/constraints/NotCompromisedPassword>`
constraint makes HTTP requests to a public API to check if the given password
has been compromised in a data breach.

.. _reference-validation-not-compromised-password-enabled:

enabled
"""""""

**type**: ``boolean`` **default**: ``true``

If you set this option to ``false``, no HTTP requests will be made and the given
password will be considered valid. This is useful when you don't want or can't
make HTTP requests, such as in ``dev`` and ``test`` environments or in
continuous integration servers.

endpoint
""""""""

**type**: ``string`` **default**: ``null``

By default, the :doc:`NotCompromisedPassword </reference/constraints/NotCompromisedPassword>`
constraint uses the public API provided by `haveibeenpwned.com`_. This option
allows you to define a different, but compatible, API endpoint to make the password
checks. It's useful for example when the Symfony application is run in an
intranet without public access to the internet.

static_method
.............

**type**: ``string | array`` **default**: ``['loadValidatorMetadata']``

Defines the name of the static method which is called to load the validation
metadata of the class. You can define an array of strings with the names of
several methods. In that case, all of them will be called in that order to load
the metadata.

translation_domain
..................

**type**: ``string | false`` **default**: ``validators``

The translation domain that is used when translating validation constraint
error messages. Use false to disable translations.

.. _`haveibeenpwned.com`: https://haveibeenpwned.com/
.. _`PHP attributes`: https://www.php.net/manual/en/language.attributes.overview.php
