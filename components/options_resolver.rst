.. index::
    single: OptionsResolver
    single: Components; OptionsResolver

The OptionsResolver Component
=============================

    The OptionsResolver component helps you to easily process option arrays.

Installation
------------

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/options-resolver`` on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/OptionsResolver).

Usage
-----

.. versionadded:: 2.6
    This documentation was written for Symfony 2.6 and later. If you use an older
    version, please read the corresponding documentation using the version
    drop-down on the upper right.

Imagine you have a ``Mailer`` class which has four options: ``host``,
``username``, ``password`` and ``port``::

    class Mailer
    {
        protected $options;

        public function __construct(array $options = array())
        {
            $this->options = $options;
        }
    }

When accessing the ``$options``, you need to add a lot of boilerplate code to
check which options are set::

    class Mailer
    {
        // ...
        public function sendMail($from, $to)
        {
            $mail = ...;
            $mail->setHost(isset($this->options['host'])
                ? $this->options['host']
                : 'smtp.example.org');
            $mail->setUsername(isset($this->options['username'])
                ? $this->options['username']
                : 'user');
            $mail->setPassword(isset($this->options['password'])
                ? $this->options['password']
                : 'pa$$word');
            $mail->setPort(isset($this->options['port'])
                ? $this->options['port']
                : 25);
            // ...
        }
    }

This boilerplate is hard to read and repetitive. Also, the default values of the
options are buried in the business logic of your code. Let's use
:method:`Symfony\\Component\\OptionsResolver\\Options::resolve` to fix that::

    use Symfony\Component\OptionsResolver\Options;

    class Mailer
    {
        // ...
        public function __construct(array $options = array())
        {
            $this->options = Options::resolve($options, array(
                'host'     => 'smtp.example.org',
                'username' => 'user',
                'password' => 'pa$$word',
                'port'     => 25,
            ));
        }
    }

Now all options are guaranteed to be set. Any option that wasn't passed through
``$options`` will be set to the specified default value. Additionally, an
:class:`Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException`
is thrown if an unknown option is passed::

    $mailer = new Mailer(array(
        'usernme' => 'johndoe',
    ));

    // InvalidOptionsException: The option "usernme" does not exist. Known
    // options are: "host", "password", "username"

The rest of your code can now access the values of the options without
boilerplate code::

    class Mailer
    {
        // ...
        public function sendMail($from, $to)
        {
            $mail = ...;
            $mail->setHost($this->options['host']);
            $mail->setUsername($this->options['username']);
            $mail->setPassword($this->options['password']);
            $mail->setPort($this->options['port']);
            // ...
        }
    }

Required Options
~~~~~~~~~~~~~~~~

If an option must be set by the caller, pass that option to
:method:`Symfony\\Component\\OptionsResolver\\Options::validateRequired`.
For example, let's make the ``host`` option required::

    use Symfony\Component\OptionsResolver\Options;

    class Mailer
    {
        // ...
        public function __construct(array $options = array())
        {
            Options::validateRequired($options, 'host');

            $this->options = Options::resolve($options, array(
                'host'     => null,
                'username' => 'user',
                'password' => 'pa$$word',
                'port'     => 25,
            ));
        }
    }

If you omit a required option, a
:class:`Symfony\\Component\\OptionsResolver\\Exception\\MissingOptionsException`
will be thrown::

    $mailer = new Mailer();

    // MissingOptionsException: The required option "host" is missing.

The :method:`Symfony\\Component\\OptionsResolver\\Options::validateRequired`
method accepts a single name or an array of option names if you have more than
one required option.

.. note::

    As you can see, the ``host`` option must still be passed to
    :method:`Symfony\\Component\\OptionsResolver\\Options::resolve`,
    otherwise the method will not accept that option. The default value,
    however, can be omitted as the option must be set by the caller.

Type Validation
~~~~~~~~~~~~~~~

You can run additional checks on the options to make sure they were passed
correctly. To validate the types of the options, call
:method:`Symfony\\Component\\OptionsResolver\\Options::validateTypes`::

    use Symfony\Component\OptionsResolver\Options;

    class Mailer
    {
        // ...
        public function __construct(array $options = array())
        {
            // ...
            Options::validateTypes($options, array(
                'host' => 'string',
                'port' => array('null', 'int'),
            ));

            $this->options = Options::resolve($options, array(
                'host'     => null,
                'username' => 'user',
                'password' => 'pa$$word',
                'port'     => 25,
            ));
        }
    }

For each option, you can define either just one type or an array of acceptable
types. You can pass any type for which an ``is_<type>()`` method is defined.
Additionally, you may pass fully qualified class or interface names.

If you pass an invalid option now, an :class:`Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException`
is thrown::

    $mailer = new Mailer(array(
        'host' => 25,
    ));

    // InvalidOptionsException: The option "host" with value "25" is expected to
    // be of type "string"

Value Validation
~~~~~~~~~~~~~~~~

Some options can only take one of a fixed list of predefined values. For
example, suppose the ``Mailer`` class has a ``transport`` option which can be
one of ``sendmail``, ``mail`` and ``smtp``. Use the method
:method:`Symfony\\Component\\OptionsResolver\\Options::validateValues` to verify
that the passed option contains one of these values::

    use Symfony\Component\OptionsResolver\Options;

    class Mailer
    {
        // ...
        public function __construct(array $options = array())
        {
            // ...
            Options::validateValues($options, array(
                'transport' => array('sendmail', 'mail', 'smtp'),
            ));

            $this->options = Options::resolve($options, array(
                // ...
                'transport' => 'sendmail',
            ));
        }
    }

If you pass an invalid transport, an :class:`Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException`
is thrown::

    $mailer = new Mailer(array(
        'transport' => 'send-mail',
    ));

    // InvalidOptionsException: The option "transport" has the value "send-mail",
    // but is expected to be one of "sendmail", "mail", "smtp"

For options with more complicated validation schemes, pass a callback which
returns ``true`` for acceptable values and ``false`` for invalid values::

    Options::validateValues($options, array(
        // ...
        'transport' => function ($value) {
            // return true or false
        },
    ));

Default Values that Depend on another Option
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Suppose you want to set the default value of the ``port`` option based on the
encryption chosen by the user of the ``Mailer`` class. More precisely, we want
to set the port to ``465`` if SSL is used and to ``25`` otherwise.

You can implement this feature by passing a closure as default value of the
``port`` option. The closure receives the options as argument. Based on these
options, you can return the desired default value::

    use Symfony\Component\OptionsResolver\Options;

    class Mailer
    {
        // ...
        public function __construct(array $options = array())
        {
            // ...

            $this->options = Options::resolve($options, new Options(array(
                // ...
                'encryption' => null,
                'port' => function (Options $options) {
                    if ('ssl' === $options['encryption']) {
                        return 465;
                    }

                    return 25;
                },
            )));
        }
    }

Instead of a simple array, we now pass the default options as
:class:`Symfony\\Component\\OptionsResolver\\Options` instance to
:method:`Symfony\\Component\\OptionsResolver\\Options::resolve`. This class
makes sure that the closure stored in the default value of the ``port`` option
is called. In the closure, you can use the
:class:`Symfony\\Component\\OptionsResolver\\Options` instance just like a
normal option array.

.. caution::

    The first argument of the closure must be type hinted as ``Options``.
    Otherwise, the closure is considered as the default value of the option.
    If the closure is still not called, double check that you passed the default
    options as :class:`Symfony\\Component\\OptionsResolver\\Options` instance.

.. note::

    The closure is only executed if the ``port`` option isn't set by the user.

Coding Patterns
~~~~~~~~~~~~~~~

If you have a large list of options, the option processing code can take up a
lot of space of your method. To make your code easier to read and maintain, it
is a good practice to put the option definitions into static class properties::

    use Symfony\Component\OptionsResolver\Options;

    class Mailer
    {
        private static $defaultOptions = array(
            'host'       => null,
            'username'   => 'user',
            'password'   => 'pa$$word',
            'port'       => 25,
            'encryption' => null,
        );

        private static $requiredOptions = array(
            'host',
        );

        private static $optionTypes = array(
            'host'     => 'string',
            'username' => 'string',
            'password' => 'string',
            'port'     => 'int',
        );

        private static $optionValues = array(
            'encryption' => array(null, 'ssl', 'tls'),
        );

        protected $options;

        public function __construct(array $options = array())
        {
            Options::validateRequired($options, static::$requiredOptions);
            Options::validateTypes($options, static::$optionTypes);
            Options::validateValues($options, static::$optionValues);

            $this->options = Options::resolve($options, static::$defaultOptions);
        }
    }

In this way, the class remains easy to read and maintain even with a lot of
options being processed and validated.

.. caution::

    PHP does not support closures in property definitions. In such cases, you
    must move your closure to a static method::

        private static $defaultOptions = array(
            // ...
            'port' => array(__CLASS__, 'getDefaultPort'),
        );

        public static function getDefaultPort(Options $options)
        {
            if ('ssl' === $options['encryption']) {
                return 465;
            }

            return 25;
        }

Decoupling the Option Configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

So far, the configuration of the options, their allowed types etc. was very
tightly coupled to the code that resolves the options. This is fine in most cases.
In some cases, however, the configuration of options must be distributed across
multiple classes. An example is a class hierarchy that supports the addition of
options by subclasses. In those cases, you can create an
:class:`Symfony\\Component\\OptionsResolver\\OptionsConfig` object and pass that
object everywhere that you want to adjust the option configuration. Then, call
:method:`Symfony\\Component\\OptionsResolver\\Options::resolve` with the
configuration object to resolve the options.

The following code demonstrates how to write our previous ``Mailer`` class with
an :class:`Symfony\\Component\\OptionsResolver\\OptionsConfig` object::

    use Symfony\Component\OptionsResolver\Options;
    use Symfony\Component\OptionsResolver\OptionsConfig;

    class Mailer
    {
        protected $options;

        public function __construct(array $options = array())
        {
            $config = new OptionsConfig();
            $this->configureOptions($config);

            $this->options = Options::resolve($options, $config);
        }

        protected function configureOptions(OptionsConfig $config)
        {
            $config->setDefaults(array(
                'host'       => null,
                'username'   => 'user',
                'password'   => 'pa$$word',
                'port'       => 25,
                'encryption' => null,
            ));

            $config->setRequired(array(
                'host',
            ));

            $config->setAllowedTypes(array(
                'host'     => 'string',
                'username' => 'string',
                'password' => 'string',
                'port'     => 'int',
            ));

            $config->setAllowedValues(array(
                'encryption' => array(null, 'ssl', 'tls'),
            ));
        }
    }

As you can see, the code is very similar as before. However, the performance
is marginally worse, since the creation of an additional object is required:
the :class:`Symfony\\Component\\OptionsResolver\\OptionsConfig` instance.

Nevertheless, this design also has a benefit: We can extend the ``Mailer``
class and adjust the options of the parent class in the subclass::

    use Symfony\Component\OptionsResolver\Options;
    use Symfony\Component\OptionsResolver\OptionsConfig;

    class GoogleMailer extends Mailer
    {
        protected function configureOptions(OptionsConfig $config)
        {
            $config->setDefaults(array(
                'host'       => 'smtp.google.com',
                'port'       => 25,
                'encryption' => 'ssl',
            ));

            $config->setRequired(array(
                'username',
                'password',
            ));
        }
    }

The ``host`` option is no longer required now, but defaults to "smtp.google.com".
The ``username`` and ``password`` options, however, are required in the
subclass.

The :class:`Symfony\\Component\\OptionsResolver\\OptionsConfig` has various
useful methods to find out which options are set or required. Check out the
API documentation to find out more about these methods.

.. note::

    The :class:`Symfony\\Component\\OptionsResolver\\OptionsResolver` class used
    by the Form Component inherits from
    :class:`Symfony\\Component\\OptionsResolver\\OptionsConfig`. All the
    documentation for ``OptionsConfig`` applies to ``OptionsResolver`` as well.

Optional Options
~~~~~~~~~~~~~~~~

The :class:`Symfony\\Component\\OptionsResolver\\OptionsConfig` has one feature
that is not available when not using this class: You can specify optional
options. Optional options will be accepted and validated when set. When not set,
however, *no default value* will be added to the options array. Pass the names
of the optional options to
:method:`Symfony\\Component\\OptionsResolver\\OptionsConfig::setOptional`::

    use Symfony\Component\OptionsResolver\Options;
    use Symfony\Component\OptionsResolver\OptionsConfig;

    class Mailer
    {
        // ...
        protected function configureOptions(OptionsConfig $config)
        {
            // ...

            $config->setOptional(array('port'));
        }
    }

This is useful if you need to know whether an option was explicitly passed. If
not, it will be missing from the options array::

    class Mailer
    {
        // ...
        public function __construct(array $options = array())
        {
            // ...

            if (array_key_exists('port', $this->options)) {
                echo "Set!";
            } else {
                echo "Not Set!";
            }
        }
    }

    $mailer = new Mailer(array(
        'port' => 25,
    ));
    // Set!

    $mailer = new Mailer();
    // Not Set!

.. tip::

    If you need this functionality when not using an
    :class:`Symfony\\Component\\OptionsResolver\\OptionsConfig` object, check
    the options before calling
    :method:`Symfony\\Component\\OptionsResolver\\Options::resolve`::

        use Symfony\Component\OptionsResolver\Options;

        class Mailer
        {
            // ...
            public function __construct(array $options = array())
            {
                // ...

                if (array_key_exists('port', $options)) {
                    echo "Set!";
                } else {
                    echo "Not Set!";
                }

                $this->options = Options::resolve($options, array(
                    // ...
                ));
            }
        }

Overwriting Default Values
~~~~~~~~~~~~~~~~~~~~~~~~~~

A previously set default value can be overwritten by invoking
:method:`Symfony\\Component\\OptionsResolver\\OptionsConfig::setDefaults`
again. When using a closure as the new value it is passed 2 arguments:

* ``$options``: an :class:`Symfony\\Component\\OptionsResolver\\Options`
  instance with all the other default options
* ``$previousValue``: the previously set default value

.. code-block:: php

    use Symfony\Component\OptionsResolver\Options;
    use Symfony\Component\OptionsResolver\OptionsConfig;

    class Mailer
    {
        // ...
        protected function configureOptions(OptionsConfig $config)
        {
            // ...
            $config->setDefaults(array(
                'encryption' => 'ssl',
                'host' => 'localhost',
            ));

            // ...
            $config->setDefaults(array(
                'encryption' => 'tls', // simple overwrite
                'host' => function (Options $options, $previousValue) {
                    return 'localhost' == $previousValue
                        ? '127.0.0.1'
                        : $previousValue;
                },
            ));
        }
    }

.. tip::

    If the previous default value is calculated by an expensive closure and
    you don't need access to it, use the
    :method:`Symfony\\Component\\OptionsResolver\\OptionsConfig::replaceDefaults`
    method instead. It acts like ``setDefaults`` but erases the previous value
    to improve performance. This means that the previous default value is not
    available when overwriting with another closure::

        use Symfony\Component\OptionsResolver\Options;
        use Symfony\Component\OptionsResolver\OptionsConfig;

        class Mailer
        {
            // ...
            protected function configureOptions(OptionsConfig $config)
            {
                // ...
                $config->setDefaults(array(
                    'encryption' => 'ssl',
                    'heavy' => function (Options $options) {
                        // Some heavy calculations to create the $result

                        return $result;
                    },
                ));

                $config->replaceDefaults(array(
                    'encryption' => 'tls', // simple overwrite
                    'heavy' => function (Options $options) {
                        // $previousValue not available
                        // ...

                        return $someOtherResult;
                    },
                ));
            }
        }

.. note::

    Existing option keys that you do not mention when overwriting are preserved.

Option Normalization
~~~~~~~~~~~~~~~~~~~~

Some values need to be normalized before you can use them. For instance,
assume that the ``host`` should always start with ``http://``. To do that,
you can write normalizers. Normalizers are executed after all options were
processed. You can configure these normalizers by calling
:method:`Symfony\\Components\\OptionsResolver\\OptionsConfig::setNormalizers`::

    use Symfony\Component\OptionsResolver\Options;
    use Symfony\Component\OptionsResolver\OptionsConfig;

    class Mailer
    {
        // ...
        protected function configureOptions(OptionsConfig $config)
        {
            // ...

            $config->setNormalizers(array(
                'host' => function (Options $options, $value) {
                    if ('http://' !== substr($value, 0, 7)) {
                        $value = 'http://'.$value;
                    }

                    return $value;
                },
            ));
        }
    }

The normalizer receives the actual ``$value`` and returns the normalized form.
You see that the closure also takes an ``$options`` parameter. This is useful
if you need to use other options for the normalization::

    use Symfony\Component\OptionsResolver\Options;
    use Symfony\Component\OptionsResolver\OptionsConfig;

    class Mailer
    {
        // ...
        protected function configureOptions(OptionsConfig $config)
        {
            // ...

            $config->setNormalizers(array(
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
    }

.. tip::

    When not using an :class:`Symfony\\Component\\OptionsResolver\\OptionsConfig`
    object, perform normalization after the call to
    :method:`Symfony\\Component\\OptionsResolver\\Options::resolve`::

        use Symfony\Component\OptionsResolver\Options;

        class Mailer
        {
            // ...
            public function __construct(array $options = array())
            {
                $this->options = Options::resolve($options, array(
                    // ...
                ));

                if ('http://' !== substr($this->options['host'], 0, 7)) {
                    $this->options['host'] = 'http://'.$this->options['host'];
                }
            }
        }

That's it! You now have all the tools and knowledge needed to easily process
options in your code.

.. _Packagist: https://packagist.org/packages/symfony/options-resolver
