Symfony2 for symfony 1 users
============================


Create a new project
--------------------

In a symfony 1 environment, to create a new project, you used the global "symfony" executable, or the one contained in the data/bin folder of the symfony1 source:

.. code-block::

	svn checkout http://svn.symfony-project.com/branches/1.4/ symfony1
    mkdir mynewproject
    cd mynewproject
    php ../symfony1/data/bin/symfony generate:project mynewproject

In Symfony2, to create a new project you need to use the symfony bootstrapper:

.. code-block::

	git clone git://github.com/symfony/symfony-bootstrapper.git
    mkdir mynewproject
    cd mynewproject
	php ../symfony-bootstrapper/symfony.phar init --name=mynewproject

and you will have to link all external libraries yourself:

.. code-block::

    ln -s ../../symfony-bootstrapper/src/vendor src/vendor



Use the console
---------------

In symfony 1, the console was directly in the base directory and was called ```symfony```:

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