.. index::
   single: Deployment; Deploying to Microsoft Azure Website Cloud

Deploying to Microsoft Azure Website Cloud
==========================================

SensioLabs and Microsoft announce their partnership during the upcoming PHP Tour
in Lyon (France). This announcement covers several strategic aspects including
the integration of Symfony into the Microsoft Azure Website Cloud platform
destined to leverage all the devops aspects from Azure Websites to the Symfony2
developers community.

The two companies will provide all the needed tutorials allowing both
communities and companies to migrate their Symfony applications to the Microsoft
Azure Cloud platform.
 
Additionally, Microsoft and SensioLabs are working on integrating Symfony into
the Azure Website gallery in an effort to simplify the deployment of Symfony
applications and provide a complete offer to satisfy various company's target
markets.

Objectives
----------

Simply put, this cookbook illustrates how to deploy a very simple **Symfony2
Standard Edition** web application. You can easily download a copy of the
Symfony Standard Edition on the `downloads`_ page. Also, this cookbook assumes
you're already familiar with the basic worfklows of managing code with Git.

This step by step cookbook describes how to deploy a small Symfony2 web
application to the Microsoft Azure Website cloud platform. It will explain how
to setup a new Azure website including configuring the right PHP version and
global environment variables. Finally, the document will demonstrate how to
leverage the Git source code management system and Composer dependencies manager
to deploy your Symfony application to the cloud.

Setting up the Azure Website
----------------------------

To setup a new Microsoft Azure Website, first `signup to Azure`_ or signin with
your credentials. Once connected to your `Azure Portal`_ interface, scroll down
to the bottom and select the **New** panel. In this panel, click on **Web Site**
and choose **Custom Create** as shown in the following screenshot.

.. image:: /images/cookbook/deployment/azure-website/step-01.png
   :alt: Create a new custom Azure Website

You will be prompted to fill in some required information like the website url,
the hosting plan, the datacenter region, the database storage engine and the
version control publishing method.

.. image:: /images/cookbook/deployment/azure-website/step-02.png
   :alt: Setup the Azure Website

Fill the Azure Website url you would like to use for your Symfony application,
then pick **Create new web hosting plan** in the region you want. By default, a
*free 20 MB SQL database* is selected in the database dropdown list. In this
tutorial, the Symfony application will be connected to a MySQL database. Pick
the **Create a new MySQL database** option in the dropdown list. You can keep
the **DefaultConnection** string name. Finally, check the box **Publish from
source control** to enable a Git repository and go to the next step.

In this step, you will be prompted to setup your MySQL database storage with a
database name and a region. The MySQL database storage is provided by Microsoft
in partnership with ClearDB. Designate the name of the database you want to
create and pick the same region you've selected for the hosting plan
configuration in the previous step.

.. image:: /images/cookbook/deployment/azure-website/step-03.png
   :alt: Setup the MySQL database

Afterwards, agree to the terms and conditions, and click on the right arrow to
go to the next step to configure your Git repository. In this third step, select
a **local Git repository** item and click on the right arrow to configure your
Azure Website credentials.

.. image:: /images/cookbook/deployment/azure-website/step-04.png
   :alt: Setup a local Git repository

This is the final step to setup the Azure Website. Select a username and a
secured password, they will become essential identifiers to connect to the FTP
server and also to push your application code to the Git repository.

.. image:: /images/cookbook/deployment/azure-website/step-05.png
   :alt: Configure Azure Website credentials

Your Azure Website is now up and running. You can check it by browsing the Azure
Website url configured in the first step. You should see the following display
in your web browser:

.. image:: /images/cookbook/deployment/azure-website/step-06.png
   :alt: Azure Website is running

The Microsoft Azure portal also provides a complete control panel for the Azure
Website.

.. image:: /images/cookbook/deployment/azure-website/step-07.png
   :alt: Azure Website Control Panel

Though your Azure Website is now technically ready to receive the Symfony
application code, Symfony requests some additional technical requirements to be
fullfilled in order to make it run on the cloud platform. The next section of
this cookbook describes how to properly configure PHP on Azure to meet the
Symfony requirements.

Meeting the Symfony Technical Requirements
------------------------------------------

This section of the tutorial details how to configure the correct version of PHP
to run Symfony. It also includes how to enable some mandatory PHP extensions and
how to properly configure PHP for a production environment.

Configuring the Latest PHP Runtime
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Even though Symfony requires at least PHP 5.3.3 to run, it's always recommended
to rely on the most recent PHP runtime version whenever possible. The PHP 5.3
version is no longer supported by the PHP core team. To update your PHP version
on Azure, go to the **Configure** tab of the control panel.

.. image:: /images/cookbook/deployment/azure-website/step-08.png
   :alt: Enabling the most recent PHP runtime from Azure Website Control Panel

Click on the **Save** button in the bottom bar to save your changes and restart
the web server to run the new version of PHP.

.. note::

    Choosing a more recent PHP version can greatly improve runtime performance.
    PHP 5.5 ships with a new built-in PHP accelerator called ZendOptimizer+ that
    replaces the APC tool. On an Azure Website, ZendOptimizer+ is already
    enabled and there is no need to install and setup any APC server.

    The following screenshot shows the output of a :phpfunction:`phpinfo` script
    run from the Azure Website to verify that PHP 5.5 is running with
    ZendOptimizer+ enabled.

    .. image:: /images/cookbook/deployment/azure-website/step-09.png
       :alt: ZendOptimizer+ Configuration

The next section describes how to change some PHP internal settings.

Tweaking php.ini Configuration Settings
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Microsoft Azure allows you to override the ``php.ini`` global configuration
settings by creating a custom ``.user.ini`` file under the project root
directory (``site/wwwroot``).

.. code-block:: ini

    ; .user.ini
    expose_php = Off
    memory_limit = 256M
    upload_max_filesize = 10M

None of these settings is required to be overriden on Azure Website. The default
PHP configuration is already pretty good, so this is just an example to show
that you can easily tweak PHP internal settings by uploading your custom
``.ini`` file.

You can either manually create this file on your Azure Website FTP server under
the ``site/wwwroot`` directory or deploy it with Git. You can get your FTP
server credentials from the Azure Website Control panel under the **Dashboard**
tab on the right sidebar. If you want to use Git, simply put your ``.user.ini``
file at the root of your local repository and push your commits to your Azure
Website repository.

.. note::

    This cookbook has a section dedicated to explaining how to configure your
    Azure Website Git repository and how to push the commits to be deployed. See
    `Deploying from Git`_ section. You can also learn more about configuring PHP
    internal settings on the official page `PHP MSDN documentation`_.

The next section explains how to enable the PHP ``intl`` extension that Symfony
requires to run correctly.

Enabling the PHP Intl Extension
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This is the tricky part of the guide! At the time of writing this cookbook,
Microsoft Azure Website provided the ``intl`` extension, but it's not enabled in
the main PHP configuration. However, Azure Website allows you to upload and
enable custom PHP extensions. To enable the ``intl`` extension, there is no need
to upload any DLL files as the ``php_intl.dll`` file already exists on Azure. In
fact, this file just has to be moved into the custom website extension
directory.

.. note::

    The Microsoft Azure team is currently working on enabling the ``intl`` PHP
    extension by default. In the near future, the following steps will become
    obsolete to enable the ``intl`` extension.

To get the ``php_intl.dll`` file under your ``site/wwwroot`` directory, simply
access the online **Kudu** tool by browsing the following url:

.. code-block:: text

    https://[your-website-name].scm.azurewebsites.net

**Kudu** is a set of tools to manage your application. It comes with a file
explorer, a command line prompt, a log stream and configuration settings summary
page. Of course, this section is forbidden if you're not authorized entry with
your main Azure Website account.

.. image:: /images/cookbook/deployment/azure-website/step-10.png
   :alt: The Kudu Panel

From the Kudu front page, click on the **Debug Console** navigation item in the
main menu and choose **CMD**. You should access the **Debug Console** page that
shows a file explorer and a console prompt below.

In the console prompt, type the following three commands to copy the original
``php_intl.dll`` extension file into a custom website ``ext/`` directory. This
new directory must be created under the main directory ``site/wwwroot``.

.. code-block:: bash

    $ cd site\wwwroot
    $ mkdir ext
    $ copy "D:\Program Files (x86)\PHP\v5.5\ext\php_intl.dll" ext

You should get the same output as the following screenshot:

.. image:: /images/cookbook/deployment/azure-website/step-11.png
   :alt: Executing commands in the online Kudu Console prompt

To complete the activation of the ``php_intl.dll`` extension, you must tell
Azure Website to load it from the newly created ``ext`` directory. This can be
easily done by registering a global ``PHP_EXTENSIONS`` environment variable from
the **Configure** tab of the main Azure Website Control panel.

In the **app settings** section, register the ``PHP_EXTENSIONS`` environment
variable with the value ``ext\php_intl.dll`` as shown in the screenshot below:

.. image:: /images/cookbook/deployment/azure-website/step-12.png
   :alt: Registering custom PHP extensions

Hit "save" to confirm your changes and restart the web server. The PHP ``Intl``
extension should now be available in your web server environment. The following
screenshot of a :phpfunction:`phpinfo` page verifies the ``intl`` extension is
properly enabled:

.. image:: /images/cookbook/deployment/azure-website/step-13.png
   :alt: Intl extension is enabled

The PHP environment setup is now complete. The two remaining sections of this
tutorial explain how to configure the Git repository to push the code to
production and how to install and configure the Symfony application after it's
deployed.

Deploying from Git
~~~~~~~~~~~~~~~~~~

First, make sure Git is correctly installed on your local machine using the
following command in your terminal:

.. code-block:: bash

    $ git --version

.. note::

    Get your Git from the `git-scm.com`_ website and follow the instructions
    to install and configure it on your local machine.

In the Azure Website Control panel, browse the **Deployment** tab to get the
Git repository url where to push the code.

.. image:: /images/cookbook/deployment/azure-website/step-14.png
   :alt: Git deployment panel

Then, go to your local Symfony application root directory and connect your local
Git repository to the Azure Website upstream. If your Symfony application is not
yet under Git, you must first create a Git repository in your Symfony
application directory with the ``git init`` command and commit to it with the
``git commit`` command.

Also, make sure your Symfony repository owns a ``.gitignore`` file at its root
directory with at least the following content:

.. code-block:: text

    /app/bootstrap.php.cache
    /app/cache/*
    /app/config/parameters.yml
    /app/logs/*
    !app/cache/.gitkeep
    !app/logs/.gitkeep
    /app/SymfonyRequirements.php
    /build/
    /vendor/
    /bin/
    /composer.phar
    /web/app_dev.php
    /web/bundles/
    /web/config.php 

The ``.gitignore`` file asks Git not to track any of the files and directories
that match the patterns. This means these files won't be deployed to the Azure
Website.

To deploy the code to the Azure Website Git repository, create a remote
dependency of the local Git repository by running the following commands into
your local terminal:

.. code-block:: bash

    $ git remote add azure https://<username>@<your-website-name>.scm.azurewebsites.net:443/<your-website-name>.git
    $ git push azure master

Don't forget to replace the values enclosed by ``<`` and ``>`` with your custom
settings displayed in the **Deployment** tab of your Azure Website panel. The
``git remote`` command connects the Azure Website remote Git repository and
assigns an alias to it with the name ``azure``. The second ``git push`` command
pushes all your commits to the remote ``master`` branch of your remote ``azure``
Git repository.

The deployment with Git should produce an output similar to the screenshot
below:

.. image:: /images/cookbook/deployment/azure-website/step-15.png
   :alt: Deploying files to the Git Azure Website repository

The code of the Symfony application has now been deployed to the Azure Website
which you can browse from the file explorer of the Kudu application. You should
see the ``app/``, ``src/`` and ``web/`` directories under your ``site/wwwroot``
directory on the Azure Website filesystem.

Configure the Symfony Application
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The last step to make the Symfony application work online is to setup its
configuration and install the third party dependencies it requires that aren't
tracked by Git. Switch back to the online **Console** of the Kudu application
and execute the following commands in it:

.. code-block:: bash

    $ cd site\wwwroot
    $ curl -sS https://getcomposer.org/installer | php
    $ php -d extension=php_intl.dll composer.phar install

The ``curl`` command retrieves and downloads the Composer command line tool and
installs it at the root of the ``site/wwwroot`` directory. Then, run the
``install`` command of Composer with the ``php`` executable to make it download
and install all necessary third-party libraries.

Execution may take a while depending on the number of third-party dependencies
you've configured into your ``composer.json`` file.

.. note::

    The ``-d`` switch allows to quickly override/add any ``php.ini`` settings
    to the PHP runtime. So in this command, we are forcing PHP to use the
    ``intl`` extension as it is not enabled by default in Azure Website at the
    moment. Soon, this ``-d`` option will no longer be needed since
    Microsoft will enable the ``intl`` extension by default.

At the end of the ``composer install`` command, you will be prompted to fill in
the values of some Symfony settings like database credentials, locale, mailer
credentials, CSRF token protection, etc. These parameters come from the
``app/config/parameters.yml.dist`` file.

.. image:: /images/cookbook/deployment/azure-website/step-16.png
   :alt: Configuring Symfony global parameters

The most important thing in this cookbook is to correctly setup your database
settings. You can get your MySQL database settings on the right sidebar of the
**Azure Website Dashboard** panel. Simply click on the
**View Connection Strings** link to make them appear in a pop-in.

.. image:: /images/cookbook/deployment/azure-website/step-17.png
   :alt: MySQL database settings

The displayed MySQL database settings should be something similar to the code
below. Of course, each value depends on what you've already configured.

.. code-block:: text

    Database=mysymfony2MySQL;Data Source=eu-cdbr-azure-north-c.cloudapp.net;User Id=bff2481a5b6074;Password=bdf50b42

Switch back to the console and answer the prompted questions and provide the
following answers. Don't forget to adapt the values below with your real values
from the MySQL connection string.

.. code-block:: text

    database_driver: pdo_mysql
    database_host: u-cdbr-azure-north-c.cloudapp.net
    database_port: null
    database_name: mysymfony2MySQL
    database_user: bff2481a5b6074
    database_password: bdf50b42
    // ...

Don't forget to answer all the questions. It's important to set a unique random
string to the ``secret`` variable. Also, for the mailer configuration, Azure
Website doesn't provide a built-in mailer service. You should consider
configuring the host-name and credentials of your third-party mailing service if
your application needs to send emails.

.. image:: /images/cookbook/deployment/azure-website/step-18.png
   :alt: Configuring Symfony

Your Symfony application is now configured and should be almost operational. The
final step is to build the database schema. This can easily be done with the
command line interface if you're using Doctrine. In the online **Console** tool
of the Kudu application, run the following command to mount the tables into your
MySQL database.

.. code-block:: bash

    $ php app/console doctrine:schema:update --force

This command builds the tables and indexes in your MySQL database. If your
Symfony application is more complex than a basic Symfony Standard Edition, you
may have additional commands to execute for setup.

Make sure that your application is running by browsing the ``app.php`` front
controller with your web browser and the following url:

.. code-block:: bash

    http://<your-website-name>.azurewebsites.net/web/app.php

If Symfony is correctly installed, you should see the front page of your Symfony
application showing.

Configure the Web Server
~~~~~~~~~~~~~~~~~~~~~~~~

At this point, the Symfony application has been deployed and works perfectly on
the Azure Website. However the ``web`` folder is still part of the url, which
does not conform to good practice and is not recommended. The final step in this
tutorial is to configure the web server to make the url point to the ``web``
folder, and avoid others browsing or tampering with your application code. To do
so, you need to create and deploy (see previous manipulation with Git) the
following ``web.config`` file:

The ``web.config`` file must be located at the root of your project next to the
``composer.json`` file, ``web/`` directory and other directories. This file is
the Microsoft IIS Server equivalent of the well-known ``.htaccess`` file from
Apache. For a Symfony application, configure this file with the following
content:

.. code-block:: xml

    <!-- web.config -->
    <?xml version="1.0" encoding="UTF-8"?>
    <configuration>
      <system.webServer>
        <rewrite>
          <rules>
            <clear />
            <rule name="BlockAccessToPublic" patternSyntax="Wildcard" stopProcessing="true">
              <match url="*" />
              <conditions logicalGrouping="MatchAll" trackAllCaptures="false">
                <add input="{URL}" pattern="/web/*" />
              </conditions>
              <action type="CustomResponse" statusCode="403" statusReason="Forbidden: Access is denied." statusDescription="You do not have permission to view this directory or page using the credentials that you supplied." />
            </rule>
            <rule name="RewriteAssetsToPublic" stopProcessing="true">
              <match url="^(.*)(\.css|\.js|\.jpg|\.png|\.gif)$" />
              <conditions logicalGrouping="MatchAll" trackAllCaptures="false">
              </conditions>
              <action type="Rewrite" url="web/{R:0}" />
            </rule>
            <rule name="RewriteRequestsToPublic" stopProcessing="true">
              <match url="^(.*)$" />
              <conditions logicalGrouping="MatchAll" trackAllCaptures="false">
              </conditions>
              <action type="Rewrite" url="web/app.php/{R:0}" />
            </rule>
          </rules>
        </rewrite>
      </system.webServer>
    </configuration>

As you can see, the latest rule ``RewriteRequestsToPublic`` is responsible for
rewriting any urls to the ``web/app.php`` front controller which allows you to
skip the ``web/`` folder from the url pattern. The first rule called
``BlockAccessToPublic`` matches all url patterns that contain the ``web/``
folder and serve a ``403 Forbidden`` HTTP response instead. This example is
based on Benjamin Eberlei's sample you can find on Github in the
`SymfonyAzureEdition`_ bundle.

Deploy this file under the ``site/wwwroot`` directory of the Azure Website and
browse your application urls without the ``web/app.php`` segment in the urls.

Conclusion
----------

This cookbook explained how to deploy a Symfony application to the Microsoft
Azure Website Cloud platform. It also showed that Symfony can be easily
configured and executed on a Microsoft IIS web server. The process remains
simple and easy to implement. Microsoft continues working to reduce the number
of necessary steps so that deployment becomes even easier.

.. _`downloads`: http://symfony.com/download/
.. _`signup to Azure`: https://signup.live.com/signup.aspx
.. _`Azure Portal`: https://manage.windowsazure.com
.. _`PHP MSDN documentation`: http://blogs.msdn.com/b/silverlining/archive/2012/07/10/configuring-php-in-windows-azure-websites-with-user-ini-files.aspx
.. _`git-scm.com`: http://git-scm.com/download
.. _`SymfonyAzureEdition`: https://github.com/beberlei/symfony-azure-edition/
