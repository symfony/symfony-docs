.. index::
   single: Deployment; Deploying to Cloudways Cloud Servers

Deploying to Cloudways 
======================

Cloudways is a managed hosting provider for PHP based applications. This step by
step article describes how to install Symfony on Cloudways servers and how to
deploy Symfony applications using Git and SSH.

Step 1: Prepare your server and application
-------------------------------------------

`Sign up`_ or login to your **Cloudways account** and create a new server by
selecting PHP Stack from the **Select your Application** drop-down. Also, name
your application, server and project accordingly.

.. image:: /_images/deployment/cloudways/select-application.png
   :alt: Select PHP Stack in Drop down

Step 2: Select Cloud Provider
-----------------------------

Now select your favorite cloud provider. You can select any one from the five
providers to install Symfony.

.. image:: /_images/deployment/cloudways/select-provider.png
   :alt: Select cloud Provider

Step 3: Select Server Size & Location
-------------------------------------

In the **Server Size** field, scale the server as your requirement (**1GB**
is the minimum recommended size for Symfony applications).

.. image:: /_images/deployment/cloudways/select-server-size.png
   :alt: Select server size

Keeping the latency difference in mind, now select your server location in the
**Location** field.

.. image:: /_images/deployment/cloudways/select-server-location.png
   :alt: Select server location

To launch your server, click on the **Launch** button at the bottom right side
of the page.

.. image:: /_images/deployment/cloudways/launch-server.png
   :alt: launch server

Step 4: Server Timezone
-----------------------

Once the server is completely launched, set the server timezone, because Symfony
requires to define a valid timezone for PHP. Under **Server Management**, go to
**Settings & Packages.**

.. image:: /_images/deployment/cloudways/server-management.png
   :alt: server management tab

In **Basic** tab you can see **PHP Timezone.** Change the server timezone to the
appropriate value according to your location.

.. image:: /_images/deployment/cloudways/server-timezone-1.png
   :alt: Select server timezone

Step 5: SSH Terminal
--------------------

After changing server timezone, in **Server Management** click on **Launch SSH
Terminal** under  **Master Credentials** where you can find your login
credentials for SSH Terminal.

.. image:: /_images/deployment/cloudways/master-credentials.png
   :alt: Cloudways Master Credentials

Login to SSH with your Master Credentials.

.. image:: /_images/deployment/cloudways/symfony-7.png
   :alt: login to SSH

Since the application folder contains a ``index.php`` fie by default, remove it
from your ``public_html/`` directory executing the ``rm -Rf *`` command.

Step 6: Symfony Installation
----------------------------

Now in your ``public_html/`` directory, run the following command to install
Symfony 3 on your server:

.. code-block:: terminal

    $ composer create-project symfony/framework-standard-edition your_project_name "3.1.*"

This command will create a Symfony project in ``public_html/``. You can also
change the version number to install any other Symfony version.

Step 7: DB Information and Finishing Project Installation
---------------------------------------------------------

Now Symfony will start downloading to your server. During downloading, it will
ask for your database information.

.. image:: /_images/deployment/cloudways/db-info.png
   :alt: database information

You can find out your DB information under **Application Management** go to
**Access detail** and see MySQL Access.

.. image:: /_images/deployment/cloudways/mysql-access.png
   :alt: database information

After providing database information, you will see the following screen which
means that  Symfony has been installed successfully.

.. image:: /_images/deployment/cloudways/symfony-installed.png
   :alt: Final Symfony Installation

Step 7: Running in Browser
--------------------------

After a successful installation, in **Application Management** go to **Access
detail** and copy your Application URL.

.. image:: /_images/deployment/cloudways/mysql-access.png
   :alt: database information

Open your favorite web browser and access to the "Symfony Welcome page" using
the following URL: http://your_application_url/your_project_directory/web

This is how the Symfony Welcome page should look like:

.. image:: /_images/deployment/cloudways/symfony-home.png
   :alt: symfony homepage

Finally your Symfony project is ready for development. 

.. _`Sign up`: https://platform.cloudways.com/signup
