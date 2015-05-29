What do these "XXX is deprecated " E_USER_DEPRECATED Warnings mean?
===================================================================

Starting in Symfony 2.7, if you use a deprecated class, function or option,
Symfony triggers an ``E_USER_DEPRECATED`` error. Internally, that looks something
like this::

    trigger_error(
        'The fooABC method is deprecated since version 2.4 and will be removed in 3.0.',
        E_USER_DEPRECATED
    );

This is great, because you can check your logs to know what needs to change
before you upgrade. In the Symfony Framework, the number of deprecated calls
shows up in the web debug toolbar. And if you install the `phpunit-bridge`_,
you can get a report of deprecated calls after running your tests.

How can I Silence the Warnings?
-------------------------------

As useful as these are, you don't want them to show up while developing and
you may also want to silence them on production to avoid filling up your
error logs.

In the Symfony Framework
~~~~~~~~~~~~~~~~~~~~~~~~

In the Symfony Framework, ``~E_USER_DEPRECATED`` is added to ``app/bootstrap.php.cache``
automatically, but you need at least version 2.3.14 or 3.0.21 of the
`SensioDistributionBundle`_. So, you may need to upgrade:

.. code-block:: bash

    $ composer update sensio/distribution-bundle

Once you've updated, the ``bootstrap.php.cache`` file is rebuilt automatically.
At the top, you should see a line adding ``~E_USER_DEPRECATED``.

Outside of the Symfony Framework
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

To do that, add ``~E_USER_DEPRECATED`` to your ``error_reporting``
setting in ``php.ini``:

.. code-block:: ini

    ; before
    error_reporting = E_ALL
    ; after
    error_reporting = E_ALL & ~E_USER_DEPRECATED

Alternatively, you can set this directly in bootstrap of your project::

    error_reporting(error_reporting() & ~E_USER_DEPRECATED);

How can I Fix the Warnings?
---------------------------

Of course ultimately, you want to stop using the deprecated functionality.
Sometimes, this is easy: the warning might tell you exactly what to change.

But other times, the warning might be unclear: a setting somewhere might
cause a class deeper to trigger the warning. In this case, Symfony does its
best to give a clear message, but you may need to research that warning further.

And sometimes, the warning may come from a third-party library or bundle
that you're using. If that's true, there's a good chance that those deprecations
have already been updated. In that case, upgrade the library to fix them.

Once all the deprecation warnings are gone, you can upgrade with a lot
more confidence.

.. _`phpunit-bridge`: https://github.com/symfony/phpunit-bridge
.. _`SensioDistributionBundle`: https://github.com/sensiolabs/SensioDistributionBundle
