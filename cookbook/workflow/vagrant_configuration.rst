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

1. Download and install the latest `Virtualbox`_.

2. Download and install the latest `Vagrant`_.

3. Install the Symfony Standard Edition as detailed in :doc:`/cookbook/workflow/new_project_git`.

Setup
-----

You will be creating a set of files under a new ``vagrant`` directory:

.. code-block:: text

    vagrant/.gitignore
    vagrant/Vagrantfile
    vagrant/puppet/modules.sh
    vagrant/puppet/manifests/symfony.pp

1. Create a new set of directories at the root of your project to store the
   Vagrant configuration files:

   .. code-block:: bash

        $ mkdir vagrant
        $ mkdir vagrant/puppet
        $ mkdir vagrant/puppet/manifests

2. Create a new file ``vagrant/Vagrantfile`` and paste the following into it.

   .. code-block:: text

        # -*- mode: ruby -*-
        # vi: set ft=ruby :

        Vagrant.configure("2") do |config|
          config.vm.box = "precise32"

          config.vm.box_url = "http://files.vagrantup.com/precise32.box"

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

3. Create a new file ``vagrant/puppet/modules.sh`` and paste the following
   into it.

   .. code-block:: text

        #!/bin/sh

        if [ ! -d "/etc/puppet/modules" ]; then
            mkdir -p /etc/puppet/modules;
        fi

        if [ ! -d "/etc/puppet/modules/apache" ]; then
            puppet module install puppetlabs-apache;
        fi

        if [ ! -d "/etc/puppet/modules/mysql" ]; then
            puppet module install puppetlabs-mysql;
        fi

        if [ ! -d "/etc/puppet/modules/apt" ]; then
            puppet module install puppetlabs-apt;
        fi

        if [ ! -d "/etc/puppet/modules/git" ]; then
            puppet module install puppetlabs-git;
        fi

4. Create a new file ``vagrant/puppet/manifests/symfony.pp`` and paste the
   following into it.

   .. code-block:: text

        # update system first before new packages are installed
        class { 'apt':
            always_apt_update => true,
        }
        Exec['apt_update'] -> Package <| |>


        # install Apache
        class { 'apache': }
        class { 'apache::mod::php': }


        # install MySQL
        class { 'mysql': }
        class { 'mysql::server':
            config_hash => { 'root_password' => 'symfony' },
        }
        class { 'mysql::php': }


        # install Git for composer
        class { 'git': }


        # install PHP Extensions used with Symfony
        class php-extensions {
            package { ['php-apc', 'php5-intl', 'php5-xdebug']:
                ensure  => latest,
                require => Package['httpd'],
                notify  => Service['httpd'],
            }
        }

        include php-extensions


        # install a local composer.phar file
        class composer {
            exec { 'composerPhar':
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
                cwd       => '/vagrant',
                command   => 'php composer.phar install',
                timeout   => 1200,
                path      => ['/bin', '/usr/bin'],
                creates   => '/vagrant/vendor',
                logoutput => true,
                require   => Exec['composerPhar'],
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
            path    => '/etc/apache2/httpd.conf',
            line    => 'User vagrant',
            require => Package['httpd'],
            notify  => Service['httpd'],
        }

        file_line { 'apache_group':
            path    => '/etc/apache2/httpd.conf',
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
            path    => '/etc/php5/apache2/conf.d/xdebug.ini',
            line    => 'xdebug.max_nesting_level = 250',
            require => [ Class['apache::mod::php'], Package['php5-xdebug'] ],
            notify  => Service['httpd'],
        }

        file_line { 'php5_cli_xdebug_max_nesting_level':
            path    => '/etc/php5/cli/conf.d/xdebug.ini',
            line    => 'xdebug.max_nesting_level = 250',
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
            line  => "    || !in_array(@\$_SERVER['REMOTE_ADDR'], array('127.0.0.1', 'fe80::1', '::1', '${::host_ipaddress}'))",
        }

5. Create a new file ``vagrant/.gitignore`` and paste the following into it.

   .. code-block:: text

        .vagrant

6. Switch to the vagrant directory.

   .. code-block:: bash

        $ cd vagrant

7. Create the development virtual machine.

   .. code-block:: bash

        $ vagrant up

A virtual machine is now being prepared in Virtualbox by Vagrant. This process
will take several minutes to complete on the initial run, so be patient. When
the process has completed, you can view the Symfony demo site in a browser at:

http://192.168.33.10/app_dev.php

Now you can start developing with Symfony! Any changes made to your Symfony
project directory will appear in the virtual machine.

Further Configuration
---------------------

A MySQL database has been created on the Vagrant virtual machine which you can
use. Just update your app/config/parameters.yml file:

.. code-block:: yaml

    parameters:
        database_driver:   pdo_mysql
        database_host:     127.0.0.1
        database_port:     ~
        database_name:     symfony
        database_user:     symfony
        database_password: symfony

The database name, user, and password are "symfony".

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

Hopefully your new Vagrant configuration will help you develop your Symfony
project without having to worry about your local server setup or the setup of
another developer's machine.

.. _`Virtualbox`: https://www.virtualbox.org/wiki/Downloads
.. _`Vagrant`: http://downloads.vagrantup.com/
