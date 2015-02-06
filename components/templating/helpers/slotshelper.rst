.. index::
    single: Templating Helpers; Slots Helper

Slots Helper
============

More often than not, templates in a project share common elements, like the
well-known header and footer. Using this helper, the static HTML code can
be placed in a layout file along with "slots", which represent the dynamic
parts that will change on a page-by-page basis. These slots are then filled
in by different children template. In other words, the layout file decorates
the child template.

Displaying Slots
----------------

The slots are accessible by using the slots helper (``$view['slots']``). Use
:method:`Symfony\\Component\\Templating\\Helper\\SlotsHelper::output` to
display the content of the slot on that place:

.. code-block:: html+php

    <!-- views/layout.php -->
    <!doctype html>
    <html>
        <head>
            <title>
                <?php $view['slots']->output('title', 'Default title') ?>
            </title>
        </head>
        <body>
            <?php $view['slots']->output('_content') ?>
        </body>
    </html>

The first argument of the method is the name of the slot. The method has an
optional second argument, which is the default value to use if the slot is not
available.

The ``_content`` slot is a special slot set by the ``PhpEngine``. It contains
the content of the subtemplate.

.. caution::

    If you're using the standalone component, make sure you registered the
    :class:`Symfony\\Component\\Templating\\Helper\\SlotsHelper`::

        use Symfony\Component\Templating\Helper\SlotsHelper;

        // ...
        $templateEngine->set(new SlotsHelper());

Extending Templates
-------------------

The :method:`Symfony\\Component\\Templating\\PhpEngine::extend` method is called in the
sub-template to set its parent template. Then
:method:`$view['slots']->set() <Symfony\\Component\\Templating\\Helper\\SlotsHelper::set>`
can be used to set the content of a slot. All content which is not explicitly
set in a slot is in the ``_content`` slot.

.. code-block:: html+php

    <!-- views/page.php -->
    <?php $view->extend('layout.php') ?>

    <?php $view['slots']->set('title', $page->title) ?>

    <h1>
        <?php echo $page->title ?>
    </h1>
    <p>
        <?php echo $page->body ?>
    </p>

.. note::

    Multiple levels of inheritance is possible: a layout can extend another
    layout.

For large slots, there is also an extended syntax:

.. code-block:: html+php

    <?php $view['slots']->start('title') ?>
        Some large amount of HTML
    <?php $view['slots']->stop() ?>
