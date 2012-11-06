.. index::
   single: Workflow; Components

How to start a new project using Symfony2 Components
====================================================

Using Finder Component
----------------------

1. Create a new empty folder.

2. Create a new file called ``composer.json`` and paste the following into it:

   .. code-block:: json

        {
            "require": {
                "symfony/finder": "2.1.*"
            }
        }

3. Download vendor libraries and generate ``vendor/autoload.php`` file:

   .. code-block:: bash

        $ php composer.phar install

4. Write your code:

   .. code-block:: php

        <?php

        // File: src/script.php

        require_once '../vendor/autoload.php';

        use Symfony\Component\Finder\Finder;

        $finder = new Finder();
        $finder->in('../data/');

        ...

.. tip::

    If you want to use all the Symfony2 Component, then instead of adding them one by one:

        .. code-block:: json

            {
                "require": {
                    "symfony/finder": "2.1.*",
                    "symfony/dom-crawler": "2.1.*",
                    "symfony/css-selector": "2.1.*"
                    ...
                }
            }

    use:

        .. code-block:: json

            {
                "require": {
                    "symfony/symfony": "2.1.*"
                }
            }
