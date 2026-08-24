.. note::

    Due to the way in which parameters are resolved, you cannot use them
    to build paths in imports dynamically. This means that something like
    **the following does not work:**

    .. configuration-block::

        .. code-block:: yaml

            # config/services.yaml
            imports:
                - { resource: '%kernel.project_dir%/somefile.yaml' }

        .. code-block:: php

            // config/services.php
            namespace Symfony\Component\DependencyInjection\Loader\Configurator;

            return [
                'imports' => [
                    ['resource => '%kernel.project_dir%/somefile.yaml'],
                ],
            ];
