Url
===

Validates that a value is a valid URL string.

+----------------+---------------------------------------------------------------------+
| Applies to     | :ref:`property or method <validation-property-target>`              |
+----------------+---------------------------------------------------------------------+
| Options        | - `message`_                                                        |
|                | - `protocols`_                                                      |
+----------------+---------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Url`            |
+----------------+---------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\UrlValidator`   |
+----------------+---------------------------------------------------------------------+

Basic Usage
-----------

.. configuration-block::

    .. code-block:: php-annotations

        // src/AppBundle/Entity/Author.php
        namespace AppBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Url()
             */
             protected $bioUrl;
        }

    .. code-block:: yaml

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\Author:
            properties:
                bioUrl:
                    - Url: ~

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\Author">
                <property name="bioUrl">
                    <constraint name="Url" />
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/Author.php
        namespace AppBundle\Entity;

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

        // src/AppBundle/Entity/Author.php
        namespace AppBundle\Entity;

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

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\Author:
            properties:
                bioUrl:
                    - Url:
                        message: The url "{{ value }}" is not a valid url.

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\Author">
                <property name="bioUrl">
                    <constraint name="Url">
                        <option name="message">The url "{{ value }}" is not a valid url.</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/AppBundle/Entity/Author.php
        namespace AppBundle\Entity;

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

        // src/AppBundle/Entity/Author.php
        namespace AppBundle\Entity;

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

        # src/AppBundle/Resources/config/validation.yml
        AppBundle\Entity\Author:
            properties:
                bioUrl:
                    - Url: { protocols: [http, https, ftp] }

    .. code-block:: xml

        <!-- src/AppBundle/Resources/config/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="AppBundle\Entity\Author">
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

        // src/AppBundle/Entity/Author.php
        namespace AppBundle\Entity;

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
