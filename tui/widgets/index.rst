Widgets
=======

Widgets are the building blocks of a Tui application. Each widget is
responsible for rendering a piece of the terminal UI and, optionally,
handling user input.

All widgets extend ``AbstractWidget``, which provides common features
such as style classes, state flags, event listeners and invalidation.
Widgets that accept keyboard input implement ``FocusableInterface`` so
the focus manager can route key events to them.

.. toctree::
    :maxdepth: 1

    basics
    text
    container
    input
    editor
    select_list
    tabs
    settings_list
    loader
    cancellable_loader
    progress_bar
    markdown
