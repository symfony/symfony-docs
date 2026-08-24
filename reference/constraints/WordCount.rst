WordCount
=========

Validates that a string (or an object implementing the ``Stringable`` PHP interface)
contains a given number of words. Internally, this constraint uses the
:phpclass:`IntlBreakIterator` class to count the words depending on your locale.

==========  =======================================================================
Applies to  :ref:`property or method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\WordCount`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\WordCountValidator`
==========  =======================================================================

Basic Usage
-----------

If you wanted to ensure that the ``content`` property of a ``BlogPostDTO``
class contains between 100 and 200 words, you could do the following:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Entity/BlogPostDTO.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class BlogPostDTO
        {
            #[Assert\WordCount(min: 100, max: 200)]
            protected string $content;
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Entity\BlogPostDTO:
            properties:
                content:
                    - WordCount:
                        min: 100
                        max: 200

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Entity\BlogPostDTO">
                <property name="content">
                    <constraint name="WordCount">
                        <option name="min">100</option>
                        <option name="max">200</option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Entity/BlogPostDTO.php
        namespace App\Entity;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class BlogPostDTO
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata): void
            {
                $metadata->addPropertyConstraint('content', new Assert\WordCount(
                    min: 100,
                    max: 200,
                ));
            }
        }

Options
-------

``min``
~~~~~~~

**type**: ``integer`` **default**: ``null``

The minimum number of words that the value must contain.

``max``
~~~~~~~

**type**: ``integer`` **default**: ``null``

The maximum number of words that the value must contain.

``locale``
~~~~~~~~~~

**type**: ``string`` **default**: ``null``

The locale to use for counting the words by using the :phpclass:`IntlBreakIterator`
class. The default value (``null``) means that the constraint uses the current
user locale.

.. include:: /reference/constraints/_groups-option.rst.inc

``minMessage``
~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is too short. It should contain at least one word.|This value is too short. It should contain at least {{ min }} words.``

This is the message that will be shown if the value does not contain at least
the minimum number of words.

You can use the following parameters in this message:

================  ==================================================
Parameter         Description
================  ==================================================
``{{ min }}``     The minimum number of words
``{{ count }}``   The actual number of words
================  ==================================================

``maxMessage``
~~~~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is too long. It should contain one word.|This value is too long. It should contain {{ max }} words or less.``

This is the message that will be shown if the value contains more than the
maximum number of words.

You can use the following parameters in this message:

================  ==================================================
Parameter         Description
================  ==================================================
``{{ max }}``     The maximum number of words
``{{ count }}``   The actual number of words
================  ==================================================

.. include:: /reference/constraints/_payload-option.rst.inc
