Symfony2 for symfony 1 users
============================


Create a new project
--------------------

In a symfony 1 environment, to create a new project, you use the global "symfony" executable, or the one contained in the data/bin folder of the symfony1 source:

.. code-block::

	svn checkout http://svn.symfony-project.com/branches/1.4/ symfony1
    mkdir mynewproject
    cd mynewproject
    php ../symfony1/data/bin/symfony generate:project mynewproject

In Symfony2, to create a new project you need to install the symfony bootstrapper first:

.. code-block::

	git clone git://github.com/symfony/symfony-bootstrapper.git
	cd symfony-bootstraper/src
	sh ../bin/install_vendors.sh
	cd ../../

And then use it to create your project. Note that the default format for configuration files is now ```xml```, so if you want to keep the ```yml``` format to which you are used to in symfony 1, you need to specifiy it:

.. code-block::

    mkdir mynewproject
    cd mynewproject
	php ../symfony-bootstrapper/symfony.phar init --format=yml --name=mynewproject

Finally you now have multiple external libraries to include in your project. To avoid downloading them for every new project, you can use a symbolic link:

.. code-block::

    ln -s ../../symfony-bootstrapper/src/vendor src/vendor



Use the console
---------------

In symfony 1, the console is directly in the base directory and is called ```symfony```:

.. code-block::

	php symfony
	
In Symfony2, the console is now in the app sub-directory and is called ```console```:

.. code-block::

	php app/console
	
	
Applications
------------

In a symfony 1 project, it is common to have several applications: one for the
frontend and one for the backend for instance.

In a Symfony2 project, you only need to create one application (a blog
application, an intranet application, ...). Most of the time, if you want to
create a second application, you'd better create another project and share
some bundles between them.

And if you need to separate the frontend and the backend features of some
bundles, create sub-namespaces for controllers, sub-directories for templates,
different semantic configurations, separate routing configurations, and so on.

.. tip::

    Read the definition of a :term:`Project`, an :term:`Application`, and a
    :term:`Bundle` in the glossary.



Bundles
-------

In a symfony 1 project, you usually have multiple modules inside your application. A module is a coherent set of controllers and templates.

In a Symfony2 project, you replace the concept of modules with Bundles.


Plugins
-------

In a symfony 1 project, you usually download a lot of useful plugins to reuse the many functionalities developed by the community.

In a Symfony2 project, you download Bundles. There are not many differences between the "module" Bundles and the "plugin" Bundles, except that a "plugin" Bundle usually contains multiple "module" Bundles using the "category namespace".
