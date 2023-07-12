Expression
==========

This constraint allows you to use an :ref:`expression <component-expression-language-examples>`
for more complex, dynamic validation. See `Basic Usage`_ for an example.
See :doc:`/reference/constraints/Callback` for a different constraint that
gives you similar flexibility.

==========  ===================================================================
Applies to  :ref:`class <validation-class-target>`
            or :ref:`property/method <validation-property-target>`
Class       :class:`Symfony\\Component\\Validator\\Constraints\\Expression`
Validator   :class:`Symfony\\Component\\Validator\\Constraints\\ExpressionValidator`
==========  ===================================================================

Basic Usage
-----------

Imagine you have a class ``BlogPost`` with ``category`` and ``isTechnicalPost``
properties::

    // src/Model/BlogPost.php
    namespace App\Model;

    use Symfony\Component\Validator\Constraints as Assert;

    class BlogPost
    {
        private string $category;

        private bool $isTechnicalPost;

        // ...

        public function getCategory()
        {
            return $this->category;
        }

        public function setIsTechnicalPost($isTechnicalPost)
        {
            $this->isTechnicalPost = $isTechnicalPost;
        }

        // ...
    }

To validate the object, you have some special requirements:

A) If ``isTechnicalPost`` is true, then ``category`` must be either ``php``
   or ``symfony``;
B) If ``isTechnicalPost`` is false, then ``category`` can be anything.

One way to accomplish this is with the Expression constraint:

.. configuration-block::

    .. code-block:: php-attributes

        // src/Model/BlogPost.php
        namespace App\Model;

        use Symfony\Component\Validator\Constraints as Assert;

        #[Assert\Expression(
            "this.getCategory() in ['php', 'symfony'] or !this.isTechnicalPost()",
            message: 'If this is a tech post, the category should be either php or symfony!',
        )]
        class BlogPost
        {
            // ...
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Model\BlogPost:
            constraints:
                - Expression:
                    expression: "this.getCategory() in ['php', 'symfony'] or !this.isTechnicalPost()"
                    message: "If this is a tech post, the category should be either php or symfony!"

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">
            <class name="App\Model\BlogPost">
                <constraint name="Expression">
                    <option name="expression">
                        this.getCategory() in ['php', 'symfony'] or !this.isTechnicalPost()
                    </option>
                    <option name="message">
                        If this is a tech post, the category should be either php or symfony!
                    </option>
                </constraint>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Model/BlogPost.php
        namespace App\Model;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class BlogPost
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addConstraint(new Assert\Expression([
                    'expression' => 'this.getCategory() in ["php", "symfony"] or !this.isTechnicalPost()',
                    'message' => 'If this is a tech post, the category should be either php or symfony!',
                ]));
            }

            // ...
        }

The :ref:`expression <reference-constraint-expression-option>` option is the
expression that must return true in order for validation to pass. Learn more
about the :doc:`expression language syntax </reference/formats/expression_language>`.

Alternatively, you can set the ``negate`` option to ``false`` in order to
assert that the expression must return ``true`` for validation to fail.

.. versionadded:: 6.2

   The ``negate`` option was introduced in Symfony 6.2.

.. sidebar:: Mapping the Error to a Specific Field

    You can also attach the constraint to a specific property and still validate
    based on the values of the entire entity. This is handy if you want to attach
    the error to a specific field. In this context, ``value`` represents the value
    of ``isTechnicalPost``.

    .. configuration-block::

        .. code-block:: php-attributes

            // src/Model/BlogPost.php
            namespace App\Model;

            use Symfony\Component\Validator\Constraints as Assert;

            class BlogPost
            {
                // ...

                #[Assert\Expression(
                    "this.getCategory() in ['php', 'symfony'] or value == false",
                    message: 'If this is a tech post, the category should be either php or symfony!',
                )]
                private bool $isTechnicalPost;

                // ...
            }

        .. code-block:: yaml

            # config/validator/validation.yaml
            App\Model\BlogPost:
                properties:
                    isTechnicalPost:
                        - Expression:
                            expression: "this.getCategory() in ['php', 'symfony'] or value == false"
                            message: "If this is a tech post, the category should be either php or symfony!"

        .. code-block:: xml

            <!-- config/validator/validation.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

                <class name="App\Model\BlogPost">
                    <property name="isTechnicalPost">
                        <constraint name="Expression">
                            <option name="expression">
                                this.getCategory() in ['php', 'symfony'] or value == false
                            </option>
                            <option name="message">
                                If this is a tech post, the category should be either php or symfony!
                            </option>
                        </constraint>
                    </property>
                </class>
            </constraint-mapping>

        .. code-block:: php

            // src/Model/BlogPost.php
            namespace App\Model;

            use Symfony\Component\Validator\Constraints as Assert;
            use Symfony\Component\Validator\Mapping\ClassMetadata;

            class BlogPost
            {
                public static function loadValidatorMetadata(ClassMetadata $metadata)
                {
                    $metadata->addPropertyConstraint('isTechnicalPost', new Assert\Expression([
                        'expression' => 'this.getCategory() in ["php", "symfony"] or value == false',
                        'message' => 'If this is a tech post, the category should be either php or symfony!',
                    ]));
                }

                // ...
            }

For more information about the expression and what variables are available
to you, see the :ref:`expression <reference-constraint-expression-option>`
option details below.

.. tip::

    Internally, this expression validator constraint uses a service called
    ``validator.expression_language`` to evaluate the expressions. You can
    decorate or extend that service to fit your own needs.

Options
-------

.. _reference-constraint-expression-option:

``expression``
~~~~~~~~~~~~~~

**type**: ``string`` [:ref:`default option <validation-default-option>`]

The expression that will be evaluated. If the expression evaluates to a false
value (using ``==``, not ``===``), validation will fail. Learn more about the
:doc:`expression language syntax </reference/formats/expression_language>`.

Depending on how you use the constraint, you have access to different variables
in your expression:

* ``this``: The object being validated (e.g. an instance of BlogPost);
* ``value``: The value of the property being validated (only available when
  the constraint is applied directly to a property);

.. include:: /reference/constraints/_groups-option.rst.inc

``message``
~~~~~~~~~~~

**type**: ``string`` **default**: ``This value is not valid.``

The default message supplied when the expression evaluates to false.

You can use the following parameters in this message:

===============  ==============================================================
Parameter        Description
===============  ==============================================================
``{{ value }}``  The current (invalid) value
``{{ label }}``  Corresponding form field label
===============  ==============================================================

``negate``
~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

If ``false``, the validation fails when expression returns ``true``.

.. versionadded:: 6.2

   The ``negate`` option was introduced in Symfony 6.2.

.. include:: /reference/constraints/_payload-option.rst.inc

``values``
~~~~~~~~~~

**type**: ``array`` **default**: ``[]``

The values of the custom variables used in the expression. Values can be of any
type (numeric, boolean, strings, null, etc.)

.. configuration-block::

    .. code-block:: php-attributes

        // src/Model/Analysis.php
        namespace App\Model;

        use Symfony\Component\Validator\Constraints as Assert;

        class Analysis
        {
            #[Assert\Expression(
                'value + error_margin < threshold',
                values: ['error_margin' => 0.25, 'threshold' => 1.5],
            )]
            private float $metric;

            // ...
        }

    .. code-block:: yaml

        # config/validator/validation.yaml
        App\Model\Analysis:
            properties:
                metric:
                    - Expression:
                        expression: "value + error_margin < threshold"
                        values:     { error_margin: 0.25, threshold: 1.5 }

    .. code-block:: xml

        <!-- config/validator/validation.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping https://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Model\Analysis">
                <property name="metric">
                    <constraint name="Expression">
                        <option name="expression">
                            value + error_margin &lt; threshold
                        </option>
                        <option name="values">
                            <value key="error_margin">0.25</value>
                            <value key="threshold">1.5</value>
                        </option>
                    </constraint>
                </property>
            </class>
        </constraint-mapping>

    .. code-block:: php

        // src/Model/Analysis.php
        namespace App\Model;

        use Symfony\Component\Validator\Constraints as Assert;
        use Symfony\Component\Validator\Mapping\ClassMetadata;

        class Analysis
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('metric', new Assert\Expression([
                    'expression' => 'value + error_margin < threshold',
                    'values' => ['error_margin' => 0.25, 'threshold' => 1.5],
                ]));
            }

            // ...
        }
