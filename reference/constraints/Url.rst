Url
===

Validates that a value is a valid URL string.

+----------------+---------------------------------------------------------------------+
| Applies to     | :ref:`property or method <validation-property-target>`              |
+----------------+---------------------------------------------------------------------+
| Options        | - `message`_                                                        |
|                | - `protocols`_                                                      |
|                | - `payload`_                                                        |
|                | - `checkDNS`_                                                       |
|                | - `dnsMessage`_                                                     |
+----------------+---------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Url`            |
+----------------+---------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\UrlValidator`   |
+----------------+---------------------------------------------------------------------+

Basic Usage
-----------

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Url()
             */
             protected $bioUrl;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Author:
            properties:
                bioUrl:
                    - Url: ~

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <property name="bioUrl">
                    <constraint name="Url" />
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('bioUrl', new Assert\Url());
            }
        }

Options
-------

message
~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid URL.``

This message is shown if the URL is invalid.

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Url(
             *    message = "The url '{{ value }}' is not a valid url",
             * )
             */
             protected $bioUrl;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Author:
            properties:
                bioUrl:
                    - Url:
                        message: The url "{{ value }}" is not a valid url.

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <property name="bioUrl">
                    <constraint name="Url">
                        <option name="message">The url "{{ value }}" is not a valid url.</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('bioUrl', new Assert\Url(array(
                    'message' => 'The url "{{ value }}" is not a valid url.',
                )));
            }
        }

protocols
~~~~~~~~~

**type**: ``array`` **default**: ``array('http', 'https')``

The protocols considered to be valid for the URL. For example, if you also consider
the ``ftp://`` type URLs to be valid, redefine the ``protocols`` array, listing
``http``, ``https``, and also ``ftp``.

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Url(
             *    protocols = {"http", "https", "ftp"}
             * )
             */
             protected $bioUrl;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Author:
            properties:
                bioUrl:
                    - Url: { protocols: [http, https, ftp] }

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <property name="bioUrl">
                    <constraint name="Url">
                        <option name="protocols">
                            <value>http</value>
                            <value>https</value>
                            <value>ftp</value>
                        </option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('bioUrl', new Assert\Url(array(
                    'protocols' => array('http', 'https', 'ftp'),
                )));
            }
        }

.. include:: /reference/constraints/_payload-option.rst.inc

checkDNS
~~~~~~~~

**type**: ``boolean`` **default**: ``false``

.. versionadded:: 4.1
    This option was deprecated in Symfony 4.1 and will be removed in Symfony 5.0,
    because checking the DNS records is not reliable enough to validate the
    existence of the host. Use the :phpfunction:`checkdnsrr` PHP function if you
    still want to use this kind of validation.

By default, this constraint just validates the syntax of the given URL. If you
also need to check whether the associated host exists, set the ``checkDNS``
option to the value of any of the ``CHECK_DNS_TYPE_*`` constants in the
:class:`Symfony\\Component\\Validator\\Constraints\\Url` class:

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Url(
             *    checkDNS = "ANY"
             * )
             */
             protected $bioUrl;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Author:
            properties:
                bioUrl:
                    - Url: { checkDNS: 'ANY' }

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <property name="bioUrl">
                    <constraint name="Url">
                        <option name="checkDNS">ANY</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('bioUrl', new Assert\Url(array(
                    'checkDNS'  => Assert\Url::CHECK_DNS_TYPE_ANY,
                )));
            }
        }

This option uses the :phpfunction:`checkdnsrr` PHP function to check the validity
of the DNS record corresponding to the host associated with the given URL.

dnsMessage
~~~~~~~~~~

**type**: ``string`` **default**: ``The host could not be resolved.``

.. versionadded:: 4.1
    This option was deprecated in Symfony 4.1 and will be removed in Symfony 5.0,
    because checking the DNS records is not reliable enough to validate the
    existence of the host. Use the :phpfunction:`checkdnsrr` PHP function if you
    still want to use this kind of validation.

This message is shown when the ``checkDNS`` option is set to ``true`` and the
DNS check failed.

.. configuration-block::

    .. code-block:: php-annotations

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Url(
             *    dnsMessage = "The host '{{ value }}' could not be resolved."
             * )
             */
             protected $bioUrl;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Author:
            properties:
                bioUrl:
                    - Url: { dnsMessage: 'The host "{{ value }}" could not be resolved.' }

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <property name="bioUrl">
                    <constraint name="Url">
                        <option name="dnsMessage">The host "{{ value }}" could not be resolved.</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('bioUrl', new Assert\Url(array(
                     'dnsMessage' => 'The host "{{ value }}" could not be resolved.',
                )));
            }
        }
