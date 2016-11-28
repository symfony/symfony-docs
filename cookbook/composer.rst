.. index::
    double: Composer; Installation

Installing Composer
===================

`Composer`_ is the package manager used by modern PHP applications and the
recommended way to install Symfony2.

Install Composer on Linux and Mac OS X
--------------------------------------

To install Composer on Linux or Mac OS X, execute the following two commands:

.. code-block:: bash

    $ curl -sS https://getcomposer.org/installer | php
    $ sudo mv composer.phar /usr/local/bin/composer

.. note::

    If you don't have ``curl`` installed, you can also just download the
    ``installer`` file manually at http://getcomposer.org/installer and
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

You can read more about Composer in `its documentation`_.

.. _`Composer`: https://getcomposer.org/
.. _`getcomposer.org/download`: https://getcomposer.org/download
.. _`its documentation`: https://getcomposer.org/doc/00-intro.md
