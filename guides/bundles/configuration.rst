.. index::
   single: Bundles; Configuration

Bundle Configuration
====================

To provide more flexibility, a bundle can provide configurable settings by
using the Symfony2 built-in mechanisms.

Simple Configuration
--------------------

For simple configuration settings, rely on the default ``parameters`` entry of
the Symfony2 configuration. Symfony2 parameters are simple key/value pairs; a
value being any valid PHP value. Each parameter name must start with a
lower-cased version of the bundle name (``hello`` for ``HelloBundle``, or
``sensio.social.blog`` for ``Sensio\Social\BlogBundle`` for instance).

The end user can provide values in any configuration file:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        parameters:
            hello.email.from: fabien@example.com

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <parameters>
            <parameter key="hello.email.from">fabien@example.com</parameter>
        </parameters>

    .. code-block:: php

        // app/config/config.php
        $container->setParameter('hello.email.from', 'fabien@example.com');

    .. code-block:: ini

        [parameters]
        hello.email.from = fabien@example.com

Retrieve the configuration parameters in your code from the container::

    $container->getParameter('hello.email.from');

Even if this mechanism is simple enough, you are highly encouraged to use the
semantic configuration described below.
