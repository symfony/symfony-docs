.. index::
   single: Workflow; Vagrant

Symfony Standard Edition with Vagrant
=====================================

You can easily setup a development environment for the Symfony Standard Edition
by using Vagrant. Vagrant is a tool that can create a virtual machine (based on
a set of configuration files) with simple commands. In this case, you will be
creating a virtual machine that runs Linux and has an Apache web server, a
MySQL database server, and PHP (a LAMP stack). When you are finished creating
this Vagrant configuration in your project, you can store the files in your
version control system (git, svn, ...) and work with other developers without
having to help them configure their development machines! This configuration
is also a great way to try out the Symfony Standard Edition without having to
know how to configure a web server or database server.

Prerequisites
-------------

#. Download and install the latest `VirtualBox`_.
#. Download and install the latest `Vagrant`_.
#. Install the Symfony Standard Edition as detailed in :doc:`/cookbook/workflow/new_project_git`.

Setup
-----

You will be creating a set of files under a new ``vagrant`` directory:

.. code-block:: text

    vagrant/.gitignore
    vagrant/Vagrantfile
    vagrant/puppet/modules.sh
    vagrant/puppet/manifests/symfony.pp

#. Create a new set of directories at the root of your project to store the
   Vagrant configuration files:

   .. code-block:: bash

        $ mkdir vagrant
        $ mkdir vagrant/puppet
        $ mkdir vagrant/puppet/manifests

#. Create a new file ``vagrant/Vagrantfile`` and paste the following into it.

   .. code-block:: text

        # -*- mode: ruby -*-
        # vi: set ft=ruby :

        Vagrant.configure("2") do |config|
          config.vm.box = "ubuntu/trusty32"

          config.vm.network :private_network, ip: "192.168.33.10"

          config.vm.synced_folder "..", "/vagrant" #, :nfs => true

          config.vm.provision :shell, :path => "puppet/modules.sh"

          config.vm.provision :puppet do |puppet|
            puppet.manifests_path = "puppet/manifests"
            puppet.manifest_file  = "symfony.pp"
            puppet.facter = {
                "fqdn"           => "symfony.local",
                "host_ipaddress" => "192.168.33.1",
            }
          end
        end

   This is the main configuration file used by Vagrant. The ``config.vm.box``
   value specifies that a preconfigured "box" will be used for the base virtual
   machine. This ``ubuntu/trusty32`` reference happens to be a 32bit Ubuntu
   Linux machine with certain packages already installed (e.g. `Puppet`_).

   The ``config.vm.network`` will create a `private network`_ and specify the IP
   address of the virtual machine in that network. You can change the IP
   address to a different private IP address if you wish (such as
   192.168.50.12, 172.16.32.64, or 10.9.8.7 just to list a few examples), just
   be sure to update the ``host_ipaddress`` value in the ``puppet.facter``
   section as well. The last number in the ``host_ipaddress`` must be 1 (so the
   example host IP address values would be 192.168.50.1, 172.16.32.1, or
   10.9.8.1 respectively).

   The ``config.vm.synced_folder`` specifies that the directory one level above
   this file (your project directory) will be synced with the ``/vagrant``
   directory within the virtual machine. When you make a change to a file in
   your project directory, that change should be reflected in the virtual
   machine. The reverse is true as well. The commented out `NFS setting`_ can
   be useful but is not required. If you would like to use NFS, just uncomment
   the setting (remove the ``#``).

   The ``config.vm.provision`` sections will execute the
   ``vagrant/puppet/modules.sh`` and ``vagrant/puppet/manifests/symfony.pp``
   scripts that you will create next.

   There are a number of other settings for the `Vagrantfile`_ which you can
   use to customize your virtual machine. These are just the basics to get you
   started.

#. Create a new file ``vagrant/puppet/modules.sh`` and paste the following
   into it.

   .. code-block:: text

        #!/bin/sh

        if [ ! -d "/etc/puppet/modules" ]; then
            mkdir -p /etc/puppet/modules;
        fi

        if [ ! -d "/etc/puppet/modules/apache" ]; then
            puppet module install -v 1.2.0 puppetlabs-apache;
        fi

        if [ ! -d "/etc/puppet/modules/mysql" ]; then
            puppet module install -v 3.1.0 puppetlabs-mysql;
        fi

        if [ ! -d "/etc/puppet/modules/apt" ]; then
            puppet module install -v 1.7.0 puppetlabs-apt;
        fi

        if [ ! -d "/etc/puppet/modules/git" ]; then
            puppet module install -v 0.3.0 puppetlabs-git;
        fi

   This script will be executed within the virtual machine to install necessary
   Puppet modules for the next script.

#. Create a new file ``vagrant/puppet/manifests/symfony.pp`` and paste the
   following into it.

   .. code-block:: text

        # update system first before new packages are installed
        class { 'apt':
            always_apt_update => true,
        }
        Exec['apt_update'] -> Package <| |>


        # install Apache
        class { 'apache':
            mpm_module => 'prefork',
            sendfile   => 'Off',
        }
        class { 'apache::mod::php': }


        # install MySQL
        class { '::mysql::server':
            root_password => 'symfony',
        }
        class { '::mysql::bindings':
            php_enable => true,
        }


        # install Git for composer
        class { 'git': }


        # install PHP Extensions used with Symfony
        class php-extensions {
            package { ['php-apc', 'php5-curl', 'php5-intl', 'php5-xdebug']:
                ensure  => present,
                require => Package['httpd'],
                notify  => Service['httpd'],
            }
        }

        include php-extensions


        # install a local composer.phar file
        class composer {
            exec { 'composerPhar':
                user    => 'vagrant',
                cwd     => '/vagrant',
                command => 'curl -s http://getcomposer.org/installer | php',
                path    => ['/bin', '/usr/bin'],
                creates => '/vagrant/composer.phar',
                require => [ Class['apache::mod::php', 'git'], Package['curl'] ],
            }

            package { 'curl':
                ensure => present,
            }
        }

        include composer


        # install the Symfony vendors using composer
        class symfony {
            exec { 'vendorsInstall':
                user        => 'vagrant',
                cwd         => '/vagrant',
                environment => ['COMPOSER_HOME=/home/vagrant/.composer'],
                command     => 'php composer.phar install',
                timeout     => 1200,
                path        => ['/bin', '/usr/bin'],
                creates     => '/vagrant/vendor',
                logoutput   => true,
                require     => [ Class['php-extensions'], Exec['composerPhar'] ],
            }
        }

        include symfony


        # Create a web server host using the Symfony web/ directory
        apache::vhost { 'www.symfony.local':
            priority      => '10',
            port          => '80',
            docroot_owner => 'vagrant',
            docroot_group => 'vagrant',
            docroot       => '/vagrant/web/',
            logroot       => '/vagrant/app/logs/',
            serveraliases => ['symfony.local',],
        }

        # Create a database for Symfony
        mysql::db { 'symfony':
            user     => 'symfony',
            password => 'symfony',
            host     => 'localhost',
            grant    => ['all'],
        }


        # Configure Apache files to run as the "vagrant" user so that Symfony
        # app/cache and app/logs files can be successfully created and accessed
        # by the web server

        file_line { 'apache_user':
            path    => '/etc/apache2/apache2.conf',
            match   => 'User ',
            line    => 'User vagrant',
            require => Package['httpd'],
            notify  => Service['httpd'],
        }

        file_line { 'apache_group':
            path    => '/etc/apache2/apache2.conf',
            match   => 'Group ',
            line    => 'Group vagrant',
            require => Package['httpd'],
            notify  => Service['httpd'],
        }


        # Configure php.ini to follow recommended Symfony web/config.php settings

        file_line { 'php5_apache2_short_open_tag':
            path    => '/etc/php5/apache2/php.ini',
            match   => 'short_open_tag =',
            line    => 'short_open_tag = Off',
            require => Class['apache::mod::php'],
            notify  => Service['httpd'],
        }

        file_line { 'php5_cli_short_open_tag':
            path    => '/etc/php5/cli/php.ini',
            match   => 'short_open_tag =',
            line    => 'short_open_tag = Off',
            require => Class['apache::mod::php'],
            notify  => Service['httpd'],
        }

        file_line { 'php5_apache2_date_timezone':
            path    => '/etc/php5/apache2/php.ini',
            match   => 'date.timezone =',
            line    => 'date.timezone = UTC',
            require => Class['apache::mod::php'],
            notify  => Service['httpd'],
        }

        file_line { 'php5_cli_date_timezone':
            path    => '/etc/php5/cli/php.ini',
            match   => 'date.timezone =',
            line    => 'date.timezone = UTC',
            require => Class['apache::mod::php'],
            notify  => Service['httpd'],
        }

        file_line { 'php5_apache2_xdebug_max_nesting_level':
            path    => '/etc/php5/apache2/conf.d/20-xdebug.ini',
            line    => 'xdebug.max_nesting_level = 250',
            require => [ Class['apache::mod::php'], Package['php5-xdebug'] ],
            notify  => Service['httpd'],
        }

        file_line { 'php5_cli_xdebug_max_nesting_level':
            path    => '/etc/php5/cli/conf.d/20-xdebug.ini',
            line    => 'xdebug.max_nesting_level = 250',
            require => [ Class['apache::mod::php'], Package['php5-xdebug'] ],
            notify  => Service['httpd'],
        }


        # Enable Xdebug support

        file_line { 'php5_apache2_xdebug_remote_enable':
            path    => '/etc/php5/apache2/conf.d/20-xdebug.ini',
            line    => 'xdebug.remote_enable = on',
            require => [ Class['apache::mod::php'], Package['php5-xdebug'] ],
            notify  => Service['httpd'],
        }

        file_line { 'php5_cli_xdebug_remote_enable':
            path    => '/etc/php5/cli/conf.d/20-xdebug.ini',
            line    => 'xdebug.remote_enable = on',
            require => [ Class['apache::mod::php'], Package['php5-xdebug'] ],
            notify  => Service['httpd'],
        }

        file_line { 'php5_apache2_xdebug_remote_connect_back':
            path    => '/etc/php5/apache2/conf.d/20-xdebug.ini',
            line    => 'xdebug.remote_connect_back = on',
            require => [ Class['apache::mod::php'], Package['php5-xdebug'] ],
            notify  => Service['httpd'],
        }

        file_line { 'php5_cli_xdebug_remote_connect_back':
            path    => '/etc/php5/cli/conf.d/20-xdebug.ini',
            line    => 'xdebug.remote_connect_back = on',
            require => [ Class['apache::mod::php'], Package['php5-xdebug'] ],
            notify  => Service['httpd'],
        }


        # Configure Symfony dev controllers so that the Vagrant host machine
        # at the host_ipaddress (specified in the Vagrantfile) has access

        file_line { 'symfony_web_config_host_ipaddress':
            path  => '/vagrant/web/config.php',
            match => '::1',
            line  => "    '::1', '${::host_ipaddress}',",
        }

        file_line { 'symfony_web_app_dev_host_ipaddress':
            path  => '/vagrant/web/app_dev.php',
            match => '::1',
            line  => "    || !(in_array(@\$_SERVER['REMOTE_ADDR'], array('127.0.0.1', 'fe80::1', '::1', '${::host_ipaddress}')) || php_sapi_name() === 'cli-server')",
        }

   This file performs the bulk of the work to configure your virtual machine
   for web development. It will install Apache, MySQL, PHP, and Git. It will
   configure Apache to use recommended Symfony settings and will set your
   project ``web/`` directory as the web server's document root. If there are
   no vendors in your project, it will execute ``php composer.phar install`` to
   retrieve them. Also, it will update your project ``web/app_dev.php`` file to
   allow your physical host machine (specified by the ``host_ipaddress`` in the
   ``vagrant/Vagrantfile``) to have access to view your project website during
   development.

#. Create a new file ``vagrant/.gitignore`` and paste the following into it.

   .. code-block:: text

        .vagrant

   When the virtual machine is created by Vagrant, it will create a
   ``vagrant/.vagrant`` directory to store its files. That directory should not
   be committed in your version control system. This ``vagrant/.gitignore``
   file will prevent the ``vagrant/.vagrant`` directory from being listed in
   the ``git status`` command.

#. Switch to the vagrant directory.

   .. code-block:: bash

        $ cd vagrant

#. Create the development virtual machine.

   .. code-block:: bash

        $ vagrant up

A virtual machine is now being prepared in VirtualBox by Vagrant. This process
will take several minutes to complete on the initial run, so be patient. When
the process has completed, you can view the Symfony demo site in a browser at:

    http://192.168.33.10/app_dev.php

Now you can start developing with Symfony! Any changes made to your Symfony
project directory will appear in the virtual machine.

Further Configuration
---------------------

A MySQL database has been created on the Vagrant virtual machine which you can
use. Just update your ``app/config/parameters.yml`` file:

.. code-block:: yaml

    # app/config/parameters.yml
    parameters:
        database_driver:   pdo_mysql
        database_host:     127.0.0.1
        database_port:     ~
        database_name:     symfony
        database_user:     symfony
        database_password: symfony

The database name, user, and password are set to "symfony".

Other Vagrant Commands
----------------------

While you are in the ``vagrant`` directory, you can perform other commands.

If you came across an issue during the initial setup, execute:

.. code-block:: bash

    $ vagrant provision

This will execute the ``vagrant/puppet/modules.sh`` and
``vagrant/puppet/manifests/symfony.pp`` scripts again.

If you need to access the virtual machine command line, execute:

.. code-block:: bash

    $ vagrant ssh

While in the virtual machine command line, you can access your project code in
its ``/vagrant`` directory. This is useful when you want to update composer
dependencies for instance:

.. code-block:: bash

    $ vagrant ssh
    $ cd /vagrant
    $ php composer.phar update
    $ exit

If you need to refresh the virtual machine, execute:

.. code-block:: bash

    $ vagrant reload

If you are done developing and want to remove the virtual machine, execute:

.. code-block:: bash

    $ vagrant destroy

And if you want to install again after destroying, execute:

.. code-block:: bash

    $ vagrant up

Hopefully, your new Vagrant configuration will help you develop your Symfony
project without having to worry about your local server setup or the setup of
another developer's machine.

.. _`VirtualBox`: https://www.virtualbox.org/wiki/Downloads
.. _`Vagrant`: http://www.vagrantup.com/downloads.html
.. _`Puppet`: http://www.puppetlabs.com/
.. _`private network`: http://docs.vagrantup.com/v2/networking/private_network.html
.. _`NFS setting`: http://docs.vagrantup.com/v2/synced-folders/nfs.html
.. _`Vagrantfile`: http://docs.vagrantup.com/v2/vagrantfile/index.html
