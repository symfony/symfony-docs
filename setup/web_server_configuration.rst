Configuring a Web Server
========================

The preferred way to develop your Symfony application is to use
:doc:`Symfony Local Web Server </setup/symfony_server>`.

However, when running the application in the production environment, you'll need
to use a fully-featured web server. This article describes how to use Symfony
with Apache, Nginx or Caddy.

.. sidebar:: The public directory

    The public directory is the home of all of your application's public and
    static files, including images, stylesheets and JavaScript files. It is
    also where the front controller (``index.php``) lives.

    The public directory serves as the document root when configuring your
    web server. In the examples below, the ``public/`` directory will be the
    document root. This directory is ``/var/www/project/public/``.

    If your hosting provider requires you to change the ``public/`` directory to
    another location (e.g. ``public_html/``) make sure you
    :ref:`override the location of the public/ directory <override-web-dir>`.

Configuring PHP-FPM
-------------------

All configuration examples below use the PHP FastCGI process manager
(PHP-FPM). Ensure that you have installed PHP-FPM (for example, on a Debian
based system you have to install the ``php-fpm`` package).

PHP-FPM uses so-called *pools* to handle incoming FastCGI requests. You can
configure an arbitrary number of pools in the FPM configuration. In a pool
you configure either a TCP socket (IP and port) or a Unix domain socket to
listen on. Each pool can also be run under a different UID and GID:

.. code-block:: ini

    ; /etc/php/7.4/fpm/pool.d/www.conf

    ; a pool called www
    [www]
    user = www-data
    group = www-data

    ; use a unix domain socket
    listen = /var/run/php/php7.4-fpm.sock

    ; or listen on a TCP connection
    ; listen = 127.0.0.1:9000

Apache
------

If you are running Apache 2.4+, you can use ``mod_proxy_fcgi`` to pass
incoming requests to PHP-FPM. Install the Apache2 FastCGI mod
(``libapache2-mod-fastcgi`` on Debian), enable ``mod_proxy`` and
``mod_proxy_fcgi`` in your Apache configuration, and use the ``SetHandler``
directive to pass requests for PHP files to PHP FPM:

.. code-block:: apache

    # /etc/apache2/conf.d/example.com.conf
    <VirtualHost *:80>
        ServerName example.com
        ServerAlias www.example.com

        # Uncomment the following line to force Apache to pass the Authorization
        # header to PHP: required for "basic_auth" under PHP-FPM and FastCGI
        #
        # SetEnvIfNoCase ^Authorization$ "(.+)" HTTP_AUTHORIZATION=$1

        <FilesMatch \.php$>
            # when using PHP-FPM as a unix socket
            SetHandler proxy:unix:/var/run/php/php7.4-fpm.sock|fcgi://dummy

            # when PHP-FPM is configured to use TCP
            # SetHandler proxy:fcgi://127.0.0.1:9000
        </FilesMatch>

        DocumentRoot /var/www/project/public
        <Directory /var/www/project/public>
            AllowOverride None
            Require all granted
            FallbackResource /index.php
        </Directory>

        # uncomment the following lines if you install assets as symlinks
        # or run into problems when compiling LESS/Sass/CoffeeScript assets
        # <Directory /var/www/project>
        #     Options FollowSymlinks
        # </Directory>

        ErrorLog /var/log/apache2/project_error.log
        CustomLog /var/log/apache2/project_access.log combined
    </VirtualHost>

Nginx
-----

The **minimum configuration** to get your application running under Nginx is:

.. code-block:: nginx

    # /etc/nginx/conf.d/example.com.conf
    server {
        server_name example.com www.example.com;
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
            # when using PHP-FPM as a unix socket
            fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;

            # when PHP-FPM is configured to use TCP
            # fastcgi_pass 127.0.0.1:9000;

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
            # http://example.com/index.php/some-path
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

.. tip::

    This executes **only** ``index.php`` in the public directory. All other files
    ending in ".php" will be denied.

    If you have other PHP files in your public directory that need to be executed,
    be sure to include them in the ``location`` block above.

.. caution::

    After you deploy to production, make sure that you **cannot** access the ``index.php``
    script (i.e. ``http://example.com/index.php``).

For advanced Nginx configuration options, read the official `Nginx documentation`_.

Caddy
-----

When using Caddy on the server, you can use a configuration like this:

.. code-block:: text

    # /etc/caddy/Caddyfile
    example.com, www.example.com {
        root * /var/www/project/public

        # serve files directly if they can be found (e.g. CSS or JS files in public/)
        encode zstd gzip
        file_server


        # otherwise, use PHP-FPM (replace "unix//var/..." with "127.0.0.1:9000" when using TCP)
        php_fastcgi unix//var/run/php/php7.4-fpm.sock {
            # optionally set the value of the environment variables used in the application
            # env APP_ENV "prod"
            # env APP_SECRET "<app-secret-id>"
            # env DATABASE_URL "mysql://db_user:db_pass@host:3306/db_name"

            # Configure the FastCGI to resolve any symlinks in the root path.
            # This ensures that OpCache is using the destination filenames,
            # instead of the symlinks, to cache opcodes and php files see
            # https://caddy.community/t/root-symlink-folder-updates-and-caddy-reload-not-working/10557
            resolve_root_symlink
        }

        # return 404 for all other php files not matching the front controller
        # this prevents access to other php files you don't want to be accessible.
        @phpFile {
            path *.php*
        }
        error @phpFile "Not found" 404
    }

See the `official Caddy documentation`_ for more examples, such as using
Caddy in a container infrastructure.

.. _`Nginx documentation`: https://www.nginx.com/resources/wiki/start/topics/recipes/symfony/
.. _`How to run Symfony applications using NGINX Unit`: https://unit.nginx.org/howto/symfony/
.. _`official Caddy documentation`: https://caddyserver.com/docs/
