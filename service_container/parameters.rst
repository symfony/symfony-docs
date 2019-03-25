.. index::
   single: DependencyInjection; Parameters

Introduction to Parameters
==========================

You can define parameters in the service container which can then be used
directly or as part of service definitions. This can help to separate out
values that you will want to change more regularly.

Parameters in Configuration Files
---------------------------------

Use the ``parameters`` section of a config file to set parameters:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        parameters:
            mailer.transport: sendmail

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <parameters>
                <parameter key="mailer.transport">sendmail</parameter>
            </parameters>
        </container>

    .. code-block:: php

        // config/services.php
        $container->setParameter('mailer.transport', 'sendmail');

You can refer to parameters elsewhere in any config file by surrounding them
with percent (``%``) signs, e.g. ``%mailer.transport%``. One use for this is
to inject the values into your services. This allows you to configure different
versions of services between applications or multiple services based on the
same class but configured differently within a single application. You could
inject the choice of mail transport into the ``Mailer`` class directly. But
declaring it as a parameter makes it easier to change rather than being tied up
and hidden with the service definition:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        parameters:
            mailer.transport: sendmail

        services:
            App\Service\Mailer:
                arguments: ['%mailer.transport%']

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <parameters>
                <parameter key="mailer.transport">sendmail</parameter>
            </parameters>

            <services>
                <service id="App\Service\Mailer">
                    <argument>%mailer.transport%</argument>
                </service>
            </services>
        </container>

    .. code-block:: php

        // config/services.php
        use App\Mailer;

        $container->setParameter('mailer.transport', 'sendmail');

        $container->register(Mailer::class)
            ->addArgument('%mailer.transport%');

.. caution::

    The values between ``parameter`` tags in XML configuration files are
    not trimmed.

    This means that the following configuration sample will have the value
    ``\n    sendmail\n``:

    .. code-block:: xml

        <parameter key="mailer.transport">
            sendmail
        </parameter>

    In some cases (for constants or class names), this could throw errors.
    In order to prevent this, you must always inline your parameters as
    follow:

    .. code-block:: xml

        <parameter key="mailer.transport">sendmail</parameter>

.. note::

    If you use a string that starts with ``@`` or  has ``%`` anywhere in it, you
    need to escape it by adding another ``@`` or ``%``:

    .. configuration-block::

        .. code-block:: yaml

            # config/services.yaml
            parameters:
                # This will be parsed as string '@securepass'
                mailer_password: '@@securepass'

                # Parsed as http://symfony.com/?foo=%s&amp;bar=%d
                url_pattern: 'http://symfony.com/?foo=%%s&amp;bar=%%d'

        .. code-block:: xml

            <!-- config/services.xml -->
            <parameters>
                <!-- the @ symbol does NOT need to be escaped in XML -->
                <parameter key="mailer_password">@securepass</parameter>

                <!-- But % does need to be escaped -->
                <parameter key="url_pattern">http://symfony.com/?foo=%%s&amp;bar=%%d</parameter>
            </parameters>

        .. code-block:: php

            // config/services.php
            // the @ symbol does NOT need to be escaped in XML
            $container->setParameter('mailer_password', '@securepass');

            // But % does need to be escaped
            $container->setParameter('url_pattern', 'http://symfony.com/?foo=%%s&amp;bar=%%d');

Getting and Setting Container Parameters in PHP
-----------------------------------------------

Working with container parameters is straightforward using the container's
accessor methods for parameters::

    // checks if a parameter is defined (parameter names are case-sensitive)
    $container->hasParameter('mailer.transport');

    // gets value of a parameter
    $container->getParameter('mailer.transport');

    // adds a new parameter
    $container->setParameter('mailer.transport', 'sendmail');

.. caution::

    The used ``.`` notation is a
    :ref:`Symfony convention <service-naming-conventions>` to make parameters
    easier to read. Parameters are flat key-value elements, they can't
    be organized into a nested array

.. note::

    You can only set a parameter before the container is compiled: not at run-time.
    To learn more about compiling the container see
    :doc:`/components/dependency_injection/compilation`.

.. _component-di-parameters-array:

Array Parameters
----------------

Parameters do not need to be flat strings, they can also contain array values.
For the XML format, you need to use the ``type="collection"`` attribute
for all parameters that are arrays.

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        parameters:
            my_mailer.gateways: [mail1, mail2, mail3]

            my_multilang.language_fallback:
                en:
                    - en
                    - fr
                fr:
                    - fr
                    - en

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <parameters>
                <parameter key="my_mailer.gateways" type="collection">
                    <parameter>mail1</parameter>
                    <parameter>mail2</parameter>
                    <parameter>mail3</parameter>
                </parameter>

                <parameter key="my_multilang.language_fallback" type="collection">
                    <parameter key="en" type="collection">
                        <parameter>en</parameter>
                        <parameter>fr</parameter>
                    </parameter>

                    <parameter key="fr" type="collection">
                        <parameter>fr</parameter>
                        <parameter>en</parameter>
                    </parameter>
                </parameter>
            </parameters>
        </container>

    .. code-block:: php

        // config/services.php
        $container->setParameter('my_mailer.gateways', ['mail1', 'mail2', 'mail3']);
        $container->setParameter('my_multilang.language_fallback', [
            'en' => ['en', 'fr'],
            'fr' => ['fr', 'en'],
        ]);

Environment Variables and Dynamic Values
----------------------------------------

See :doc:`/configuration/environment_variables`.

.. _component-di-parameters-constants:

Constants as Parameters
-----------------------

Setting PHP constants as parameters is also supported:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        parameters:
            global.constant.value: !php/const GLOBAL_CONSTANT
            my_class.constant.value: !php/const My_Class::CONSTANT_NAME

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <parameters>
                <parameter key="global.constant.value" type="constant">GLOBAL_CONSTANT</parameter>
                <parameter key="my_class.constant.value" type="constant">My_Class::CONSTANT_NAME</parameter>
            </parameters>
        </container>

    .. code-block:: php

        // config/services.php
        $container->setParameter('global.constant.value', GLOBAL_CONSTANT);
        $container->setParameter('my_class.constant.value', My_Class::CONSTANT_NAME);

Binary Values as Parameters
---------------------------

If the value of a container parameter is a binary value, set it as a base64
encoded value in YAML and XML configs and use the escape sequences in PHP:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        parameters:
            some_parameter: !!binary VGhpcyBpcyBhIEJlbGwgY2hhciAH

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">

            <parameters>
                <parameter key="some_parameter" type="binary">VGhpcyBpcyBhIEJlbGwgY2hhciAH</parameter>
            </parameters>
        </container>

    .. code-block:: php

        // config/services.php
        $container->setParameter('some_parameter', 'This is a Bell char \x07');

PHP Keywords in XML
-------------------

By default, ``true``, ``false`` and ``null`` in XML are converted to the
PHP keywords (respectively ``true``, ``false`` and ``null``):

.. code-block:: xml

    <parameters>
        <parameter key="mailer.send_all_in_once">false</parameter>
    </parameters>

    <!-- after parsing
    $container->getParameter('mailer.send_all_in_once'); // returns false
    -->

To disable this behavior, use the ``string`` type:

.. code-block:: xml

    <parameters>
        <parameter key="mailer.some_parameter" type="string">true</parameter>
    </parameters>

    <!-- after parsing
    $container->getParameter('mailer.some_parameter'); // returns "true"
    -->

.. note::

    This is not available for YAML and PHP, because they already have built-in
    support for the PHP keywords.
