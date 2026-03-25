Focus Management
================

When your application has multiple focusable widgets, you need to
manage which one receives keyboard input. The ``FocusManager``
handles this.

Focus Cycling
-------------

By default, **F6** moves focus to the next focusable widget and
**Shift+F6** moves it to the previous one. This works automatically
when you register widgets with the focus manager.

Registering Focusable Widgets
-----------------------------

Add widgets to the focus manager so they participate in the focus
cycle:

.. code-block:: php

    $focus = $tui->getFocusManager();
    $focus->add($editor1);
    $focus->add($editor2);
    $focus->add($editor3);

    $tui->setFocus($editor1);

The order you add widgets determines the cycling order. Pressing
**F6** moves focus through the list, wrapping from the last widget
back to the first.

Remove a widget from the cycle with ``remove()``:

.. code-block:: php

    $focus->remove($editor2);

Setting Focus Programmatically
------------------------------

Move focus to any widget at any time:

.. code-block:: php

    $tui->setFocus($editor3);

Query the currently focused widget:

.. code-block:: php

    $focused = $tui->getFocus();

Reacting to Focus Changes
-------------------------

Register a callback that fires whenever focus changes:

.. code-block:: php

    use Symfony\Component\Tui\Event\FocusEvent;

    $focus = $tui->getFocusManager();
    $focus->onFocusChanged(function (FocusEvent $event) {
        $now = $event->getTarget();
        $previous = $event->getPrevious();
    });

Focus and Styling
-----------------

Focusable widgets automatically report a ``:focused`` state flag
when they have focus. Use this in stylesheet rules to change
appearance based on focus.

**Highlight the focused widget** with a colored border:

.. code-block:: php

    use Symfony\Component\Tui\Style\Border;
    use Symfony\Component\Tui\Style\Style;

    $stylesheet->addRule('.editor', new Style(
        border: Border::all(1, 'rounded', 'gray'),
    ));
    $stylesheet->addRule('.editor:focused', new Style(
        border: Border::all(1, 'rounded', 'cyan'),
    ));

**Style a sub-element differently when focused**, for example
changing the cursor shape:

.. code-block:: php

    use Symfony\Component\Tui\Style\Style;
    use Symfony\Component\Tui\Style\CursorShape;

    $stylesheet->addRule(
        '.editor::cursor',
        new Style(cursorShape: CursorShape::Bar),
    );

**Combine FQCN and state selectors** when you want to target all
instances of a widget type:

.. code-block:: php

    use Symfony\Component\Tui\Style\Style;
    use Symfony\Component\Tui\Widget\InputWidget;

    $stylesheet->addRule(
        InputWidget::class.':focused',
        new Style(bold: true),
    );
