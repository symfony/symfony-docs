.. index::
    double: Composer; Installation

Installing Composer
===================

`Composer`_ is the package manager used by modern PHP applications. Use Composer
to manage dependencies in your Symfony applications and to install Symfony Components
in your PHP projects.

It's recommended to install Composer globally in your system as explained in the
following sections.

Install Composer on Linux and Mac OS X
--------------------------------------

To install Composer on Linux or Mac OS X, execute the following two commands:

.. code-block:: bash

    $ curl -sS https://getcomposer.org/installer | php
    $ sudo mv composer.phar /usr/local/bin/composer

.. note::

    If you don't have ``curl`` installed, you can also just download the
    ``installer`` file manually at https://getcomposer.org/installer and
    then run:

    .. code-block:: bash

        $ php installer
        $ sudo mv composer.phar /usr/local/bin/composer

Install Composer on Windows
---------------------------

Download the installer from `getcomposer.org/download`_, execute it and follow
the instructions.

Learn more
----------

Read the `Composer documentation`_ to learn more about its usage and features.

.. _`Composer`: https://getcomposer.org/
.. _`getcomposer.org/download`: https://getcomposer.org/download
.. _`Composer documentation`: https://getcomposer.org/doc/00-intro.md
