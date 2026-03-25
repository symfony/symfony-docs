SelectListWidget
================

``SelectListWidget`` displays a scrollable list of items with keyboard
navigation. The user can move through the list and confirm a selection.

When to Use
-----------

Use ``SelectListWidget`` when the user must pick one item from a list:
a file picker, a command palette result list, a model selector, etc.
For settings that cycle through predefined values, consider
:doc:`settings_list` instead.

Basic Usage
-----------

.. code-block:: php

    use Symfony\Component\Tui\Widget\SelectListWidget;

    $items = [
        ['value' => 'apple', 'label' => 'Apple'],
        ['value' => 'banana', 'label' => 'Banana'],
        ['value' => 'cherry', 'label' => 'Cherry'],
    ];

    $list = new SelectListWidget($items);
    $tui->add($list);
    $tui->setFocus($list);

Each item is an associative array with at least ``value`` and ``label``
keys. An optional ``description`` key adds secondary text next to the
selected item.

Visible Count
-------------

Control how many items are visible at once with the ``maxVisible``
parameter (default ``5``)::

    $list = new SelectListWidget($items, maxVisible: 10);

A scroll indicator (e.g. ``(3/12)``) appears when the list is longer
than the visible window.

Filtering
---------

Call ``setFilter()`` to narrow the list to items whose value starts
with the given string. This is useful when combining a
``SelectListWidget`` with an :doc:`input` for fuzzy search::

    $list->setFilter('ban'); // shows only "Banana"

Updating Items
--------------

Replace the full item list at runtime with ``setItems()``::

    $list->setItems($newItems);

This resets the selection index and any active filter.

Events
------

* ``SelectEvent``: Fired when the user confirms a selection with
  **Enter**::

      use Symfony\Component\Tui\Event\SelectEvent;

      $list->onSelect(function (SelectEvent $event) {
          $value = $event->getValue();
      });

* ``CancelEvent``: Fired when the user presses **Escape** or
  **Ctrl+C**::

      $list->onCancel(function () {
          // close the list
      });

* ``SelectionChangeEvent``: Fired whenever the highlighted item
  changes (arrow keys, scroll, click)::

      use Symfony\Component\Tui\Event\SelectionChangeEvent;

      $list->onSelectionChange(function (SelectionChangeEvent $event) {
          $highlighted = $event->getValue();
      });

Default Keybindings
-------------------

==========================  ==================================
Key                         Action
==========================  ==================================
Up                          Move selection up (wraps)
Down                        Move selection down (wraps)
Page Up, Left               Page up
Page Down, Right             Page down
Enter                       Confirm selection
Escape, Ctrl+C              Cancel
==========================  ==================================
