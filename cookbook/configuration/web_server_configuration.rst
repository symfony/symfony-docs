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
        ServerName www.domain.tld

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
    into the virtualhost config.

Nginx
-----

For advanced Nginx configuration options, see the official `Nginx`_
documentation. The minimum basics to get your application running under Nginx
are:

.. code-block:: nginx

    server {
        server_name www.domain.tld;
        root /var/www/project/web;

        location / {
            # try to serve file directly, fallback to rewrite
            try_files $uri @rewriteapp;
        }

        location @rewriteapp {
            # rewrite all to app.php
            rewrite ^(.*)$ /app.php/$1 last;
        }

        location ~ ^/(app|app_dev)\.php(/|$) {
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

    This executes **only** ``app.php`` and ``app_dev.php`` in the web directory.
    All other files will be served as text. If you have other PHP files in
    your web directory, be sure to include them in the ``location`` block
    above.

.. _`Apache`: http://httpd.apache.org/docs/current/mod/core.html#documentroot
.. _`Nginx`: http://wiki.nginx.org/Symfony
