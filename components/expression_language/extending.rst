.. index::
    single: Extending; ExpressionLanguage

Extending the ExpressionLanguage
================================

The ExpressionLanguage can be extended by adding custom functions. For
instance, in the framework, the security has custom functions to check the
user's role.

.. note::

    If you want to learn how to use functions in an expression, read
    ":ref:`component-expression-functions`".

Register Functions
------------------

Functions will be registered on the current ``ExpressionLanguage`` instance.
That means the functions can be used in any expression executed by that
instance.

To register a function, use
:method:`Symfony\\Component\\ExpressionLanguage\\ExpressionLanguage::register``.
This method has 3 arguments:

* **name** - The name of the function in an expression;
* **compiler** - A function executed when compiling an expression using the
  function;
* **evaluator** - A function executed when the expression is evaluated.

.. code-block:: php

    use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

    $language = new ExpressionLanguage();
    $language->register('lowercase', function ($str) {
        if (!is_string($str)) {
            return $str;
        }

        return sprintf('strtolower(%s)', $str);
    }, function ($str) {
        if (!is_string($str)) {
            return $str;
        }

        return strtolower($str);
    });

    echo $language->evaluate('lowercase("HELLO")');

This will print ``hello``.

Creating a new ExpressionLanguage class
---------------------------------------

When you use the ``ExpressionLanguage`` class in your library, it's recommend
to create a new ``ExpressionLanguage`` class and register the functions there.
The class will execute ``registerFunctions`` to register the default
functions, you can override this to also add your own functions::

    namespace Acme\AwesomeLib\ExpressionLanguage;

    use Symfony\Component\ExpressionLanguage\ExpressionLanguage as BaseExpressionLanguage;

    class ExpressionLanguage extends BaseExpressionLanguage
    {
        protected function registerFunctions()
        {
            parent::registerFunctions(); // do not forget to also register core functions

            $this->register('lowercase', function ($str) {
                if (!is_string($str)) {
                    return $str;
                }

                return sprintf('strtolower(%s)', $str);
            }, function ($str) {
                if (!is_string($str)) {
                    return $str;
                }

                return strtolower($str);
            });
        }
    }
