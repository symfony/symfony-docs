.. index::
   pair: Assetic; Configuration Reference

AsseticBundle Configuration Reference
=====================================

Full Default Configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~

.. configuration-block::

    .. code-block:: yaml

        assetic:
            debug:                %kernel.debug%
            use_controller:
                enabled:              %kernel.debug%
                profiler:             false
            read_from:            %kernel.root_dir%/../web
            write_to:             %assetic.read_from%
            java:                 /usr/bin/java
            node:                 /usr/bin/node
            ruby:                 /usr/bin/ruby
            sass:                 /usr/bin/sass
            variables:

                # Prototype
                name:                 []
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