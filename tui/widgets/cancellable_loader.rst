CancellableLoaderWidget
=======================

``CancellableLoaderWidget`` extends :doc:`/tui/widgets/loader` with focus and
keyboard support so the user can cancel the operation by pressing
**Escape** or **Ctrl+C**.

When to Use
-----------

Use ``CancellableLoaderWidget`` for long-running operations that the
user should be able to abort: network requests, file indexing, AI
inference, etc. If the operation cannot be cancelled, use
:doc:`/tui/widgets/loader` instead.

Basic Usage
-----------

.. code-block:: php

    use Symfony\Component\Tui\Widget\CancellableLoaderWidget;

    $loader = new CancellableLoaderWidget('Generating response...');
    $tui->add($loader);
    $tui->setFocus($loader);

Because ``CancellableLoaderWidget`` implements ``FocusableInterface``,
it must receive focus to capture keyboard input.

Handling Cancellation
---------------------

Listen for the ``CancelEvent`` to react when the user cancels::

    use Symfony\Component\Tui\Event\CancelEvent;

    $loader->onCancel(function (CancelEvent $event) {
        // abort the background operation
        $loader->stop();
    });

You can also check the cancellation state at any time::

    if ($loader->isCancelled()) {
        // handle cancellation
    }

Call ``reset()`` to clear the cancelled flag before reusing the
widget::

    $loader->reset();
    $loader->start();

Inherited Features
------------------

``CancellableLoaderWidget`` inherits all features from
:doc:`/tui/widgets/loader`: spinner styles, animation speed, message updates and
the finished indicator.

Default Keybindings
-------------------

====================  ====================
Key                   Action
====================  ====================
Escape, Ctrl+C        Cancel
====================  ====================
