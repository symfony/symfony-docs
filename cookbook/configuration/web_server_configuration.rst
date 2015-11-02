.. index::
    single: Web Server

Configuring a Web Server
========================

The preferred way to develop your Symfony application is to use
:doc:`PHP's internal web server </cookbook/web_server/built_in>`. However,
when using an older PHP version or when running the application in the production
environment, you'll need to use a fully-featured web server. This article
describes several ways to use Symfony with Apache or Nginx.

When using Apache, you can configure PHP as an
:ref:`Apache module <web-server-apache-mod-php>` or with FastCGI using
:ref:`PHP FPM <web-server-apache-fpm>`. FastCGI also is the preferred way
to use PHP :ref:`with Nginx <web-server-nginx>`.

.. sidebar:: The Web Directory

    The web directory is the home of all of your application's public and
    static files, including images, stylesheets and JavaScript files. It is
    also where the front controllers (``app.php`` and ``app_dev.php``) live.

    The web directory serves as the document root when configuring your
    web server. In the examples below, the ``web/`` directory will be the
    document root. This directory is ``/var/www/project/web/``.

    If your hosting provider requires you to change the ``web/`` directory to
    another location (e.g. ``public_html/``) make sure you
    :ref:`override the location of the web/ directory <override-web-dir>`.

.. _web-server-apache-mod-php:

Apache with mod_php/PHP-CGI
---------------------------

The **minimum configuration** to get your application running under Apache is:

.. code-block:: apache

    <VirtualHost *:80>
        ServerName domain.tld
        ServerAlias www.domain.tld

        DocumentRoot /var/www/project/web
        <Directory /var/www/project/web>
            AllowOverride All
            Order Allow,Deny
            Allow from All
        </Directory>

        # uncomment the following lines if you install assets as symlinks
        # or run into problems when compiling LESS/Sass/CoffeScript assets
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

        DocumentRoot /var/www/project/web
        <Directory /var/www/project/web>
            AllowOverride None
            Order Allow,Deny
            Allow from All

            <IfModule mod_rewrite.c>
                Options -MultiViews
                RewriteEngine On
                RewriteCond %{REQUEST_FILENAME} !-f
                RewriteRule ^(.*)$ app.php [QSA,L]
            </IfModule>
        </Directory>

        # uncomment the following lines if you install assets as symlinks
        # or run into problems when compiling LESS/Sass/CoffeScript assets
        # <Directory /var/www/project>
        #     Options FollowSymlinks
        # </Directory>

        ErrorLog /var/log/apache2/project_error.log
        CustomLog /var/log/apache2/project_access.log combined
    </VirtualHost>

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

    <Directory /var/www/project/web>
        Require all granted
        # ...
    </Directory>

For advanced Apache configuration options, read the official `Apache documentation`_.

.. _web-server-apache-fpm:

Apache with PHP-FPM
-------------------

To make use of PHP5-FPM with Apache, you first have to ensure that you have
the FastCGI process manager ``php-fpm`` binary and Apache's FastCGI module
installed (for example, on a Debian based system you have to install the
``libapache2-mod-fastcgi`` and ``php5-fpm`` packages).

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
    listen = /var/run/php5-fpm.sock

    ; or listen on a TCP socket
    listen = 127.0.0.1:9000

Using mod_proxy_fcgi with Apache 2.4
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you are running Apache 2.4, you can easily use ``mod_proxy_fcgi`` to pass
incoming requests to PHP-FPM. Configure PHP-FPM to listen on a TCP socket
(``mod_proxy`` currently `does not support Unix sockets`_), enable ``mod_proxy``
and ``mod_proxy_fcgi`` in your Apache configuration and use the ``SetHandler``
directive to pass requests for PHP files to PHP FPM:

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
        </FilesMatch>

        # If you use Apache version below 2.4.9 you must consider update or use this instead
        # ProxyPassMatch ^/(.*\.php(/.*)?)$ fcgi://127.0.0.1:9000/var/www/project/web/$1

        # If you run your Symfony application on a subpath of your document root, the
        # regular expression must be changed accordingly:
        # ProxyPassMatch ^/path-to-app/(.*\.php(/.*)?)$ fcgi://127.0.0.1:9000/var/www/project/web/$1

        DocumentRoot /var/www/project/web
        <Directory /var/www/project/web>
            # enable the .htaccess rewrites
            AllowOverride All
            Require all granted
        </Directory>

        # uncomment the following lines if you install assets as symlinks
        # or run into problems when compiling LESS/Sass/CoffeScript assets
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

        AddHandler php5-fcgi .php
        Action php5-fcgi /php5-fcgi
        Alias /php5-fcgi /usr/lib/cgi-bin/php5-fcgi
        FastCgiExternalServer /usr/lib/cgi-bin/php5-fcgi -host 127.0.0.1:9000 -pass-header Authorization

        DocumentRoot /var/www/project/web
        <Directory /var/www/project/web>
            # enable the .htaccess rewrites
            AllowOverride All
            Order Allow,Deny
            Allow from all
        </Directory>

        # uncomment the following lines if you install assets as symlinks
        # or run into problems when compiling LESS/Sass/CoffeScript assets
        # <Directory /var/www/project>
        #     Options FollowSymlinks
        # </Directory>

        ErrorLog /var/log/apache2/project_error.log
        CustomLog /var/log/apache2/project_access.log combined
    </VirtualHost>

If you prefer to use a Unix socket, you have to use the ``-socket`` option
instead:

.. code-block:: apache

    FastCgiExternalServer /usr/lib/cgi-bin/php5-fcgi -socket /var/run/php5-fpm.sock -pass-header Authorization

.. _web-server-nginx:

Nginx
-----

The **minimum configuration** to get your application running under Nginx is:

.. code-block:: nginx

    server {
        server_name domain.tld www.domain.tld;
        root /var/www/project/web;

        location / {
            # try to serve file directly, fallback to app.php
            try_files $uri /app.php$is_args$args;
        }
        # DEV
        # This rule should only be placed on your development environment
        # In production, don't include this and don't deploy app_dev.php or config.php
        location ~ ^/(app_dev|config)\.php(/|$) {
            fastcgi_pass unix:/var/run/php5-fpm.sock;
            fastcgi_split_path_info ^(.+\.php)(/.*)$;
            include fastcgi_params;
            # When you are using symlinks to link the document root to the
            # current version of your application, you should pass the real
            # application path instead of the path to the symlink to PHP
            # FPM.
            # Otherwise, PHP's OPcache may not properly detect changes to
            # your PHP files (see https://github.com/zendtech/ZendOptimizerPlus/issues/126
            # for more information).
            fastcgi_param  SCRIPT_FILENAME  $realpath_root$fastcgi_script_name;
            fastcgi_param DOCUMENT_ROOT $realpath_root;
        }
        # PROD
        location ~ ^/app\.php(/|$) {
            fastcgi_pass unix:/var/run/php5-fpm.sock;
            fastcgi_split_path_info ^(.+\.php)(/.*)$;
            include fastcgi_params;
            # When you are using symlinks to link the document root to the
            # current version of your application, you should pass the real
            # application path instead of the path to the symlink to PHP
            # FPM.
            # Otherwise, PHP's OPcache may not properly detect changes to
            # your PHP files (see https://github.com/zendtech/ZendOptimizerPlus/issues/126
            # for more information).
            fastcgi_param  SCRIPT_FILENAME  $realpath_root$fastcgi_script_name;
            fastcgi_param DOCUMENT_ROOT $realpath_root;
            # Prevents URIs that include the front controller. This will 404:
            # http://domain.tld/app.php/some-path
            # Remove the internal directive to allow URIs like this
            internal;
        }

        error_log /var/log/nginx/project_error.log;
        access_log /var/log/nginx/project_access.log;
    }

.. note::

    Depending on your PHP-FPM config, the ``fastcgi_pass`` can also be
    ``fastcgi_pass 127.0.0.1:9000``.

.. tip::

    This executes **only** ``app.php``, ``app_dev.php`` and ``config.php`` in
    the web directory. All other files will be served as text. You **must**
    also make sure that if you *do* deploy ``app_dev.php`` or ``config.php``
    that these files are secured and not available to any outside user (the
    IP address checking code at the top of each file does this by default).

    If you have other PHP files in your web directory that need to be executed,
    be sure to include them in the ``location`` block above.

For advanced Nginx configuration options, read the official `Nginx documentation`_.

.. _`Apache documentation`: http://httpd.apache.org/docs/
.. _`does not support Unix sockets`: https://bz.apache.org/bugzilla/show_bug.cgi?id=54101
.. _`FastCgiExternalServer`: http://www.fastcgi.com/mod_fastcgi/docs/mod_fastcgi.html#FastCgiExternalServer
.. _`Nginx documentation`: http://wiki.nginx.org/Symfony
