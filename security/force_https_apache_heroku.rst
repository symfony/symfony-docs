How to Force HTTPS ON HEROKU SERVER (APACHE) 
============================================

Heroku sets his own ORIGINAL ORIGINAL head of traffic, 

your HTACCESS file must be configured as below::

	<IfModule mod_rewrite.c>
		#SSL with Heroku # ...
		RewriteEngine On
		RewriteCond %{HTTP:X-Forwarded-Proto} !https
		RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

		#Symfony # ...
		RewriteCond %{REQUEST_FILENAME} !-f
		RewriteRule ^(.*)$ index.php [QSA,L]
	</IfModule>
