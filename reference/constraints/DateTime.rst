DateTime
========

Validates that a value is a valid "datetime", meaning either a ``DateTime``
object or a string (or an object that can be cast into a string) that follows
a valid YYYY-MM-DD HH:MM:SS format.

+----------------+------------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                  |
+----------------+------------------------------------------------------------------------+
| Options        | - `message`_                                                           |
+----------------+------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\DateTime`          |
+----------------+------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\DateTimeValidator` |
+----------------+------------------------------------------------------------------------+

Basic Usage
-----------

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/BlogBundle/Resources/config/validation.yml
        Acme\BlogBundle\Entity\Author:
            properties:
                createdAt:
                    - DateTime: ~

    .. code-block:: php-annotations

        // src/Acme/BlogBundle/Entity/Author.php
        namespace Acme\BlogBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\DateTime()
             */
             protected $createdAt;
        }

Options
-------

message
~~~~~~~

**type**: ``string`` **default**: ``This value is not a valid datetime``

This message is shown if the underlying data is not a valid datetime.
