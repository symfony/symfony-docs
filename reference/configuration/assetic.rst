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
            read_from:            %kernel.root_dir%/../web
            write_to:             %assetic.read_from%
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
