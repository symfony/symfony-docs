Url
===

Validates that a value is a valid URL string.

+----------------+---------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`               |
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

    .. code-block:: yaml

        # src/BlogBundle/Resources/config/validation.yml
        Acme\BlogBundle\Entity\Author:
            properties:
                bioUrl:
                    - Url:

    .. code-block:: php-annotations

        // src/Acme/BlogBundle/Entity/Author.php
        namespace Acme\BlogBundle\Entity;
        
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Url()
             */
             protected $bioUrl;
        }

    .. code-block:: xml

        <!-- src/Acme/BlogBundle/Resources/config/validation.xml -->
        <class name="Acme\BlogBundle\Entity\Author">
            <property name="bioUrl">
                <constraint name="Url" />
            </property>
        </class>

    .. code-block:: php

        // src/Acme/BlogBundle/Entity/Author.php
        namespace Acme\BlogBundle\Entity;
        
        use Symfomy\Component\Validator\Mapping\ClassMetadata;
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

**type**: ``string`` **default**: ``This value is not a valid URL``

This message is shown if the URL is invalid.

protocols
~~~~~~~~~

**type**: ``array`` **default**: ``array('http', 'https')``

The protocols that will be considered to be valid. For example, if you also
needed ``ftp://`` type URLs to be valid, you'd redefine the ``protocols``
array, listing ``http``, ``https``, and also ``ftp``.
