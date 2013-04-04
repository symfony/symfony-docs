.. index::
   single: Web Server

Configuring web servers to run Symfony2 applications
====================================================

A good web practice is to put under the web root directory only the files that need to be accessed by a web browser, like stylesheets, Javascripts and images. By default, storing these files under the web/ sub-directory of a Symfony2 project is recommended.

If you have a look at this directory, you will find the two front controller files ``app.php`` and ``app_dev.php``. The front controllers are the only PHP files that need to be under the web root directory. All other PHP files can be hidden from the browser, which is a good idea as far as Security is concerned.

Symfony2 applications can be easily deployed on different modern webserver software.

Apache2
-------

This is the basic configuration for Symfony2 application run on apache2 server:

.. code-block:: apache

	<VirtualHost *:80>
		ServerName www.domain.com.localhost
		ServerAlias domain.com.localhost
		ServerAdmin webmaster@localhost

		DocumentRoot /home/user/www/project/web
		<Directory /home/user/www/project/web/>
			Options Indexes FollowSymLinks MultiViews
			AllowOverride None
			Order allow,deny
			allow from all
			<IfModule mod_rewrite.c>
				RewriteEngine On
				RewriteCond %{REQUEST_FILENAME} !-f
				RewriteRule ^(.*)$ /app.php [QSA,L]
			</IfModule>
		</Directory>
	</VirtualHost>

Remember that you need to have `mod_rewrite <http://httpd.apache.org/docs/2.2/mod/mod_rewrite.html>`_ installed.

Nginx
-----

minimum.conf:

.. code-block:: nginx

	server {
		listen   80;
		server_name  <your.site.com>;

		root /var/www/vhosts/<your.site.com>/site/www/;

		#site root is redirected to the app boot script
		location = / {
			try_files @site @site;
		}
		#all other locations try other files first and go to our front controller if none of them exists
		location / {
			try_files $uri $uri/ @site;
		}

		# deny access to .htaccess, .svn .bzr .git files
		location ~ /\.(ht|svn|bzr|git) {
			deny  all;
		}

		#return 404 for all php files as we do have a front controller
		location ~ \.php$ {
			return 404;
		}

		location @site {
			fastcgi_pass   unix:/var/run/php-fpm/www.sock;
			fastcgi_param  SCRIPT_FILENAME $document_root/index.php;
			fastcgi_ignore_client_abort on;
			#cache up to 256k, also sets headers size cache to 16k
			fastcgi_buffers 16 16k;
			#uncomment if your headers are more than 16k (e.g. huge cookies)
			#fastcgi_buffer_size 32k;
			#uncomment when running via htps
			#fastcgi_param HTTPS on;
			include fastcgi_params;
		}
	}

vhost.conf:

.. code-block:: nginx

	server {
		listen   80;
		server_name  <your.site.com>;

		access_log  /var/log/nginx/<your.site.com>.access.log;
		error_log   /var/log/nginx/<your.site.com>.error.log;

		index  index.html index.htm index.php;

		gzip             on;
		gzip_min_length  1000;
		gzip_proxied     expired no-cache no-store private auth;
		gzip_types       text/plain text/xml application/xml application/xml+rss text/css text/javascript application/javascript application/x-javascript application/json;
		gzip_disable     "MSIE [1-6]\.";
		gzip_static      on;
		gzip_buffers     32 8k;

		set $page_root /var/www/vhosts/<your.site.com>;

		root $page_root/site/www/;

		#site root is redirected to the app boot script
		location = / {
			try_files @site @site;
		}
		#all other locations try other files first and go to our front controller if none of them exists
		location / {
			try_files $uri $uri/ @site;
		}

		#in application you do this "X-Accel-Redirect: /storage/<file name>"
		location /storage/ {
			internal;
			root $page_root/storage/;
		}

		#error pages redirects to static htmls
		error_page  403  /403.html;
		location = /403.html {
			root   /var/www/default-pages;
		}

		error_page  404  /404.html;
		location = /404.html {
			root   /var/www/default-pages;
		}

		error_page   500 502 503 504  /50x.html;
		location = /50x.html {
			root   /var/www/default-pages;
		}

		# deny access to .htaccess, .svn .bzr .git files
		location ~ /\.(ht|svn|bzr|git) {
			deny  all;
		}

		# Support for various "default" files that should reside in a documen root. 
		# We return a 204 (No Content) if such file doesn't exist.
		location = /favicon.ico {
			try_files /favicon.ico =204;
		}
		location = /apple-touch-icon.png {
			try_files /apple-touch-icon.png =204;
		}
		location = /robots.txt {
			try_files /robots.txt =204;
		}
		location = /sitemap.xml {
			try_files /sitemap.xml =204;
		}

		#return 404 for all php files as we do have a front controller
		location ~ \.php$ {
			return 404;
		}

		#uncomment in production
		#location ~* \.(js|css|png|jpg|jpeg|gif|ico)$ {
		#	expires 7d;
		#	log_not_found off;
		#}

		location @site {
			fastcgi_pass   unix:/var/run/php-fpm/www.sock;
			fastcgi_param  SCRIPT_FILENAME $document_root/index.php;
			fastcgi_ignore_client_abort on;
			#cache up to 256k, also sets headers size cache to 16k
			fastcgi_buffers 16 16k;
			#uncomment if your headers are more than 16k (e.g. huge cookies)
			#fastcgi_buffer_size 32k;
			#comment in production
			fastcgi_param  APPLICATION_ENV development;
			#uncomment when running via htps
			#fastcgi_param HTTPS on;
			include fastcgi_params;
		}
	}

Originally from: https://gist.github.com/917200

Lighttpd
--------

The server is deployed on Ubuntu 12.04 with php-fpm:

.. code-block:: lighttpd

	server.modules += ("mod_fastcgi")
	fastcgi.server += ( ".php" =>-
		("localhost" => (
				"socket" => "/tmp/php-fpm.sock"
			)
		)
	)

Below is a fully functional configuration for a Symfony2 vhost:

.. code-block:: lighttpd

	$HTTP["host"] =~ "myhost\.tld" {
	server.document-root = "/home/user/www/myhost/web"

		url.rewrite-if-not-file = (
			"^/$" => "$0",
			"^(?!app_dev\.php/)[^\?]+(\?.*)?" => "app.php/$1$2",
		)
	}

The following is the dynamic rewrite which takes ``{project}.{username}.domain.tld`` and point it into ``/home/{username}/www/{project}/web``:

.. code-block:: lighttpd

	server.modules += ( "mod_evhost" )

	$HTTP["host"] =~ "\.piglet\.bjrnskov\.dk" {
		evhost.path-pattern = "/home/%4/www/%5/web"

Originally from: http://henrik.bjrnskov.dk/symfony2-lighttpd/
