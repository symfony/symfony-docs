.. index::
    single: Web Server

Configuring a web server
========================

The web directory is the home of all of your application's public and static
files. Including images, stylesheets and JavaScript files. It is also where the
front controllers live. For more details, see the :ref:`the-web-directory`.

The web directory services as the document root when configuring your web
server. In the examples below, this directory is in ``/var/www/project/web/``.

Apache2
-------

For advanced Apache configuration options, see the official `Apache`_
documentation. The minimum basics to get your application running under Apache2
are:

.. code-block:: apache

    <VirtualHost *:80>
        ServerName domain.tld
        ServerAlias www.domain.tld

        DocumentRoot /var/www/project/web
        <Directory /var/www/project/web>
            # enable the .htaccess rewrites
            AllowOverride All
            Order allow,deny
            Allow from All
        </Directory>

        ErrorLog /var/log/apache2/project_error.log
        CustomLog /var/log/apache2/project_access.log combined
    </VirtualHost>

.. note::

    For performance reasons, you will probably want to set
    ``AllowOverride None`` and implement the rewrite rules in the ``web/.htaccess``
    into the ``VirtualHost`` config.

If you are using **php-cgi**, Apache does not pass HTTP basic username and
password to PHP by default. To work around this limitation, you should use the
following configuration snippet:

.. code-block:: apache

    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

.. caution::

    In Apache 2.4, ``Order allow,deny`` has been replaced by ``Require all granted``,
    and hence you need to modify your ``Directory`` permission settings as follows:

    .. code-block:: apache

        <Directory /var/www/project/web>
            # enable the .htaccess rewrites
            AllowOverride All
            Require all granted
        </Directory>

Nginx
-----

For advanced Nginx configuration options, see the official `Nginx`_
documentation. The minimum basics to get your application running under Nginx
are:

.. code-block:: nginx

    server {
        server_name domain.tld www.domain.tld;
        root /var/www/project/web;

        location / {
            # try to serve file directly, fallback to app.php
            try_files $uri /app.php$is_args$args;
        }

        location ~ ^/(app|app_dev|config)\.php(/|$) {
            fastcgi_pass unix:/var/run/php5-fpm.sock;
            fastcgi_split_path_info ^(.+\.php)(/.*)$;
            include fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_param HTTPS off;
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
    IP checking code at the top of each file does this by default).

    If you have other PHP files in your web directory that need to be executed,
    be sure to include them in the ``location`` block above.

.. _`Apache`: http://httpd.apache.org/docs/current/mod/core.html#documentroot
.. _`Nginx`: http://wiki.nginx.org/Symfony
