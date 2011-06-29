.. index::
   single: Configuration Reference; Swiftmailer

SwiftmailerBundle Configuration
===============================

Full Default Configuration
--------------------------

.. configuration-block::

    .. code-block:: yaml

        swiftmailer:
            transport:            smtp
            username:             ~
            password:             ~
            host:                 localhost
            port:                 false
            encryption:           ~
            auth_mode:            ~
            spool:
                type:                 file
                path:                 %kernel.cache_dir%/swiftmailer/spool
            sender_address:       ~
            antiflood:
                threshold:            99
                sleep:                0
            delivery_address:     ~
            disable_delivery:     ~
            logging:              true