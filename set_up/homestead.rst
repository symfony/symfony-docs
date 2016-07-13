.. index:: Vagrant, Homestead

Using Symfony with Homestead/Vagrant
====================================

In order to develop a Symfony application, you might want to use a virtual
development environment instead of the built-in server or WAMP/LAMP. Homestead_
is an easy-to-use Vagrant_ box to get a virtual environment up and running
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
sync your files in this project. Execute ``homestead edit`` to edit the
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
        - map: symfony-demo.dev
          to: /home/vagrant/projects/symfony_demo/web
          type: symfony

The ``type`` option tells Homestead to use the Symfony nginx configuration.

At last, edit the hosts file on your local machine to map ``symfony-demo.dev``
to ``192.168.10.10`` (which is the IP used by Homestead)::

    # /etc/hosts (unix) or C:\Windows\System32\drivers\etc\hosts (Windows)
    192.168.10.10 symfony-demo.dev

Now, navigate to ``http://symfony-demo.dev`` in your web browser and enjoy
developing your Symfony application!

.. seealso::

    To learn more features of Homestead, including Blackfire Profiler
    integration, automatic creation of MySQL databases and more, read the
    `Daily Usage`_ section of the Homestead documentation.

.. _Homestead: http://laravel.com/docs/homestead
.. _Vagrant: https://www.vagrantup.com/
.. _the Homestead documentation: http://laravel.com/docs/homestead#installation-and-setup
.. _Daily Usage: http://laravel.com/docs/5.1/homestead#daily-usage
.. _this blog post: http://www.whitewashing.de/2013/08/19/speedup_symfony2_on_vagrant_boxes.html
