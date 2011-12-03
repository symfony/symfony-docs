MinLength
=========

Validates that the length of a string is at least as long as the given limit.

+----------------+-------------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`                   |
+----------------+-------------------------------------------------------------------------+
| Options        | - `limit`_                                                              |
|                | - `message`_                                                            |
|                | - `charset`_                                                            |
+----------------+-------------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\MinLength`          |
+----------------+-------------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\MinLengthValidator` |
+----------------+-------------------------------------------------------------------------+

Basic Usage
-----------

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/BlogBundle/Resources/config/validation.yml
        Acme\BlogBundle\Entity\Blog:
            properties:
                firstName:
                    - MinLength: { limit: 3, message: "Your name must have at least {{ limit }} characters." }

    .. code-block:: php-annotations

        // src/Acme/BlogBundle/Entity/Blog.php
        use Symfony\Component\Validator\Constraints as Assert;

        class Blog
        {
            /**
             * @Assert\MinLength(
             *     limit=3,
             *     message="Your name must have at least {{ limit }} characters."
             * )
             */
            protected $summary;
        }

    .. code-block:: xml

        <!-- src/Acme/BlogBundle/Resources/config/validation.xml -->
        <class name="Acme\BlogBundle\Entity\Blog">
            <property name="summary">
                <constraint name="MinLength">
                    <option name="limit">3</option>
                    <option name="message">Your name must have at least {{ limit }} characters.</option>
                </constraint>
            </property>
        </class>

Options
-------

limit
~~~~~

**type**: ``integer`` [:ref:`default option<validation-default-option>`]

This required option is the "min" value. Validation will fail if the length
of the give string is **less** than this number.

message
~~~~~~~

**type**: ``string`` **default**: ``This value is too short. It should have {{ limit }} characters or more``

The message that will be shown if the underlying string has a length that
is shorter than the `limit`_ option.

charset
~~~~~~~

**type**: ``charset`` **default**: ``UTF-8``

If the PHP extension "mbstring" is installed, then the PHP function `mb_strlen`_
will be used to calculate the length of the string. The value of the ``charset``
option is passed as the second argument to that function.

.. _`mb_strlen`: http://php.net/manual/en/function.mb-strlen.php
