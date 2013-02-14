.. index::
   pair: Assetic; Configuration reference

AsseticBundle Configuration Reference
=====================================

Full Default Configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~

.. configuration-block::

    .. code-block:: yaml

        assetic:
            debug:                true
            use_controller:       true
            read_from:            "%kernel.root_dir%/../web"
            write_to:             "%assetic.read_from%"
            java:                 /usr/bin/java
            node:                 /usr/bin/node
            sass:                 /usr/bin/sass
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

                # Prototype
                name:
                    inputs:               []
                    filters:              []
                    options:

                        # Prototype
                        name:                 []
            filters:

                # Prototype
                name:                 []
            twig:
                functions:

                    # Prototype
                    name:                 []

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
