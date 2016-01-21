.. index::
    pair: Assetic; Configuration reference

AsseticBundle Configuration ("assetic")
=======================================

.. include:: /cookbook/assetic/_standard_edition_warning.inc

Full Default Configuration
--------------------------

.. configuration-block::

    .. code-block:: yaml

        assetic:
            debug:                '%kernel.debug%'
            use_controller:
                enabled:              '%kernel.debug%'
                profiler:             false
            read_from:            '%assetic.read_from%'
            write_to:             '%kernel.root_dir%/../web'
            java:                 /usr/bin/java
            node:                 /usr/bin/node
            ruby:                 /usr/bin/ruby
            sass:                 /usr/bin/sass
            # An key-value pair of any number of named elements
            variables:
                some_name:                 []
            bundles:

                # Defaults (all currently registered bundles):
                - FrameworkBundle
                - SecurityBundle
                - TwigBundle
                - MonologBundle
                - SwiftmailerBundle
                - DoctrineBundle
                - AsseticBundle
                - ...
            assets:
                # An array of named assets (e.g. some_asset, some_other_asset)
                some_asset:
                    inputs:               []
                    filters:              []
                    options:
                        # A key-value array of options and values
                        some_option_name: []
            filters:

                # An array of named filters (e.g. some_filter, some_other_filter)
                some_filter:                 []
            workers:
                # see https://github.com/symfony/AsseticBundle/pull/119
                # Cache can also be busted via the framework.templating.assets_version
                # setting - see the "framework" configuration section
                cache_busting:
                    enabled:              false
            twig:
                functions:
                    # An array of named functions (e.g. some_function, some_other_function)
                    some_function:                 []

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8"?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:assetic="http://symfony.com/schema/dic/assetic"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd
                http://symfony.com/schema/dic/assetic
                http://symfony.com/schema/dic/assetic/assetic-1.0.xsd">

            <assetic:config
                debug="%kernel.debug%"
                use-controller="%kernel.debug%"
                read-from="%assetic.read_from%"
                write-to="%kernel.root_dir%/../web"
                java="/usr/bin/java"
                node="/usr/bin/node"
                sass="/usr/bin/sass">

                <!-- Defaults (all currently registered bundles) -->
                <assetic:bundle>FrameworkBundle</assetic:bundle>
                <assetic:bundle>SecurityBundle</assetic:bundle>
                <assetic:bundle>TwigBundle</assetic:bundle>
                <assetic:bundle>MonologBundle</assetic:bundle>
                <assetic:bundle>SwiftmailerBundle</assetic:bundle>
                <assetic:bundle>DoctrineBundle</assetic:bundle>
                <assetic:bundle>AsseticBundle</assetic:bundle>
                <assetic:bundle>...</assetic:bundle>

                <assetic:asset>
                    <!-- prototype -->
                    <assetic:name>
                        <assetic:input />

                        <assetic:filter />

                        <assetic:option>
                            <!-- prototype -->
                            <assetic:name />
                        </assetic:option>
                    </assetic:name>
                </assetic:asset>

                <assetic:filter>
                    <!-- prototype -->
                    <assetic:name />
                </assetic:filter>

                <assetic:twig>
                    <assetic:functions>
                        <!-- prototype -->
                        <assetic:name />
                    </assetic:functions>
                </assetic:twig>
            </assetic:config>
        </container>
