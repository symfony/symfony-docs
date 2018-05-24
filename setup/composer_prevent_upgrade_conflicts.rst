
.. index::
    double: Composer; Conflicts

How to prevent PHP conflicts during upgrades when using Composer
================================================================

Each Symfony release is guaranteed to work with a specific PHP version,
so for example when you download and install Symfony 2.8 you are
sure that it can work on PHP 5.4.16.

When you upgrade Symfony (e.g. from 2.8.25 to 2.8.26) you must be sure that
all the upgraded dependencies are still compatible with your own PHP version
running in your production server (e.g. the old PHP 5.4.16).

Achieving this is very simple: just remember to add such constraints in your
``composer.json`` file located in your project root directory (my_project_name),
so that when you will launch updates that would not be a pain. Just replace these lines::

    // my_project_name/composer.json
        "config": {
            "bin-dir": "bin"
        },
    
with these::

    // my_project_name/composer.json
        "config": {
            "bin-dir": "bin",
            "platform": {
                    "php": "5.4.16"
            }
        },
        
and enjoy your Symfony upgrades.
