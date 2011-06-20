MaxLength
=========

Validates that the string length of a value is not larger than the given limit.

+----------------+----------------------------------------------------------------+
| Validates      | a string                                                       |
+----------------+----------------------------------------------------------------+
| Options        | - ``limit``                                                    |
|                | - ``message``                                                  |
|                | - ``charset``                                                  |
+----------------+----------------------------------------------------------------+
| Default Option | ``limit``                                                      |
+----------------+----------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\MaxLength` |
+----------------+----------------------------------------------------------------+

Options
-------

*   ``limit`` (**default**, required) [type: integer]
    This is the maximum length of the string. If set to 10, the string must
    be no more than 10 characters in length.

*   ``message`` [type: string, default: ``This value is too long. It should have {{ limit }} characters or less``]
    This is the validation error message when the validation fails.

Basic Usage
-----------

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/HelloBundle/Resources/config/validation.yml
        Acme\HelloBundle\Blog:
            properties:
                summary:
                    - MaxLength: 100
    
    .. code-block:: xml

        <!-- src/Acme/HelloBundle/Resources/config/validation.xml -->
        <class name="Acme\HelloBundle\Blog">
            <property name="summary">
                <constraint name="MaxLength">
                    <value>100</value>
                </constraint>
            </property>
        </class>

    .. code-block:: php-annotations

        // src/Acme/HelloBundle/Blog.php
        use Symfony\Component\Validator\Constraints as Assert;

        class Blog
        {
            /**
             * @Assert\MaxLength(100)
             */
            protected $summary;
        }
