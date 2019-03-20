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

Example::

    use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

    $expressionLanguage = new ExpressionLanguage();
    $expressionLanguage->register('lowercase', function ($str) {
        return sprintf('(is_string(%1$s) ? strtolower(%1$s) : %1$s)', $str);
    }, function ($arguments, $str) {
        if (!is_string($str)) {
            return $str;
        }

        return strtolower($str);
    });

    var_dump($expressionLanguage->evaluate('lowercase("HELLO")'));
    // this will print: hello

In addition to the custom function arguments, the **evaluator** is passed an
``arguments`` variable as its first argument, which is equal to the second
argument of ``compile()`` (e.g. the "values" when evaluating an expression).

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
register::

    use Symfony\Component\ExpressionLanguage\ExpressionFunction;
    use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

    class StringExpressionLanguageProvider implements ExpressionFunctionProviderInterface
    {
        public function getFunctions()
        {
            return [
                new ExpressionFunction('lowercase', function ($str) {
                    return sprintf('(is_string(%1$s) ? strtolower(%1$s) : %1$s)', $str);
                }, function ($arguments, $str) {
                    if (!is_string($str)) {
                        return $str;
                    }

                    return strtolower($str);
                }),
            ];
        }
    }

.. tip::

    To create an expression function from a PHP function with the
    :method:`Symfony\\Component\\ExpressionLanguage\\ExpressionFunction::fromPhp` static method::

        ExpressionFunction::fromPhp('strtoupper');

    Namespaced functions are supported, but they require a second argument to
    define the name of the expression::

        ExpressionFunction::fromPhp('My\strtoupper', 'my_strtoupper');

You can register providers using
:method:`Symfony\\Component\\ExpressionLanguage\\ExpressionLanguage::registerProvider`
or by using the second argument of the constructor::

    use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

    // using the constructor
    $expressionLanguage = new ExpressionLanguage(null, [
        new StringExpressionLanguageProvider(),
        // ...
    ]);

    // using registerProvider()
    $expressionLanguage->registerProvider(new StringExpressionLanguageProvider());

.. tip::

    It is recommended to create your own ``ExpressionLanguage`` class in your
    library. Now you can add the extension by overriding the constructor::

        use Psr\Cache\CacheItemPoolInterface;
        use Symfony\Component\ExpressionLanguage\ExpressionLanguage as BaseExpressionLanguage;

        class ExpressionLanguage extends BaseExpressionLanguage
        {
            public function __construct(CacheItemPoolInterface $parser = null, array $providers = [])
            {
                // prepends the default provider to let users override it
                array_unshift($providers, new StringExpressionLanguageProvider());

                parent::__construct($parser, $providers);
            }
        }

