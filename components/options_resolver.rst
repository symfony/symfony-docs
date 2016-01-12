.. index::
    single: OptionsResolver
    single: Components; OptionsResolver

The OptionsResolver Component
=============================

    The OptionsResolver component is :phpfunction:`array_replace` on steroids.
    It allows you to create an options system with required options, defaults,
    validation (type, value), normalization and more.

Installation
------------

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/options-resolver`` on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/options-resolver).

.. include:: /components/require_autoload.rst.inc

Notes on Previous Versions
--------------------------

Usage
-----

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
options are buried in the business logic of your code. Use the
:phpfunction:`array_replace` to fix that::

    class Mailer
    {
        // ...

        public function __construct(array $options = array())
        {
            $this->options = array_replace(array(
                'host'     => 'smtp.example.org',
                'username' => 'user',
                'password' => 'pa$$word',
                'port'     => 25,
            ), $options);
        }
    }

Now all four options are guaranteed to be set. But what happens if the user of
the ``Mailer`` class makes a mistake?

.. code-block:: php

    $mailer = new Mailer(array(
        'usernme' => 'johndoe',  // usernAme misspelled 
    ));

No error will be shown. In the best case, the bug will appear during testing,
but the developer will spend time looking for the problem. In the worst case,
the bug might not appear until it's deployed to the live system.

Fortunately, the :class:`Symfony\\Component\\OptionsResolver\\OptionsResolver`
class helps you to fix this problem::

    use Symfony\Component\OptionsResolver\OptionsResolver;

    class Mailer
    {
        // ...

        public function __construct(array $options = array())
        {
            $resolver = new OptionsResolver();
            $resolver->setDefaults(array(
                'host'     => 'smtp.example.org',
                'username' => 'user',
                'password' => 'pa$$word',
                'port'     => 25,
            ));

            $this->options = $resolver->resolve($options);
        }
    }

Like before, all options will be guaranteed to be set. Additionally, an
:class:`Symfony\\Component\\OptionsResolver\\Exception\\UndefinedOptionsException`
is thrown if an unknown option is passed::

    $mailer = new Mailer(array(
        'usernme' => 'johndoe',
    ));

    // UndefinedOptionsException: The option "usernme" does not exist.
    // Known options are: "host", "password", "port", "username"

The rest of your code can access the values of the options without boilerplate
code::

    // ...
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

It's a good practice to split the option configuration into a separate method::

    // ...
    class Mailer
    {
        // ...

        public function __construct(array $options = array())
        {
            $resolver = new OptionsResolver();
            $this->configureOptions($resolver);

            $this->options = $resolver->resolve($options);
        }

        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults(array(
                'host'       => 'smtp.example.org',
                'username'   => 'user',
                'password'   => 'pa$$word',
                'port'       => 25,
                'encryption' => null,
            ));
        }
    }

First, your code becomes easier to read, especially if the constructor does more
than processing options. Second, sub-classes may now override the
``configureOptions()`` method to adjust the configuration of the options::

    // ...
    class GoogleMailer extends Mailer
    {
        public function configureOptions(OptionsResolver $resolver)
        {
            parent::configureOptions($resolver);

            $resolver->setDefaults(array(
                'host' => 'smtp.google.com',
                'encryption' => 'ssl',
            ));
        }
    }

Required Options
~~~~~~~~~~~~~~~~

If an option must be set by the caller, pass that option to
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::setRequired`.
For example, to make the ``host`` option required, you can do::

    // ...
    class Mailer
    {
        // ...

        public function configureOptions(OptionsResolver $resolver)
        {
            // ...
            $resolver->setRequired('host');
        }
    }

If you omit a required option, a
:class:`Symfony\\Component\\OptionsResolver\\Exception\\MissingOptionsException`
will be thrown::

    $mailer = new Mailer();

    // MissingOptionsException: The required option "host" is missing.

The :method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::setRequired`
method accepts a single name or an array of option names if you have more than
one required option::

    // ...
    class Mailer
    {
        // ...

        public function configureOptions(OptionsResolver $resolver)
        {
            // ...
            $resolver->setRequired(array('host', 'username', 'password'));
        }
    }

Use :method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::isRequired` to find
out if an option is required. You can use
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::getRequiredOptions` to
retrieve the names of all required options::

    // ...
    class GoogleMailer extends Mailer
    {
        public function configureOptions(OptionsResolver $resolver)
        {
            parent::configureOptions($resolver);

            if ($resolver->isRequired('host')) {
                // ...
            }

            $requiredOptions = $resolver->getRequiredOptions();
        }
    }

If you want to check whether a required option is still missing from the default
options, you can use :method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::isMissing`.
The difference between this and :method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::isRequired`
is that this method will return false if a required option has already
been set::

    // ...
    class Mailer
    {
        // ...

        public function configureOptions(OptionsResolver $resolver)
        {
            // ...
            $resolver->setRequired('host');
        }
    }

    // ...
    class GoogleMailer extends Mailer
    {
        public function configureOptions(OptionsResolver $resolver)
        {
            parent::configureOptions($resolver);

            $resolver->isRequired('host');
            // => true

            $resolver->isMissing('host');
            // => true

            $resolver->setDefault('host', 'smtp.google.com');

            $resolver->isRequired('host');
            // => true

            $resolver->isMissing('host');
            // => false
        }
    }

The method :method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::getMissingOptions`
lets you access the names of all missing options.

Type Validation
~~~~~~~~~~~~~~~

You can run additional checks on the options to make sure they were passed
correctly. To validate the types of the options, call
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::setAllowedTypes`::

    // ...
    class Mailer
    {
        // ...

        public function configureOptions(OptionsResolver $resolver)
        {
            // ...
            $resolver->setAllowedTypes('host', 'string');
            $resolver->setAllowedTypes('port', array('null', 'int'));
        }
    }

For each option, you can define either just one type or an array of acceptable
types. You can pass any type for which an ``is_<type>()`` function is defined
in PHP. Additionally, you may pass fully qualified class or interface names.

If you pass an invalid option now, an
:class:`Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException`
is thrown::

    $mailer = new Mailer(array(
        'host' => 25,
    ));

    // InvalidOptionsException: The option "host" with value "25" is
    // expected to be of type "string"

In sub-classes, you can use :method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::addAllowedTypes`
to add additional allowed types without erasing the ones already set.

Value Validation
~~~~~~~~~~~~~~~~

Some options can only take one of a fixed list of predefined values. For
example, suppose the ``Mailer`` class has a ``transport`` option which can be
one of ``sendmail``, ``mail`` and ``smtp``. Use the method
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::setAllowedValues`
to verify that the passed option contains one of these values::

    // ...
    class Mailer
    {
        // ...

        public function configureOptions(OptionsResolver $resolver)
        {
            // ...
            $resolver->setDefault('transport', 'sendmail');
            $resolver->setAllowedValues('transport', array('sendmail', 'mail', 'smtp'));
        }
    }

If you pass an invalid transport, an
:class:`Symfony\\Component\\OptionsResolver\\Exception\\InvalidOptionsException`
is thrown::

    $mailer = new Mailer(array(
        'transport' => 'send-mail',
    ));

    // InvalidOptionsException: The option "transport" has the value
    // "send-mail", but is expected to be one of "sendmail", "mail", "smtp"

For options with more complicated validation schemes, pass a closure which
returns ``true`` for acceptable values and ``false`` for invalid values::

    $resolver->setAllowedValues(array(
        // ...
        $resolver->setAllowedValues('transport', function ($value) {
            // return true or false
        });
    ));

In sub-classes, you can use :method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::addAllowedValues`
to add additional allowed values without erasing the ones already set.

Option Normalization
~~~~~~~~~~~~~~~~~~~~

Sometimes, option values need to be normalized before you can use them. For
instance, assume that the ``host`` should always start with ``http://``. To do
that, you can write normalizers. Normalizers are executed after validating an
option. You can configure a normalizer by calling
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::setNormalizer`::

    use Symfony\Component\OptionsResolver\Options;

    // ...
    class Mailer
    {
        // ...

        public function configureOptions(OptionsResolver $resolver)
        {
            // ...

            $resolver->setNormalizer('host', function (Options $options, $value) {
                if ('http://' !== substr($value, 0, 7)) {
                    $value = 'http://'.$value;
                }

                return $value;
            });
        }
    }

The normalizer receives the actual ``$value`` and returns the normalized form.
You see that the closure also takes an ``$options`` parameter. This is useful
if you need to use other options during normalization::

    // ...
    class Mailer
    {
        // ...
        public function configureOptions(OptionsResolver $resolver)
        {
            // ...
            $resolver->setNormalizer('host', function (Options $options, $value) {
                if (!in_array(substr($value, 0, 7), array('http://', 'https://'))) {
                    if ('ssl' === $options['encryption']) {
                        $value = 'https://'.$value;
                    } else {
                        $value = 'http://'.$value;
                    }
                }

                return $value;
            });
        }
    }

Default Values that Depend on another Option
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Suppose you want to set the default value of the ``port`` option based on the
encryption chosen by the user of the ``Mailer`` class. More precisely, you want
to set the port to ``465`` if SSL is used and to ``25`` otherwise.

You can implement this feature by passing a closure as the default value of
the ``port`` option. The closure receives the options as argument. Based on
these options, you can return the desired default value::

    use Symfony\Component\OptionsResolver\Options;

    // ...
    class Mailer
    {
        // ...
        public function configureOptions(OptionsResolver $resolver)
        {
            // ...
            $resolver->setDefault('encryption', null);

            $resolver->setDefault('port', function (Options $options) {
                if ('ssl' === $options['encryption']) {
                    return 465;
                }

                return 25;
            });
        }
    }

.. caution::

    The argument of the callable must be type hinted as ``Options``. Otherwise,
    the callable itself is considered as the default value of the option.

.. note::

    The closure is only executed if the ``port`` option isn't set by the user
    or overwritten in a sub-class.

A previously set default value can be accessed by adding a second argument to
the closure::

    // ...
    class Mailer
    {
        // ...
        public function configureOptions(OptionsResolver $resolver)
        {
            // ...
            $resolver->setDefaults(array(
                'encryption' => null,
                'host' => 'example.org',
            ));
        }
    }

    class GoogleMailer extends Mailer
    {
        public function configureOptions(OptionsResolver $resolver)
        {
            parent::configureOptions($resolver);

            $options->setDefault('host', function (Options $options, $previousValue) {
                if ('ssl' === $options['encryption']) {
                    return 'secure.example.org'
                }

                // Take default value configured in the base class
                return $previousValue;
            });
        }
    }

As seen in the example, this feature is mostly useful if you want to reuse the
default values set in parent classes in sub-classes.

Options without Default Values
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In some cases, it is useful to define an option without setting a default value.
This is useful if you need to know whether or not the user *actually* set
an option or not. For example, if you set the default value for an option,
it's not possible to know whether the user passed this value or if it simply
comes from the default::

    // ...
    class Mailer
    {
        // ...
        public function configureOptions(OptionsResolver $resolver)
        {
            // ...
            $resolver->setDefault('port', 25);
        }

        // ...
        public function sendMail($from, $to)
        {
            // Is this the default value or did the caller of the class really
            // set the port to 25?
            if (25 === $this->options['port']) {
                // ...
            }
        }
    }

You can use :method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::setDefined`
to define an option without setting a default value. Then the option will only
be included in the resolved options if it was actually passed to
:method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::resolve`::

    // ...
    class Mailer
    {
        // ...

        public function configureOptions(OptionsResolver $resolver)
        {
            // ...
            $resolver->setDefined('port');
        }

        // ...
        public function sendMail($from, $to)
        {
            if (array_key_exists('port', $this->options)) {
                echo 'Set!';
            } else {
                echo 'Not Set!';
            }
        }
    }

    $mailer = new Mailer();
    $mailer->sendMail($from, $to);
    // => Not Set!

    $mailer = new Mailer(array(
        'port' => 25,
    ));
    $mailer->sendMail($from, $to);
    // => Set!

You can also pass an array of option names if you want to define multiple
options in one go::

    // ...
    class Mailer
    {
        // ...
        public function configureOptions(OptionsResolver $resolver)
        {
            // ...
            $resolver->setDefined(array('port', 'encryption'));
        }
    }

The methods :method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::isDefined`
and :method:`Symfony\\Component\\OptionsResolver\\OptionsResolver::getDefinedOptions`
let you find out which options are defined::

    // ...
    class GoogleMailer extends Mailer
    {
        // ...

        public function configureOptions(OptionsResolver $resolver)
        {
            parent::configureOptions($resolver);

            if ($resolver->isDefined('host')) {
                // One of the following was called:

                // $resolver->setDefault('host', ...);
                // $resolver->setRequired('host');
                // $resolver->setDefined('host');
            }

            $definedOptions = $resolver->getDefinedOptions();
        }
    }

Performance Tweaks
~~~~~~~~~~~~~~~~~~

With the current implementation, the ``configureOptions()`` method will be
called for every single instance of the ``Mailer`` class. Depending on the
amount of option configuration and the number of created instances, this may add
noticeable overhead to your application. If that overhead becomes a problem, you
can change your code to do the configuration only once per class::

    // ...
    class Mailer
    {
        private static $resolversByClass = array();

        protected $options;

        public function __construct(array $options = array())
        {
            // What type of Mailer is this, a Mailer, a GoogleMailer, ... ?
            $class = get_class($this);

            // Was configureOptions() executed before for this class?
            if (!isset(self::$resolversByClass[$class])) {
                self::$resolversByClass[$class] = new OptionsResolver();
                $this->configureOptions(self::$resolversByClass[$class]);
            }

            $this->options = self::$resolversByClass[$class]->resolve($options);
        }

        public function configureOptions(OptionsResolver $resolver)
        {
            // ...
        }
    }

Now the :class:`Symfony\\Component\\OptionsResolver\\OptionsResolver` instance
will be created once per class and reused from that on. Be aware that this may
lead to memory leaks in long-running applications, if the default options contain
references to objects or object graphs. If that's the case for you, implement a
method ``clearOptionsConfig()`` and call it periodically::

    // ...
    class Mailer
    {
        private static $resolversByClass = array();

        public static function clearOptionsConfig()
        {
            self::$resolversByClass = array();
        }

        // ...
    }

That's it! You now have all the tools and knowledge needed to easily process
options in your code.

.. _Packagist: https://packagist.org/packages/symfony/options-resolver
.. _Form component: http://symfony.com/doc/current/components/form/introduction.html
.. _CHANGELOG: https://github.com/symfony/symfony/blob/master/src/Symfony/Component/OptionsResolver/CHANGELOG.md#260
.. _`read the Symfony 2.5 documentation`: http://symfony.com/doc/2.5/components/options_resolver.html
