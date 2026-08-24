Url
===

Validates that a value is a valid URL string.

==========  ===================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Url`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\UrlValidator`
==========  ===================================================================

Basic Usage
-----------

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            #[Assert\Url]
            protected string $bioUrl;
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
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <property name="bioUrl">
                    <constraint name="Url"/>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Author
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('bioUrl', new Assert\Url());
            }
        }

This constraint doesn't check that the host of the given URL really exists,
because the information of the DNS records is not reliable. Use the
:phpfunction:`checkdnsrr` PHP function if you still want to check that.

.. include:: /reference/constraints/_empty-values-are-valid.rst.inc

Options
-------

.. include:: /reference/constraints/_groups-option.rst.inc

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid URL.``

This message is shown if the URL is invalid.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
``{{ label }}``  Corresponding form field label
===============  ==============================================================

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            #[Assert\Url(
                message: 'The url {{ value }} is not a valid url',
            )]
            protected string $bioUrl;
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
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

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

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Author
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('bioUrl', new Assert\Url(
                    message: 'The url "{{ value }}" is not a valid url.',
                ));
            }
        }

.. include:: /reference/constraints/_normalizer-option.rst.inc

.. include:: /reference/constraints/_payload-option.rst.inc

``protocols``
~~~~~~~~~~~~~

**type**: ``array|string`` **default**: ``['http', 'https']``

The protocols considered to be valid for the URL. For example, if you also consider
the ``ftp://`` type URLs to be valid, redefine the ``protocols`` array, listing
``http``, ``https``, and also ``ftp``.

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            #[Assert\Url(
                protocols: ['http', 'https', 'ftp'],
            )]
            protected string $bioUrl;
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
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

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

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Author
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('bioUrl', new Assert\Url(
                    protocols: ['http', 'https', 'ftp'],
                ));
            }
        }

The value of this option can also be an asterisk (``*``) to allow all protocols::

    // allows all protocols whose names are RFC 3986 compliant
    // (e.g. 'https://', 'git+ssh://', 'file://', 'custom://')
    protocols: '*'

``relativeProtocol``
~~~~~~~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``false``

If ``true``, the protocol is considered optional when validating the syntax of
the given URL. This means that both ``http://`` and ``https://`` are valid but
also relative URLs that contain no protocol (e.g. ``//example.com``).

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            #[Assert\Url(
                relativeProtocol: true,
            )]
            protected string $bioUrl;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Author:
            properties:
                bioUrl:
                    - Url: { relativeProtocol: true }

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Author">
                <property name="bioUrl">
                    <constraint name="Url">
                        <option name="relativeProtocol">true</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Author.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Author
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('bioUrl', new Assert\Url(
                    relativeProtocol: true,
                ));
            }
        }

``requireTld``
~~~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

By default, URLs like ``https://aaa`` or ``https://foobar`` are considered valid
because they are technically correct according to the `URL spec`_. If you set this option
to ``true``, the host part of the URL will have to include a TLD (top-level domain
name): e.g. ``https://example.com`` will be valid but ``https://example`` won't.

.. note::

    This constraint does not validate that the given TLD value is included in
    the `list of official top-level domains`_ (because that list is growing
    continuously and it's hard to keep track of it).

``tldMessage``
~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This URL does not contain a TLD.``

This message is shown if the ``requireTld`` option is set to ``true`` and the URL
does not contain at least one TLD.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
``{{ label }}``  Corresponding form field label
===============  ==============================================================

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/Website.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Website
        {
            #[Assert\Url(
                requireTld: true,
                tldMessage: 'Add at least one TLD to the {{ value }} URL.',
            )]
            protected string $homepageUrl;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\Website:
            properties:
                homepageUrl:
                    - Url:
                        requireTld: true
                        tldMessage: Add at least one TLD to the {{ value }} URL.

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\Website">
                <property name="homepageUrl">
                    <constraint name="Url">
                        <option name="requireTld">true</option>
                        <option name="tldMessage">Add at least one TLD to the {{ value }} URL.</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/Website.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Website
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('homepageUrl', new Assert\Url(
                    requireTld: true,
                    tldMessage: 'Add at least one TLD to the {{ value }} URL.',
                ));
            }
        }

.. _`URL spec`: https://datatracker.ietf.org/doc/html/rfc1738
.. _`list of official top-level domains`: https://en.wikipedia.org/wiki/List_of_Internet_top-level_domains
