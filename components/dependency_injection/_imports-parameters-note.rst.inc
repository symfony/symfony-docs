.. note::

    Due to the way in which parameters are resolved, you cannot use them
    to build paths in imports dynamically. This means that something like
    the following doesn't work:

    .. configuration-block::

        .. code-block:: yaml

            # app/config/config.yml
            imports:
                - { resource: '%kernel.root_dir%/parameters.yml' }

        .. code-block:: xml

            <!-- app/config/config.xml -->
            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://symfony.com/schema/dic/services
                    http://symfony.com/schema/dic/services/services-1.0.xsd">

                <imports>
                    <import resource="%kernel.root_dir%/parameters.yml" />
                </imports>
            </container>

        .. code-block:: php

            // app/config/config.php
            $loader->import('%kernel.root_dir%/parameters.yml');
