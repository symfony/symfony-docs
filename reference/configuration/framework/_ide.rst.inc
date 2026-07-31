.. _reference-framework-ide:

ide
~~~

**type**: ``string`` **default**: ``%env(default::SYMFONY_IDE)%``

Symfony turns file paths seen in variable dumps and exception messages into
links that open those files right inside your browser. If you prefer to open
those files in your favorite IDE or text editor, set this option to any of the
following values: ``phpstorm``, ``sublime``, ``textmate``, ``macvim``, ``emacs``,
``atom`` and ``vscode``.

.. note::

    The ``phpstorm`` option is supported natively by PhpStorm on macOS and
    Windows; Linux requires installing `phpstorm-url-handler`_.

If you use another editor, the expected configuration value is a URL template
that contains an ``%f`` placeholder where the file path is expected and ``%l``
placeholder for the line number (percentage signs (``%``) must be escaped by
doubling them to prevent Symfony from interpreting them as container parameters).

.. configuration-block::

    .. code-block:: yaml

        # config/packages/framework.yaml
        framework:
            ide: 'myide://open?url=file://%%f&line=%%l'

    .. code-block:: xml

        <!-- config/packages/framework.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:framework="http://symfony.com/schema/dic/symfony"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/symfony https://symfony.com/schema/dic/symfony/symfony-1.0.xsd">

            <framework:config ide="myide://open?url=file://%%f&line=%%l"/>
        </container>

    .. code-block:: php

        // config/packages/framework.php
        use Symfony\Config\FrameworkConfig;

        return static function (FrameworkConfig $framework): void {
            $framework->ide('myide://open?url=file://%%f&line=%%l');
        };

Since every developer uses a different IDE, the recommended way to enable this
feature is to configure it on a system level. First, you can define this option
in the ``SYMFONY_IDE`` environment variable, which Symfony reads automatically
when ``framework.ide`` config is not set.

Another alternative is to set the ``xdebug.file_link_format`` option in your
``php.ini`` configuration file. The format to use is the same as for the
``framework.ide`` option, but without the need to escape the percent signs
(``%``) by doubling them:

.. code-block:: ini

    // example for PhpStorm
    xdebug.file_link_format="phpstorm://open?file=%f&line=%l"

    // example for PhpStorm with Jetbrains Toolbox
    xdebug.file_link_format="jetbrains://phpstorm/navigate/reference?project=example&path=%f:%l"

    // example for Sublime Text
    xdebug.file_link_format="subl://open?url=file://%f&line=%l"

.. note::

    If both ``framework.ide`` and ``xdebug.file_link_format`` are defined,
    Symfony uses the value of the ``xdebug.file_link_format`` option.

.. tip::

    Setting the ``xdebug.file_link_format`` ini option works even if the Xdebug
    extension is not enabled.

.. tip::

    When running your app in a container or in a virtual machine, you can tell
    Symfony to map files from the guest to the host by changing their prefix.
    This map should be specified at the end of the URL template, using ``&`` and
    ``>`` as guest-to-host separators:

    .. code-block:: text

        // /path/to/guest/.../file will be opened
        // as /path/to/host/.../file on the host
        // and /var/www/app/ as /projects/my_project/ also
        'myide://%%f:%%l&/path/to/guest/>/path/to/host/&/var/www/app/>/projects/my_project/&...'

        // example for PhpStorm
        'phpstorm://open?file=%%f&line=%%l&/var/www/app/>/projects/my_project/'

        // example for JetBrains Toolbox, using a relative path
        // by removing the `/path/to/root/` (note trailing `/`), it becomes relative
        'jetbrains://php-storm/navigate/reference?project=my-project&path=%%f:%%l&/path/to/root/>'

.. _`phpstorm-url-handler`: https://github.com/sanduhrs/phpstorm-url-handler
