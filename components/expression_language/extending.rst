.. index::
    single: Extending; ExpressionLanguage

Extending the ExpressionLanguage
================================

The ExpressionLanguage can be extended by adding custom functions. For
instance, in the Symfony Framework, the security has custom functions to check
the user's role.

.. note::

    If you want to learn how to use functions in an expression, read
    ":ref:`component-expression-functions`".

Registering Functions
---------------------

Functions are registered on each specific ``ExpressionLanguage`` instance.
That means the functions can be used in any expression executed by that
instance.

To register a function, use
:method:`Symfony\\Component\\ExpressionLanguage\\ExpressionLanguage::register`.
This method has 3 arguments:

* **name** - The name of the function in an expression;
* **compiler** - A function executed when compiling an expression using the
  function;
* **evaluator** - A function executed when the expression is evaluated.

.. code-block:: php

    use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

    $language = new ExpressionLanguage();
    $language->register('lowercase', function ($str) {
        return sprintf('(is_string(%1$s) ? strtolower(%1$s) : %1$s)', $str);
    }, function ($arguments, $str) {
        if (!is_string($str)) {
            return $str;
        }

        return strtolower($str);
    });

    var_dump($language->evaluate('lowercase("HELLO")'));

This will print ``hello``. Both the **compiler** and **evaluator** are passed
an ``arguments`` variable as their first argument, which is equal to the
second argument to ``evaluate()`` or ``compile()`` (e.g. the "values" when
evaluating or the "names" if compiling).

.. _components-expression-language-provider:

Using Expression Providers
--------------------------

When you use the ``ExpressionLanguage`` class in your library, you often want
to add custom functions. To do so, you can create a new expression provider by
creating a class that implements
:class:`Symfony\\Component\\ExpressionLanguage\\ExpressionFunctionProviderInterface`.

This interface requires one method: 
:method:`Symfony\\Component\\ExpressionLanguage\\ExpressionFunctionProviderInterface::getFunctions`,
which returns an array of expression functions (instances of
:class:`Symfony\\Component\\ExpressionLanguage\\ExpressionFunction`) to
register.

.. code-block:: php

    use Symfony\Component\ExpressionLanguage\ExpressionFunction;
    use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

    class StringExpressionLanguageProvider implements ExpressionFunctionProviderInterface
    {
        public function getFunctions()
        {
            return array(
                new ExpressionFunction('lowercase', function ($str) {
                    return sprintf('(is_string(%1$s) ? strtolower(%1$s) : %1$s)', $str);
                }, function ($arguments, $str) {
                    if (!is_string($str)) {
                        return $str;
                    }

                    return strtolower($str);
                }),
            );
        }
    }

You can register providers using
:method:`Symfony\\Component\\ExpressionLanguage\\ExpressionLanguage::registerProvider`
or by using the second argument of the constructor::

    use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

    // using the constructor
    $language = new ExpressionLanguage(null, array(
        new StringExpressionLanguageProvider(),
        // ...
    ));

    // using registerProvider()
    $language->registerProvider(new StringExpressionLanguageProvider());

.. tip::

    It is recommended to create your own ``ExpressionLanguage`` class in your
    library. Now you can add the extension by overriding the constructor::

        use Symfony\Component\ExpressionLanguage\ExpressionLanguage as BaseExpressionLanguage;
        use Symfony\Component\ExpressionLanguage\ParserCache\ParserCacheInterface;

        class ExpressionLanguage extends BaseExpressionLanguage
        {
            public function __construct(ParserCacheInterface $parser = null, array $providers = array())
            {
                // prepend the default provider to let users override it easily
                array_unshift($providers, new StringExpressionLanguageProvider());

                parent::__construct($parser, $providers);
            }
        }

