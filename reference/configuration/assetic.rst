.. index::
   pair: Assetic; Configuration reference

AsseticBundle Configuration Reference
=====================================

Full Default Configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~

.. configuration-block::

    .. code-block:: yaml

        assetic:
            debug:                "%kernel.debug%"
            use_controller:
                enabled:              "%kernel.debug%"
                profiler:             false
            read_from:            "%kernel.root_dir%/../web"
            write_to:             "%assetic.read_from%"
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
            twig:
                functions:
                    # An array of named functions (e.g. some_function, some_other_function)
                    some_function:                 []

    .. code-block:: xml

        <assetic:config
            debug="%kernel.debug%"
            use-controller="%kernel.debug%"
            read-from="%kernel.root_dir%/../web"
            write-to="%assetic.read_from%"
            java="/usr/bin/java"
            node="/usr/bin/node"
            sass="/usr/bin/sass"
        >
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
