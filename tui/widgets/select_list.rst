SelectListWidget
================

``SelectListWidget`` displays a scrollable list of items with keyboard
navigation. The user can move through the list and confirm a selection
of one item or, in multiselect mode, of several items.

When to Use
-----------

Use ``SelectListWidget`` when the user must pick one item from a list:
a file picker, a command palette result list, a model selector, etc.
Enable :ref:`multiselect mode <tui-select-list-multiselect>` when the
user can pick several items at once, such as the files to include in
an operation. For settings that cycle through predefined values,
consider :doc:`/tui/widgets/settings_list` instead.

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
selected item. An optional ``checked`` key is only used in
:ref:`multiselect mode <tui-select-list-multiselect>`.

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
``SelectListWidget`` with an :doc:`/tui/widgets/input` for fuzzy search::

    $list->setFilter('ban'); // shows only "Banana"

Updating Items
--------------

Replace the full item list at runtime with ``setItems()``::

    $list->setItems($newItems);

This resets the selection index and any active filter.

.. _tui-select-list-multiselect:

Selecting Multiple Items
------------------------

By default, the user picks a single item and confirms it with **Enter**.
Set the ``multiselect`` constructor argument to ``true`` to let the user
check any number of items instead::

    $list = new SelectListWidget($items, multiselect: true);

In this mode, each item is rendered with a checkbox. **Space** toggles
the highlighted item and **Enter** confirms all the checked items at
once.

Add a ``checked`` key to an item to check it initially::

    $items = [
        ['value' => 'apple', 'label' => 'Apple', 'checked' => true],
        ['value' => 'banana', 'label' => 'Banana'],
        ['value' => 'cherry', 'label' => 'Cherry', 'checked' => true],
    ];

    $list = new SelectListWidget($items, multiselect: true);

Call ``getSelectedItems()`` at any time to get the checked items. In
single-select mode, this method always returns an empty array::

    $checkedItems = $list->getSelectedItems();
    // [
    //     ['value' => 'apple', 'label' => 'Apple', 'checked' => true],
    //     ['value' => 'cherry', 'label' => 'Cherry', 'checked' => true],
    // ]

Checked items are preserved when filtering the list, so the user can
narrow the list, check some items, clear the filter and continue.
Calling ``setItems()`` replaces the items along with their checked
state.

Confirming with **Enter** dispatches a ``MultiSelectEvent`` instead of
a ``SelectEvent``, and toggling an item dispatches a
``SelectionToggleEvent`` (see the events below).

.. versionadded:: 8.2

    Support for selecting multiple items was introduced in Symfony 8.2.

Events
------

* ``SelectEvent``: Fired when the user confirms a selection with
  **Enter** in single-select mode::

      use Symfony\Component\Tui\Event\SelectEvent;

      $list->onSelect(function (SelectEvent $event) {
          $value = $event->getValue();
      });

* ``MultiSelectEvent``: Fired when the user confirms the checked items
  with **Enter** in multiselect mode. It is dispatched even when no item
  is checked::

      use Symfony\Component\Tui\Event\MultiSelectEvent;

      $list->onMultiSelect(function (MultiSelectEvent $event) {
          if ($event->isEmpty()) {
              // no item was checked
          }

          $values = $event->getValues(); // e.g. ['apple', 'cherry']
          $items = $event->getItems();   // the full item arrays
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

* ``SelectionToggleEvent``: Fired when the user checks or unchecks an
  item with **Space** in multiselect mode::

      use Symfony\Component\Tui\Event\SelectionToggleEvent;

      $list->onSelectionToggle(function (SelectionToggleEvent $event) {
          $value = $event->getValue();
          $checked = $event->isChecked();
          // all the checked items after the toggle
          $checkedItems = $event->getSelectedItems();
      });

Default Keybindings
-------------------

==========================  ==============================================
Key                         Action
==========================  ==============================================
Up                          Move selection up (wraps)
Down                        Move selection down (wraps)
Page Up, Left               Page up
Page Down, Right            Page down
Space                       Toggle the highlighted item (multiselect mode)
Enter                       Confirm selection
Escape, Ctrl+C              Cancel
==========================  ==============================================
