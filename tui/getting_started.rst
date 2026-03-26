Getting Started
===============

This guide walks you through building your first terminal UI
application with Tui.

Creating a Tui
--------------

The ``Tui`` class is the entry point for every application. Create
one, add widgets and run the event loop::

    use Symfony\Component\Tui\Tui;
    use Symfony\Component\Tui\Widget\TextWidget;

    $tui = new Tui();
    $tui->add(new TextWidget('Hello, terminal!'));
    $tui->quitOn('ctrl+c');
    $tui->run();

``run()`` blocks until you call ``stop()``. The Tui does not
impose any default quit shortcut; ``quitOn()`` registers key
patterns that call ``stop()`` automatically.

The Tui takes ownership of the terminal: it hides the cursor,
enables raw mode and restores everything when it stops.

Building the Widget Tree
------------------------

The Tui manages a root ``ContainerWidget`` internally. Use ``add()``,
``remove()`` and ``clear()`` to build the widget tree::

    use Symfony\Component\Tui\Widget\ContainerWidget;
    use Symfony\Component\Tui\Widget\InputWidget;
    use Symfony\Component\Tui\Widget\TextWidget;

    $tui->add(new TextWidget('Enter your name:'));

    $input = new InputWidget();
    $tui->add($input);

    // Remove a widget
    $tui->remove($input);

    // Remove all widgets
    $tui->clear();

You can retrieve any widget by its id::

    $input->setId('name-input');
    $tui->add($input);

    $widget = $tui->getById('name-input');

Setting Focus
-------------

Widgets that accept keyboard input (``InputWidget``,
``EditorWidget``, ``SelectListWidget``, etc.) implement
``FocusableInterface``. Only the focused widget receives keyboard
events. Set focus with ``setFocus()``::

    $tui->setFocus($input);

When your application has multiple focusable widgets, see
:doc:`/tui/topics/focus` for how to manage focus cycling.

Reacting to Events
------------------

Widgets dispatch events when the user interacts with them. The most
common pattern is to register a callback on the widget directly::

    use Symfony\Component\Tui\Event\SubmitEvent;

    $input->onSubmit(function (SubmitEvent $event) {
        $value = $event->getValue();
        // process the submitted text
    });

    $input->onCancel(function () use ($tui) {
        $tui->stop();
    });

For more advanced event handling (global listeners, filtering by
source widget), see :doc:`/tui/topics/events`.

Styling the Root Container
--------------------------

The root container is matched by the ``:root`` pseudo-class
selector, like in CSS. Style it through a stylesheet to control
the global layout::

    use Symfony\Component\Tui\Style\Style;
    use Symfony\Component\Tui\Style\StyleSheet;
    use Symfony\Component\Tui\Style\VerticalAlign;

    $stylesheet = new StyleSheet();
    $stylesheet->addRule(':root', new Style(
        gap: 1,
        verticalAlign: VerticalAlign::Bottom,
    ));

    $tui = new Tui($stylesheet);

Pass the stylesheet to the constructor, or add it later with
``addStyleSheet()``. See :doc:`/tui/style/index` for the full
styling documentation.

Stopping the Tui
----------------

Call ``stop()`` to exit the event loop, restore the terminal and
return control to the caller::

    $tui->stop();

This is typically done inside an event callback::

    $input->onSubmit(function () use ($tui) {
        $tui->stop();
    });

After ``stop()`` returns, the terminal is back to its normal state.
You can query widget values and write to the terminal::

    $tui->run();

    // After the loop exits
    if ($input->wasSubmitted()) {
        echo 'You entered: '.$input->getValue()."\n";
    }

A Complete Example
------------------

Putting it all together::

    use Symfony\Component\Tui\Style\Style;
    use Symfony\Component\Tui\Style\StyleSheet;
    use Symfony\Component\Tui\Tui;
    use Symfony\Component\Tui\Widget\InputWidget;
    use Symfony\Component\Tui\Widget\TextWidget;

    $tui = new Tui(new StyleSheet([
        ':root' => new Style(gap: 1),
    ]));
    $tui->quitOn('ctrl+c');

    $input = new InputWidget();
    $input->setPrompt('Name: ');
    $input->onSubmit(fn () => $tui->stop());

    $tui->add(new TextWidget('Enter your name and press Enter:'));
    $tui->add($input);
    $tui->setFocus($input);

    $tui->run();

    if ($input->wasSubmitted()) {
        echo 'Hello, '.$input->getValue()."!\n";
    }

Next Steps
----------

* :doc:`/tui/widgets/index` for all available widgets.
* :doc:`/tui/style/index` for colors, borders, padding and stylesheets.
* :doc:`/tui/topics/focus` for focus cycling between multiple widgets.
* :doc:`/tui/topics/events` for advanced event handling.
* :doc:`/tui/topics/keybindings` for customizing keyboard shortcuts.
* :doc:`/tui/topics/tick_loop` for async work and the tick callback.
