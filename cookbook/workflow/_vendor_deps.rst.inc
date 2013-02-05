Managing Vendor Libraries with composer.json
--------------------------------------------

How does it work?
~~~~~~~~~~~~~~~~~

Every Symfony project uses a group of third-party "vendor" libraries. One
way or another the goal is to download these files into your ``vendor/``
directory and, ideally, to give you some sane way to manage the exact version
you need for each.

By default, these libraries are downloaded by running a ``php composer.phar install``
"downloader" binary. This ``composer.phar`` file is from a library called
`Composer`_ and you can read more about installing it in the :ref:`Installation<installation-updating-vendors>`
chapter.

The ``composer.phar`` file reads from the ``composer.json`` file at the root
of your project. This is an JSON-formatted file, which holds a list of each
of the external packages you need, the version to be downloaded and more.
The ``composer.phar`` file also reads from a ``composer.lock`` file, which
allows you to pin each library to an **exact** version. In fact, if a ``composer.lock``
file exists, the versions inside will override those in ``composer.json``.
To upgrade your libraries to new versions, run ``php composer.phar update``.

.. tip::

    If you want to add a new package to your application, modify the ``composer.json``
    file:

    .. code-block:: json

        {
            "require": {
                ...
                "doctrine/doctrine-fixtures-bundle": "@dev"
            }
        }

    and then execute the ``update`` command for this specific package, i.e.:

    .. code-block:: bash

        $ php composer.phar update doctrine/doctrine-fixtures-bundle

    You can also combine both steps into a single command:

    .. code-block:: bash

        $ php composer.phar require doctrine/doctrine-fixtures-bundle:@dev

To learn more about Composer, see `GetComposer.org`_:

It's important to realize that these vendor libraries are *not* actually part
of *your* repository. Instead, they're simply un-tracked files that are downloaded
into the ``vendor/``. But since all the information needed to download these
files is saved in ``composer.json`` and ``composer.lock`` (which *are* stored
in the repository), any other developer can use the project, run ``php composer.phar install``,
and download the exact same set of vendor libraries. This means that you're
controlling exactly what each vendor library looks like, without needing to
actually commit them to *your* repository.

So, whenever a developer uses your project, he/she should run the ``php composer.phar install``
script to ensure that all of the needed vendor libraries are downloaded.

.. sidebar:: Upgrading Symfony

    Since Symfony is just a group of third-party libraries and third-party
    libraries are entirely controlled through ``composer.json`` and ``composer.lock``,
    upgrading Symfony means simply upgrading each of these files to match
    their state in the latest Symfony Standard Edition.

    Of course, if you've added new entries to ``composer.json``, be sure
    to replace only the original parts (i.e. be sure not to also delete any of
    your custom entries).

.. _Composer: http://getcomposer.org/
.. _GetComposer.org: http://getcomposer.org/
