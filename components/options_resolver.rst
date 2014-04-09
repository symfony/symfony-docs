.. index::
    single: OptionsResolver
    single: Components; OptionsResolver

The OptionsResolver Component
=============================

    The OptionsResolver component helps you configure objects with option
    arrays. It supports default values, option constraints and lazy options.

Installation
------------

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/options-resolver`` on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/OptionsResolver).

Usage
-----

Imagine you have a ``Mailer`` class which has 2 options: ``host`` and
``password``. These options are going to be handled by the OptionsResolver
Component.

First, create the ``Mailer`` class::

    class Mailer
    {
        protected $options;

        public function __construct(array $options = array())
        {
        }
    }

You could of course set the ``$options`` value directly on the property. Instead,
use the :class:`Symfony\\Component\\OptionsResolver\\OptionsResolver` class
and let it resolve the options by calling
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::resolve`.
The advantages of doing this will become more obvious as you continue::

    use Symfony\Component\OptionsResolver\OptionsResolver;

    // ...
    public function __construct(array $options = array())
    {
        $resolver = new OptionsResolver();

        $this->options = $resolver->resolve($options);
    }

The options property now is a well defined array with all resolved options
readily available::

    // ...
    public function sendMail($from, $to)
    {
        $mail = ...;
        $mail->setHost($this->options['host']);
        $mail->setUsername($this->options['username']);
        $mail->setPassword($this->options['password']);
        // ...
    }

Configuring the OptionsResolver
-------------------------------

Now, try to actually use the class::

    $mailer = new Mailer(array(
        'host'     => 'smtp.example.org',
        'username' => 'user',
        'password' => 'pa$$word',
    ));

Right now, you'll receive a
:class:`Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException`,
which tells you that the options ``host`` and ``password`` do not exist.
This is because you need to configure the ``OptionsResolver`` first, so it
knows which options should be resolved.

.. tip::

    To check if an option exists, you can use the
    :method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::isKnown`
    function. You can also `suppress the exception`_ by passing one of the
    flags ``IGNORE_UNKNOWN`` or ``REMOVE_UNKNOWN`` to
    :method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::resolve`.

A best practice is to put the configuration in a method (e.g.
``configureOptions``). You call this method in the constructor to configure
the ``OptionsResolver`` class::

    use Symfony\Component\OptionsResolver\OptionsResolver;
    use Symfony\Component\OptionsResolver\OptionsResolverInterface;

    class Mailer
    {
        protected $options;

        public function __construct(array $options = array())
        {
            $resolver = new OptionsResolver();
            $this->configureOptions($resolver);

            $this->options = $resolver->resolve($options);
        }

        protected function configureOptions(OptionsResolverInterface $resolver)
        {
            // ... configure the resolver, you will learn this
            // in the sections below
        }
    }

Set Default Values
~~~~~~~~~~~~~~~~~~

Most of the options have a default value. You can configure these options by
calling :method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::setDefaults`::

    // ...
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // ...

        $resolver->setDefaults(array(
            'username' => 'root',
        ));
    }

This would add an option - ``username`` - and give it a default value of
``root``. If the user passes in a ``username`` option, that value will
override this default. You don't need to configure ``username`` as an optional
option.

Required Options
~~~~~~~~~~~~~~~~

The ``host`` option is required: the class can't work without it. You can set
the required options by calling
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::setRequired`::

    // ...
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('host'));
    }

You are now able to use the class without errors::

    $mailer = new Mailer(array(
        'host' => 'smtp.example.org',
    ));

    echo $mailer->getHost(); // 'smtp.example.org'

If you don't pass a required option, a
:class:`Symfony\\Component\\OptionsResolver\\Exception\\MissingOptionsException`
will be thrown.

.. tip::

    To determine if an option is required, you can use the
    :method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::isRequired`
    method. You can also `suppress the exception`_ by passing the flag
    ``IGNORE_MISSING`` to
    :method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::resolve`.

Optional Options
~~~~~~~~~~~~~~~~

Sometimes, an option can be optional (e.g. the ``password`` option in the
``Mailer`` class), but it doesn't have a default value. You can configure
these options by calling
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::setOptional`::

    // ...
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // ...

        $resolver->setOptional(array('password'));
    }

Options with defaults are already marked as optional.

.. tip::

    When setting an option as optional, you can't be sure if it's in the array
    or not. You have to check if the option exists before using it.

    To avoid checking if it exists everytime, you can also set a default of
    ``null`` to an option using the ``setDefaults()`` method (see `Set Default Values`_),
    this means the element always exists in the array, but with a default of
    ``null``.

Default Values that Depend on another Option
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Suppose you add a ``port`` option to the ``Mailer`` class, whose default
value you guess based on the encryption. You can do that easily by using a
closure as the default value::

    use Symfony\Component\OptionsResolver\Options;
    use Symfony\Component\OptionsResolver\OptionsResolverInterface;

    // ...
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // ...

        $resolver->setDefaults(array(
            'encryption' => null,
            'port' => function (Options $options) {
                if ('ssl' === $options['encryption']) {
                    return 465;
                }

                return 25;
            },
        ));
    }

The :class:`Symfony\\Component\\OptionsResolver\\Options` class implements
:phpclass:`ArrayAccess`, :phpclass:`Iterator` and :phpclass:`Countable`. That
means you can handle it just like a normal array containing the options.

.. caution::

    The first argument of the closure must be typehinted as ``Options``,
    otherwise it is considered as the value.

Overwriting Default Values
~~~~~~~~~~~~~~~~~~~~~~~~~~

A previously set default value can be overwritten by invoking
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::setDefaults`
again. When using a closure as the new value it is passed 2 arguments:

* ``$options``: an :class:`Symfony\\Component\\OptionsResolver\\Options`
  instance with all the other default options
* ``$previousValue``: the previous set default value

.. code-block:: php

    use Symfony\Component\OptionsResolver\Options;
    use Symfony\Component\OptionsResolver\OptionsResolverInterface;

    // ...
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // ...
        $resolver->setDefaults(array(
            'encryption' => 'ssl',
            'host' => 'localhost',
        ));

        // ...
        $resolver->setDefaults(array(
            'encryption' => 'tls', // simple overwrite
            'host' => function (Options $options, $previousValue) {
                return 'localhost' == $previousValue
                    ? '127.0.0.1'
                    : $previousValue;
            },
        ));
    }

.. tip::

    If the previous default value is calculated by an expensive closure and
    you don't need access to it, you can use the
    :method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::replaceDefaults`
    method instead. It acts like ``setDefaults`` but simply erases the
    previous value to improve performance. This means that the previous
    default value is not available when overwriting with another closure::

        use Symfony\Component\OptionsResolver\Options;
        use Symfony\Component\OptionsResolver\OptionsResolverInterface;

        // ...
        protected function setDefaultOptions(OptionsResolverInterface $resolver)
        {
            // ...
            $resolver->setDefaults(array(
                'encryption' => 'ssl',
                'heavy' => function (Options $options) {
                    // Some heavy calculations to create the $result

                    return $result;
                },
            ));

            $resolver->replaceDefaults(array(
                'encryption' => 'tls', // simple overwrite
                'heavy' => function (Options $options) {
                    // $previousValue not available
                    // ...

                    return $someOtherResult;
                },
            ));
        }

.. note::

    Existing option keys that you do not mention when overwriting are preserved.

Setting Allowed Values
~~~~~~~~~~~~~~~~~~~~~~

Not all values are valid values for options. Suppose the ``Mailer`` class has
a ``transport`` option, which can only be one of ``sendmail``, ``mail`` or
``smtp``. You can configure these allowed values by calling
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::setAllowedValues`::

    // ...
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // ...

        $resolver->setAllowedValues(array(
            'encryption' => array(null, 'ssl', 'tls'),
        ));
    }

There is also an
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::addAllowedValues`
method. Use that method if you want to add another allowed value to the ones
you configured previously.

.. versionadded:: 2.5
    The callback support for allowed values was introduced in Symfony 2.5.

If you need to add some more logic to the value validation process, you can pass a callable
as an allowed value::

    // ...
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // ...

        $resolver->setAllowedValues(array(
            'transport' => function($value) {
                return false !== strpos($value, 'mail');
            },
        ));
    }

.. caution::

    Note that using this together with ``addAllowedValues`` will not work.

Setting Allowed Types
~~~~~~~~~~~~~~~~~~~~~

You can also specify allowed types. For instance, the ``port`` option can carry
any value, as llong as it is an integer. You can configure the allowed type by
calling
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::setAllowedTypes`::

    // ...
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // ...

        $resolver->setAllowedTypes(array(
            'port' => 'integer',
        ));
    }

Possible types are the ones associated with the ``is_*`` PHP functions or a
class name. You can also pass an array of types as the value. For instance,
``array('null', 'string')`` allows ``port`` to be ``null`` or a ``string``.

Like for the allowed values, there is also an
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::addAllowedTypes`
method, which adds an allowed type to the previously configured ones.

Option Normalization
~~~~~~~~~~~~~~~~~~~~

Some values need to be normalized before you can use them. For instance,
pretend that the ``host`` should always start with ``http://``. To do that,
you can write normalizers. These closures will be executed after all options
are passed and should return the normalized value. You can configure these
normalizers by calling
:method:`Symfony\\Components\\OptionsResolver\\OptionsResolver::setNormalizers`::

    // ...
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // ...

        $resolver->setNormalizers(array(
            'host' => function (Options $options, $value) {
                if ('http://' !== substr($value, 0, 7)) {
                    $value = 'http://'.$value;
                }

                return $value;
            },
        ));
    }

You see that the closure also gets an ``$options`` parameter. Sometimes, you
need to use the other options for normalizing::

    // ...
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // ...

        $resolver->setNormalizers(array(
            'host' => function (Options $options, $value) {
                if (!in_array(substr($value, 0, 7), array('http://', 'https://'))) {
                    if ($options['ssl']) {
                        $value = 'https://'.$value;
                    } else {
                        $value = 'http://'.$value;
                    }
                }

                return $value;
            },
        ));
    }

.. _suppress the exception:

Dealing With Errors
~~~~~~~~~~~~~~~~~~~

By default, if you pass a non-existing option to
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::resolve`, an
:class:`Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException`
will be thrown. In some cases, that's not what you want. You can change this
behavior by passing one of the following flags:

* ``FORBID_UNKNOWN`` (the default)
* ``IGNORE_UNKNOWN``
* ``REMOVE_UNKNOWN``

For example, if you pass ``REMOVE_UNKNOWN``, non-existing options will be
removed from the resolved options array instead of throwing an exception::

    $resolver->setDefaults(array(
        'transport' => 'http',
    ));

    $options = $resolver->resolve(array(
        'transport' => 'https',
        'encryption' => 'tls',
    ), OptionsResolverInterface::REMOVE_UNKNOWN);

    print_r($options);
    // => Array(
    //     [transport] => https
    // )

If you pass ``IGNORE_UNKNOWN`` instead, the unknown option will be left in the
final options array unchanged::

    $options = $resolver->resolve(array(
        'transport' => 'https',
        'encryption' => 'tls',
    ), OptionsResolverInterface::IGNORE_UNKNOWN);

    print_r($options);
    // => Array(
    //     [transport] => https
    //     [encryption] => tls
    // )

Likewise, you can configure how to deal with missing required options. By
default, a
:class:`Symfony\\Component\\OptionsResolver\\Exception\\MissingOptionsException`
is thrown. The following flag values are available:

* ``FORBID_MISSING`` (the default)
* ``IGNORE_MISSING``

If you pass ``IGNORE_MISSING``, missing required options will silently be
ignored::

    $resolver->setRequired(array(
        'transport',
        'port',
    ));

    $options = $resolver->resolve(array(
        'port' => 587,
    ), OptionsResolverInterface::IGNORE_MISSING);

    print_r($options);
    // => Array(
    //     [port] => 587
    // )

You can combine multiple flags using the bitwise OR ("|") operator::

    $options = $resolver->resolve(array(
        'port' => 587,
    ), OptionsResolverInterface::REMOVE_UNKNOWN | OptionsResolverInterface::IGNORE_MISSING);

.. _Packagist: https://packagist.org/packages/symfony/options-resolver
