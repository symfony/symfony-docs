Managing Vendor Libraries with bin/vendors and deps
---------------------------------------------------

Every Symfony project uses a group of third-party "vendor" libraries. One
way or another the goal is to download these files into your ``vendor/``
directory and, ideally, to give you some sane way to manage the exact version
you need for each.

By default, these libraries are downloaded by running a ``php bin/vendors install``
"downloader" script. This script reads from the ``deps`` file at the root
of your project. This is an ini-formatted script, which holds a list of each
of the external libraries you need, the directory each should be downloaded to,
and (optionally) the version to be downloaded. The ``bin/vendors`` script
uses ``git`` to downloaded these, solely because these external libraries
themselves tend to be stored via git. The ``bin/vendors`` script also reads
the ``deps.lock`` file, which allows you to pin each library to an exact
git commit hash.

It's important to realize that these vendor libraries are *not* actually part
of *your* repository. Instead, they're simply un-tracked files that are downloaded
into the ``vendor/`` directory by the ``bin/vendors`` script. But since all
the information needed to download these files is saved in ``deps`` and ``deps.lock``
(which *are* stored) in our repository), any other developer can use our
project, run ``php bin/vendors install``, and download the exact same set
of vendor libraries. This means that you're controlling exactly what each
vendor library looks like, without needing to actually commit them to *your*
repository.

So, whenever a developer uses your project, he/she should run the ``php bin/vendors install``
script to ensure that all of the needed vendor libraries are downloaded.

.. sidebar:: Upgrading Symfony

    Since Symfony is just a group of third-party libraries and third-party
    libraries are entirely controlled through ``deps`` and ``deps.lock``,
    upgrading Symfony means simply upgrading each of these files to match
    their state in the latest Symfony Standard Edition.

    Of course, if you've added new entries to ``deps`` or ``deps.lock``, be sure
    to replace only the original parts (i.e. be sure not to also delete any of
    your custom entries).

.. caution::

    There is also a ``php bin/vendors update`` command, but this has nothing
    to do with upgrading your project and you will normally not need to use
    it. This command is used to freeze the versions of all of your vendor libraries
    by updating them to the version specified in ``deps`` and recording it
    into the ``deps.lock`` file.