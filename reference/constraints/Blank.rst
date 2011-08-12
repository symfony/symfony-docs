Blank
=====

Validates that a value is blank, defined as equal to a blank string or equal
to ``null``. To force that a value strictly be equal to ``null``, see the
:doc:`/reference/constraints/Null` constraint. To force that a value is *not*
blank, see :doc:`/reference/constraints/NotBlank`.

+----------------+-----------------------------------------------------------------------+
| Applies to     | :ref:`property<validation-property-target>`                           |
+----------------+-----------------------------------------------------------------------+
| Options        | - `message`_                                                          |
+----------------+-----------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\Blank`            |
+----------------+-----------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\BlankValidator`   |
+----------------+-----------------------------------------------------------------------+

Basic Usage
-----------

If, for some reason, you wanted to ensure that the ``firstName`` property
of an ``Author`` class were blank, you could do the following:

.. configuration-block::

    .. code-block:: yaml

        properties:
            firstName:
                - Blank: ~

    .. code-block:: php-annotations

        // src/Acme/BlogBundle/Entity/Author.php
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\Blank()
             */
            protected $firstName;
        }

Options
-------

message
~~~~~~~

**type**: ``string`` **default**: ``This value should be blank``

This is the message that will be shown if the value is not blank.
