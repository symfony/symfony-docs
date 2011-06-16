MinLength
=========

Validates that the string length of a value is not smaller than the given limit.

+----------------+----------------------------------------------------------------+
| Validates      | a string                                                       |
+----------------+----------------------------------------------------------------+
| Options        | - ``limit``                                                    |
|                | - ``message``                                                  |
|                | - ``charset``                                                  |
+----------------+----------------------------------------------------------------+
| Default Option | ``limit``                                                      |
+----------------+----------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\MinLength` |
+----------------+----------------------------------------------------------------+

Options
-------

*   ``limit`` (**default**, required) [type: integer]
    This is the minimum length of the string. If set to 3, the string must
    be at least 3 characters in length.

*   ``message`` [type: string, default: ``This value is too short. It should have {{ limit }} characters or more``]
    This is the validation error message when the validation fails.

Basic Usage
-----------

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/HelloBundle/Resources/config/validation.yml
        Acme\HelloBundle\Author:
            properties:
                firstName:
                    - MinLength: 3
    
    .. code-block:: xml

        <!-- src/Acme/HelloBundle/Resources/config/validation.xml -->
        <class name="Acme\HelloBundle\Author">
            <property name="firstName">
                <constraint name="MinLength">
                    <value>3</value>
                </constraint>
            </property>
        </class>

    .. code-block:: php-annotations

        // src/Acme/HelloBundle/Author.php
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\MinLength(3)
             */
            protected $firstName;
        }
