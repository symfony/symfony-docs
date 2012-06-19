.. index::
   pair: Assetic; Configuration reference

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
