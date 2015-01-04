.. index::
   single: Deployment; Deploying to Heroku Cloud

Deploying to Heroku Cloud
=========================

This step by step cookbook describes how to deploy a Symfony web application to
the Heroku cloud platform. Its contents are based on `the original article`_
published by Heroku.

Setting up
----------

To setup a new Heroku website, first `signup with Heroku`_ or sign in
with your credentials. Then download and install the `Heroku Toolbelt`_ on your
local computer.

You can also check out the `getting Started with PHP on Heroku`_ guide to gain
more familiarity with the specifics of working with PHP applications on Heroku.

Preparing your Application
~~~~~~~~~~~~~~~~~~~~~~~~~~

Deploying a Symfony application to Heroku doesn't require any change in its
code, but it requires some minor tweaks to its configuration.

By default, the Symfony app will log into your application's ``app/log/``
directory. This is not ideal as Heroku uses an `ephemeral file system`_. On
Heroku, the best way to handle logging is using `Logplex`_. And the best way to
send log data to Logplex is by writing to ``STDERR`` or ``STDOUT``. Luckily,
Symfony uses the excellent Monolog library for logging. So, a new log
destination is just a change to a config file away.

Open the ``app/config/config_prod.yml`` file, locate the
``monolog/handlers/nested``  section (or create it if it doesn't exist yet) and
change the value of ``path`` from
``"%kernel.logs_dir%/%kernel.environment%.log"`` to ``"php://stderr"``:

.. code-block:: yaml

    # app/config/config_prod.yml
    monolog:
        # ...
        handlers:
            # ...
            nested:
                # ...
                path: "php://stderr"

Once the application is deployed, run ``heroku logs --tail`` to keep the
stream of logs from Heroku open in your terminal.

Creating a new Application on Heroku
------------------------------------

To create a new Heroku application that you can push to, use the CLI ``create``
command:

.. code-block:: bash

    $ heroku create

    Creating mighty-hamlet-1981 in organization heroku... done, stack is cedar
    http://mighty-hamlet-1981.herokuapp.com/ | git@heroku.com:mighty-hamlet-1981.git
    Git remote heroku added

You are now ready to deploy the application as explained in the next section.

Deploying your Application on Heroku
------------------------------------

To deploy your application to Heroku, you must first create a ``Procfile``,
which tells Heroku what command to use to launch the web server with the
correct document root. After that, you will ensure that your Symfony application
runs the ``prod`` environment, and then you'll be ready to ``git push`` to
Heroku for your first deploy!

Creating a Procfile
~~~~~~~~~~~~~~~~~~~

By default, Heroku will launch an Apache web server together with PHP to serve
applications. However, two special circumstances apply to Symfony applications:

#. The document root is in the ``web/`` directory and not in the root directory
   of the application;
#. The Composer ``bin-dir``, where vendor binaries (and thus Heroku's own boot
   scripts) are placed, is ``bin/`` , and not the default ``vendor/bin``.

.. note::

    Vendor binaries are usually installed to ``vendor/bin`` by Composer, but
    sometimes (e.g. when running a Symfony Standard Edition project!), the
    location will be different. If in doubt, you can always run
    ``composer config bin-dir`` to figure out the right location.

Create a new file called ``Procfile`` (without any extension) at the root
directory of the application and add just the following content:

.. code-block:: text

    web: bin/heroku-php-apache2 web/

If you prefer working on the command console, execute the following commands to
create the ``Procfile`` file and to add it to the repository:

.. code-block:: bash

    $ echo "web: bin/heroku-php-apache2 web/" > Procfile
    $ git add .
    $ git commit -m "Procfile for Apache and PHP"
    [master 35075db] Procfile for Apache and PHP
     1 file changed, 1 insertion(+)

Setting the ``prod`` Environment
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

During a deploy, Heroku runs ``composer install --no-dev`` to install all of the
dependencies your application requires. However, typical `post-install-commands`_
in ``composer.json``, e.g. to install assets or clear (or pre-warm) caches, run
using Symfony's ``dev`` environment by default.

This is clearly not what you want - the app runs in "production" (even if you
use it just for an experiment, or as a staging environment), and so any build
steps should use the same ``prod`` environment as well.

Thankfully, the solution to this problem is very simple: Symfony will pick up an
environment variable named ``SYMFONY_ENV`` and use that environment if nothing
else is explicitly set. As Heroku exposes all `config vars`_ as environment
variables, you can issue a single command to prepare your app for a deployment:

.. code-block:: bash

    $ heroku config:set SYMFONY_ENV=prod

Pushing to Heroku
~~~~~~~~~~~~~~~~~

Next up, it's finally time to deploy your application to Heroku. If you are
doing this for the very first time, you may see a message such as the following:

.. code-block:: bash

    The authenticity of host 'heroku.com (50.19.85.132)' can't be established.
    RSA key fingerprint is 8b:48:5e:67:0e:c9:16:47:32:f2:87:0c:1f:c8:60:ad.
    Are you sure you want to continue connecting (yes/no)?

In this case, you need to confirm by typing ``yes`` and hitting ``<Enter>`` key
- ideally after you've `verified that the RSA key fingerprint is correct`_.

Then, deploy your application executing this command:

.. code-block:: bash

    $ git push heroku master

    Initializing repository, done.
    Counting objects: 130, done.
    Delta compression using up to 4 threads.
    Compressing objects: 100% (107/107), done.
    Writing objects: 100% (130/130), 70.88 KiB | 0 bytes/s, done.
    Total 130 (delta 17), reused 0 (delta 0)

    -----> PHP app detected

    -----> Setting up runtime environment...
           - PHP 5.5.12
           - Apache 2.4.9
           - Nginx 1.4.6

    -----> Installing PHP extensions:
           - opcache (automatic; bundled, using 'ext-opcache.ini')

    -----> Installing dependencies...
           Composer version 64ac32fca9e64eb38e50abfadc6eb6f2d0470039 2014-05-24 20:57:50
           Loading composer repositories with package information
           Installing dependencies from lock file
             - ...

           Generating optimized autoload files
           Creating the "app/config/parameters.yml" file
           Clearing the cache for the dev environment with debug true
           Installing assets using the hard copy option
           Installing assets for Symfony\Bundle\FrameworkBundle into web/bundles/framework
           Installing assets for Acme\DemoBundle into web/bundles/acmedemo
           Installing assets for Sensio\Bundle\DistributionBundle into web/bundles/sensiodistribution

    -----> Building runtime environment...

    -----> Discovering process types
           Procfile declares types -> web

    -----> Compressing... done, 61.5MB

    -----> Launching... done, v3
           http://mighty-hamlet-1981.herokuapp.com/ deployed to Heroku

    To git@heroku.com:mighty-hamlet-1981.git
     * [new branch]      master -> master

And that's it! If you now open your browser, either by manually pointing
it to the URL ``heroku create`` gave you, or by using the Heroku Toolbelt, the
application will respond:

.. code-block:: bash

    $ heroku open
    Opening mighty-hamlet-1981... done

You should be seeing your Symfony application in your browser.

.. _`the original article`: https://devcenter.heroku.com/articles/getting-started-with-symfony2
.. _`signup with Heroku`: https://signup.heroku.com/signup/dc
.. _`Heroku Toolbelt`: https://devcenter.heroku.com/articles/getting-started-with-php#local-workstation-setup
.. _`getting Started with PHP on Heroku`: https://devcenter.heroku.com/articles/getting-started-with-php
.. _`ephemeral file system`: https://devcenter.heroku.com/articles/dynos#ephemeral-filesystem
.. _`Logplex`: https://devcenter.heroku.com/articles/logplex
.. _`verified that the RSA key fingerprint is correct`: https://devcenter.heroku.com/articles/git-repository-ssh-fingerprints
.. _`post-install-commands`: https://getcomposer.org/doc/articles/scripts.md
.. _`config vars`: https://devcenter.heroku.com/articles/config-vars