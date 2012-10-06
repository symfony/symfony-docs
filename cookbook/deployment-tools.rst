.. index::
   single: Deployment Tools

How to deploy a symfony2 application
====================================

There are several ways you can deploy a symfony2 application:

* One way is moving the files manually or via ftp if you don't use versioning
  (e.g. git);

* If you use versioning you could still move things manually cloning or fetching
  your repository from the final server folder location. However it is advised
  you make use of better tools especially in the case where you have access
  capabilities such as enough permissions in a virtual private server or similar system;

* Some projects are really large so they make use of more established tools for
  deploying not only the files but really deploy a OS or package distribution
  containing the sf2 project inside;

* Another important thing to keep in mind is the handling of dependencies.
  One can use composer to fetch dependencies or include them all together with the
  repository. Some tools would handle this for you and even perhaps avoid fetching
  dependencies when a simple copy would do;

* Do remember that deployment process includes all the setup and configuring, warming up caches,
  cleaning cache, all configuration environment required, setting of permissions, run of
  cron jobs, etc. All these before, during and after the deployment should be kept in
  mind to properly deploy a working symfony2 application.

Deployment of large applications require care. The use of staging, testing, QA,
continuous integration, database migrations and capability to roll back in case of failure
is strongly advised. There are simple and more complex tools and one can make
the deployment as easy or sophisticated. Here we present only a few popular on the map:

The Tools
---------

`Capifony`_:

    This tool is a deployment recipe on top of capistrano for symfony2 project

`Magallanes`_:

    This tool is probably the one top php deployment tool capistrano-like for deploying any kind of php project

`sf2debpkg`_:

    This tool helps you build a native debian package for your symfony project

Bundles:

    There are `listings`_ of bundles for deployment you can search and use

Basic scripting:

    You can of course use shell, `ant`_, or other build tool to script the deploying of your project

Deployment services:

    Some services require a single file in project's git repository like `pagodabox`_ to handle all deployment


.. tip::

    Consult your symfony community at IRC channel #symfony2 for more fresh ideas or common problems.

.. _`Capifony`: https://capifony.org/
.. _`sf2debpkg`: https://github.com/liip/sf2debpkg
.. _`ant`: http://blog.sznapka.pl/deploying-symfony2-applications-with-ant
.. _`pagodabox`: https://github.com/jmather/pagoda-symfony-sonata-distribution/blob/master/Boxfile
.. _`Magallanes`: https://github.com/andres-montanez/Magallanes
.. _`listings`: http://knpbundles.com/search?q=deploy