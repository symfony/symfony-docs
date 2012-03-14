.. index::
   single: Templating

The Templating Component
=====================

    The Templating Component is a PHP template engine providing template
    inheritence and object-oriented helper functions.

Installation
------------

You can install the component in many different ways:

* Use the official Git repository (https://github.com/symfony/Templating);
* Install it via PEAR ( `pear.symfony.com/Templating`);
* Install it via Composer (`symfony/templating` on Packagist).

Usage
-----

The :class:`Symfony\\Component\\Templating\\PhpEngine` class is the entry point
of the component. It needs a template name parser
(:class:`Symfony\\Component\\templating\\TemplateNameParserInterface`) to
convert a template name to a template reference and template loader
(:class:`Symfony\\Component\\templating\\Loader\\LoaderInterface`) to find the
template associated to a reference.

    use Symfony\Component\Templating\PhpEngine;
    use Symfony\Component\Templating\TemplateNameParser;
    use Symfony\Component\Templating\Loader\FilesystemLoader;

    $loader = new FilesystemLoader(__DIR__ . '/views/%name%');

    $view = new PhpEngine(new TemplateNameParser(), $loader);

    echo $view->render('hello.php', array('firstname' => 'Fabien'));

The :method:`Symfony\\Component\\Templating\\PhpEngine::render` method executes
the file `views/hello.php` and returns the output text.

.. code-block::php

    <!-- views/hello.php -->
    Hello, <?php echo $firstname ?>!


Template inheritence with slots
-------------------------------

The template inheritence is designed to share layouts with many templates.

.. code-block::php

    <!-- views/layout.php -->
    <html>
        <head>
            <title><?php $view['slots']->output('title', 'Default title') ?></title>
        </head>
        <body>
            <?php $view['slots']->output('_content') ?>
        </body>
    </html>

The :method:`Symfony\\Templating\\PhpEngine::extend` method is called in the
sub-template to set its parent template.

.. code-block::php

    <!-- views/page.php -->
    <?php $view->extend('layout.php') ?>

    <?php $view['slots']->set('title', $page->title) ?>

    <h1>
        <?php echo $page->title ?>
    </h1>
    <p>
        <?php echo $page->body ?>
    </p>

To use template inheritence, the :class:`Symfony\\Templating\\Helper\\SlotsHelper`
helper must be registered.

    use Symfony\Templating\Helper\SlotsHelper;

    $view->set(new SlotsHelper());

    // Retrieve $page object

    echo $view->render('page.php', array('page' => $page));

.. note::

    Multiple levels of inheritence are possible: a layout can extend an other
    layout.

