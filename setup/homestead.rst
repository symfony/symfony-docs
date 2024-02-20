Using Symfony with Homestead/Vagrant
====================================

In order to develop a Symfony application, you might want to use a virtual
development environment instead of the built-in server or WAMP/LAMP. `Homestead`_
is an easy-to-use `Vagrant`_ box to get a virtual environment up and running
quickly.

.. tip::

    Due to the amount of filesystem operations in Symfony (e.g. updating cache
    files and writing to log files), Symfony can slow down significantly. To
    improve the speed, consider :ref:`overriding the cache and log directories <override-cache-dir>`
    to a location outside the NFS share (for instance, by using
    :phpfunction:`sys_get_temp_dir`). You can read `this blog post`_ for more
    tips to speed up Symfony on Vagrant.

Install Vagrant and Homestead
-----------------------------

Before you can use Homestead, you need to install and configure Vagrant and
Homestead as explained in `the Homestead documentation`_.

Setting Up a Symfony Application
--------------------------------

Imagine you've installed your Symfony application in
``~/projects/symfony_demo`` on your local system. You first need Homestead to
sync your files in this project. Run ``homestead edit`` to edit the
Homestead configuration and configure the ``~/projects`` directory:

.. code-block:: yaml

    # ...
    folders:
        - map: ~/projects
          to: /home/vagrant/projects

The ``projects/`` directory on your PC is now accessible at
``/home/vagrant/projects`` in the Homestead environment.

After you've done this, configure the Symfony application in the Homestead
configuration:

.. code-block:: yaml

    # ...
    sites:
        - map: symfony-demo.test
          to: /home/vagrant/projects/symfony_demo/public
          type: symfony4

The ``type`` option tells Homestead to use the Symfony nginx configuration.
Homestead now supports a Symfony 2 and 3 web layout with ``app.php`` and
``app_dev.php`` when using type ``symfony2`` and an ``index.php`` layout when
using type ``symfony4``.

At last, edit the hosts file on your local machine to map ``symfony-demo.test``
to ``192.168.10.10`` (which is the IP used by Homestead):

.. code-block:: text

    # /etc/hosts (unix) or C:\Windows\System32\drivers\etc\hosts (Windows)
    192.168.10.10 symfony-demo.test

Now, navigate to ``http://symfony-demo.test`` in your web browser and enjoy
developing your Symfony application!

.. seealso::

    To learn more features of Homestead, including Blackfire Profiler
    integration, automatic creation of MySQL databases and more, read the
    `Daily Usage`_ section of the Homestead documentation.

.. _`Homestead`: https://laravel.com/docs/homestead
.. _`Vagrant`: https://www.vagrantup.com/
.. _`the Homestead documentation`: https://laravel.com/docs/homestead#installation-and-setup
.. _`Daily Usage`: https://laravel.com/docs/homestead#daily-usage
.. _`this blog post`: https://beberlei.de/2013/08/19/speedup_symfony2_on_vagrant_boxes.html
