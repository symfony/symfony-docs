.. index::
    single: Web Server

Configuring a Web Server
========================

The preferred way to develop your Symfony application is to use
:doc:`Symfony Local Web Server </setup/symfony_server>`.

However, when running the application in the production environment, you'll need
to use a fully-featured web server. This article describes several ways to use
Symfony with Apache or Nginx.

When using Apache, you can configure PHP as an
:ref:`Apache module <web-server-apache-mod-php>` or with FastCGI using
:ref:`PHP FPM <web-server-apache-fpm>`. FastCGI also is the preferred way
to use PHP :ref:`with Nginx <web-server-nginx>`.

.. sidebar:: The ``public/`` directory

    The ``public/`` directory is the home of all of your application's public and
    static files, including images, stylesheets and JavaScript files. It is
    also where the front controller (``index.php``) lives.

    The public directory serves as the document root when configuring your
    web server. In the examples below, the ``public/`` directory will be the
    document root. This directory is ``/var/www/project/public/``.

    If your hosting provider requires you to change the ``public/`` directory to
    another location (e.g. ``public_html/``) make sure you
    :ref:`override the location of the public/ directory <override-web-dir>`.

.. _web-server-nginx:

Nginx
-----

The **minimum configuration** to get your application running under Nginx is:

.. code-block:: nginx

    server {
        server_name domain.tld www.domain.tld;
        root /var/www/project/public;

        location / {
            # try to serve file directly, fallback to index.php
            try_files $uri /index.php$is_args$args;
        }

        # optionally disable falling back to PHP script for the asset directories;
        # nginx will return a 404 error when files are not found instead of passing the
        # request to Symfony (improves performance but Symfony's 404 page is not displayed)
        # location /bundles {
        #     try_files $uri =404;
        # }

        location ~ ^/index\.php(/|$) {
            fastcgi_pass unix:/var/run/php/php-fpm.sock;
            fastcgi_split_path_info ^(.+\.php)(/.*)$;
            include fastcgi_params;

            # optionally set the value of the environment variables used in the application
            # fastcgi_param APP_ENV prod;
            # fastcgi_param APP_SECRET <app-secret-id>;
            # fastcgi_param DATABASE_URL "mysql://db_user:db_pass@host:3306/db_name";

            # When you are using symlinks to link the document root to the
            # current version of your application, you should pass the real
            # application path instead of the path to the symlink to PHP
            # FPM.
            # Otherwise, PHP's OPcache may not properly detect changes to
            # your PHP files (see https://github.com/zendtech/ZendOptimizerPlus/issues/126
            # for more information).
            # Caveat: When PHP-FPM is hosted on a different machine from nginx
            #         $realpath_root may not resolve as you expect! In this case try using
            #         $document_root instead.
            fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
            fastcgi_param DOCUMENT_ROOT $realpath_root;
            # Prevents URIs that include the front controller. This will 404:
            # http://domain.tld/index.php/some-path
            # Remove the internal directive to allow URIs like this
            internal;
        }

        # return 404 for all other php files not matching the front controller
        # this prevents access to other php files you don't want to be accessible.
        location ~ \.php$ {
            return 404;
        }

        error_log /var/log/nginx/project_error.log;
        access_log /var/log/nginx/project_access.log;
    }

.. tip::

    If you use NGINX Unit, check out the official article about
    `How to run Symfony applications using NGINX Unit`_.

.. note::

    Depending on your PHP-FPM config, the ``fastcgi_pass`` can also be
    ``fastcgi_pass 127.0.0.1:9000``.

.. tip::

    This executes **only** ``index.php`` in the public directory. All other files
    ending in ".php" will be denied.

    If you have other PHP files in your public directory that need to be executed,
    be sure to include them in the ``location`` block above.

.. caution::

    After you deploy to production, make sure that you **cannot** access the ``index.php``
    script (i.e. ``http://example.com/index.php``).

For advanced Nginx configuration options, read the official `Nginx documentation`_.

.. _web-server-apache-mod-php:

Adding Rewrite Rules for Apache
-------------------------------

The easiest way is to install the ``apache`` :ref:`Symfony pack <symfony-packs>`
by executing the following command:

.. code-block:: terminal

    $ composer require symfony/apache-pack

This pack installs a ``.htaccess`` file in the ``public/`` directory that contains
the rewrite rules needed to serve the Symfony application.

In production servers, you should move the ``.htaccess`` rules into the main
Apache configuration file to improve performance. To do so, copy the
``.htaccess`` contents inside the ``<Directory>`` configuration associated to
the Symfony application ``public/`` directory (and replace ``AllowOverride All``
by ``AllowOverride None``):

.. code-block:: apache

    <VirtualHost *:80>
        # ...
        DocumentRoot /var/www/project/public

        <Directory /var/www/project/public>
            AllowOverride None

            # Copy .htaccess contents here
        </Directory>
    </VirtualHost>

Apache with mod_php/PHP-CGI
---------------------------

The **minimum configuration** to get your application running under Apache is:

.. code-block:: apache

    <VirtualHost *:80>
        ServerName domain.tld
        ServerAlias www.domain.tld

        DocumentRoot /var/www/project/public
        <Directory /var/www/project/public>
            AllowOverride All
            Order Allow,Deny
            Allow from All
        </Directory>

        # uncomment the following lines if you install assets as symlinks
        # or run into problems when compiling LESS/Sass/CoffeeScript assets
        # <Directory /var/www/project>
        #     Options FollowSymlinks
        # </Directory>

        ErrorLog /var/log/apache2/project_error.log
        CustomLog /var/log/apache2/project_access.log combined
    </VirtualHost>

.. tip::

    If your system supports the ``APACHE_LOG_DIR`` variable, you may want
    to use ``${APACHE_LOG_DIR}/`` instead of hardcoding ``/var/log/apache2/``.

Use the following **optimized configuration** to disable ``.htaccess`` support
and increase web server performance:

.. code-block:: apache

    <VirtualHost *:80>
        ServerName domain.tld
        ServerAlias www.domain.tld

        DocumentRoot /var/www/project/public
        DirectoryIndex /index.php

        <Directory /var/www/project/public>
            AllowOverride None
            Order Allow,Deny
            Allow from All

            FallbackResource /index.php
        </Directory>

        # uncomment the following lines if you install assets as symlinks
        # or run into problems when compiling LESS/Sass/CoffeeScript assets
        # <Directory /var/www/project>
        #     Options FollowSymlinks
        # </Directory>

        # optionally disable the fallback resource for the asset directories
        # which will allow Apache to return a 404 error when files are
        # not found instead of passing the request to Symfony
        <Directory /var/www/project/public/bundles>
            DirectoryIndex disabled
            FallbackResource disabled
        </Directory>
        ErrorLog /var/log/apache2/project_error.log
        CustomLog /var/log/apache2/project_access.log combined

        # optionally set the value of the environment variables used in the application
        #SetEnv APP_ENV prod
        #SetEnv APP_SECRET <app-secret-id>
        #SetEnv DATABASE_URL "mysql://db_user:db_pass@host:3306/db_name"
    </VirtualHost>

.. caution::

    Use ``FallbackResource`` on Apache 2.4.25 or higher, due to a bug which was
    fixed on that release causing the root ``/`` to hang.

.. tip::

    If you are using **php-cgi**, Apache does not pass HTTP basic username and
    password to PHP by default. To work around this limitation, you should use
    the following configuration snippet:

    .. code-block:: apache

        RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

Using mod_php/PHP-CGI with Apache 2.4
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In Apache 2.4, ``Order Allow,Deny`` has been replaced by ``Require all granted``.
Hence, you need to modify your ``Directory`` permission settings as follows:

.. code-block:: apache

    <Directory /var/www/project/public>
        Require all granted
        # ...
    </Directory>

For advanced Apache configuration options, read the official `Apache documentation`_.

.. _web-server-apache-fpm:

Apache with PHP-FPM
-------------------

To make use of PHP-FPM with Apache, you first have to ensure that you have
the FastCGI process manager ``php-fpm`` binary and Apache's FastCGI module
installed (for example, on a Debian based system you have to install the
``libapache2-mod-fastcgi`` and ``php<version>-fpm`` packages).

PHP-FPM uses so-called *pools* to handle incoming FastCGI requests. You can
configure an arbitrary number of pools in the FPM configuration. In a pool
you configure either a TCP socket (IP and port) or a Unix domain socket to
listen on. Each pool can also be run under a different UID and GID:

.. code-block:: ini

    ; a pool called www
    [www]
    user = www-data
    group = www-data

    ; use a unix domain socket
    listen = /var/run/php/php-fpm.sock

    ; or listen on a TCP socket
    listen = 127.0.0.1:9000

Using mod_proxy_fcgi with Apache 2.4
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you are running Apache 2.4, you can use ``mod_proxy_fcgi`` to pass incoming
requests to PHP-FPM. Configure PHP-FPM to listen on a TCP or Unix socket, enable
``mod_proxy`` and ``mod_proxy_fcgi`` in your Apache configuration, and use the
``SetHandler`` directive to pass requests for PHP files to PHP FPM:

.. code-block:: apache

    <VirtualHost *:80>
        ServerName domain.tld
        ServerAlias www.domain.tld

        # Uncomment the following line to force Apache to pass the Authorization
        # header to PHP: required for "basic_auth" under PHP-FPM and FastCGI
        #
        # SetEnvIfNoCase ^Authorization$ "(.+)" HTTP_AUTHORIZATION=$1

        # For Apache 2.4.9 or higher
        # Using SetHandler avoids issues with using ProxyPassMatch in combination
        # with mod_rewrite or mod_autoindex
        <FilesMatch \.php$>
            SetHandler proxy:fcgi://127.0.0.1:9000
            # for Unix sockets, Apache 2.4.10 or higher
            # SetHandler proxy:unix:/path/to/fpm.sock|fcgi://dummy
        </FilesMatch>

        # If you use Apache version below 2.4.9 you must consider update or use this instead
        # ProxyPassMatch ^/(.*\.php(/.*)?)$ fcgi://127.0.0.1:9000/var/www/project/public/$1

        # If you run your Symfony application on a subpath of your document root, the
        # regular expression must be changed accordingly:
        # ProxyPassMatch ^/path-to-app/(.*\.php(/.*)?)$ fcgi://127.0.0.1:9000/var/www/project/public/$1

        DocumentRoot /var/www/project/public
        <Directory /var/www/project/public>
            # enable the .htaccess rewrites
            AllowOverride All
            Require all granted
        </Directory>

        # uncomment the following lines if you install assets as symlinks
        # or run into problems when compiling LESS/Sass/CoffeeScript assets
        # <Directory /var/www/project>
        #     Options FollowSymlinks
        # </Directory>

        ErrorLog /var/log/apache2/project_error.log
        CustomLog /var/log/apache2/project_access.log combined
    </VirtualHost>

PHP-FPM with Apache 2.2
~~~~~~~~~~~~~~~~~~~~~~~

On Apache 2.2 or lower, you cannot use ``mod_proxy_fcgi``. You have to use
the `FastCgiExternalServer`_ directive instead. Therefore, your Apache configuration
should look something like this:

.. code-block:: apache

    <VirtualHost *:80>
        ServerName domain.tld
        ServerAlias www.domain.tld

        AddHandler php7-fcgi .php
        Action php7-fcgi /php7-fcgi
        Alias /php7-fcgi /usr/lib/cgi-bin/php7-fcgi
        FastCgiExternalServer /usr/lib/cgi-bin/php7-fcgi -host 127.0.0.1:9000 -pass-header Authorization

        DocumentRoot /var/www/project/public
        <Directory /var/www/project/public>
            # enable the .htaccess rewrites
            AllowOverride All
            Order Allow,Deny
            Allow from all
        </Directory>

        # uncomment the following lines if you install assets as symlinks
        # or run into problems when compiling LESS/Sass/CoffeeScript assets
        # <Directory /var/www/project>
        #     Options FollowSymlinks
        # </Directory>

        ErrorLog /var/log/apache2/project_error.log
        CustomLog /var/log/apache2/project_access.log combined
    </VirtualHost>

If you prefer to use a Unix socket, you have to use the ``-socket`` option
instead:

.. code-block:: apache

    FastCgiExternalServer /usr/lib/cgi-bin/php7-fcgi -socket /var/run/php/php-fpm.sock -pass-header Authorization

.. _`Apache documentation`: https://httpd.apache.org/docs/
.. _`FastCgiExternalServer`: https://docs.oracle.com/cd/B31017_01/web.1013/q20204/mod_fastcgi.html#FastCgiExternalServer
.. _`Nginx documentation`: https://www.nginx.com/resources/wiki/start/topics/recipes/symfony/
.. _`How to run Symfony applications using NGINX Unit`: https://unit.nginx.org/howto/symfony/
