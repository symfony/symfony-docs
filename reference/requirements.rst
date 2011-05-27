.. index::
   single: Requirements
   
Requirements for running Symfony2
=================================

To run Symfony2, your system needs to adhere to a list of requirements. You can
easily see if your system passes all requirements by running the ``web/config.php``
in your symfony distribution. Since the CLI often uses a different ``php.ini``
configuration file, it's also a good idea to check your requirements from
the command line via:

.. code-block:: bash

    php app/check.php

Below is the list of required and optional requirements.

Required
--------

* PHP needs to be a minimum version of PHP 5.3.2
* Your PHP.ini needs to have the date.timezone setting

Optional
--------

* You need to have the PHP-XML module installed
* You need to have at least version 2.6.21 of libxml
* PHP tokenizer needs to be enabled
* mbstring functions need to be enabled
* iconv needs to be enabled
* POSIX needs to be enabled
* Intl needs to be installed
* APC (or another opcode cache needs to be installed)
* PHP.ini recommended settings

    * short_open_tags: off
    * magic_quotes_gpc: off
    * register_globals: off
    * session.autostart: off
    
Doctrine
--------

If you want to use Doctrine, you will need to have PDO installed. Additionally,
you need to have the PDO driver installed for the database server you want
to use.