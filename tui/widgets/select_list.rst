SelectListWidget
================

``SelectListWidget`` displays a scrollable list of items with keyboard
navigation. The user can move through the list and confirm one item or,
in multiselect mode, several items at once.

When to Use
-----------

Use ``SelectListWidget`` when the user must pick one or more items from
a list: a file picker, a command palette result list, a model selector,
etc. Enable :ref:`multiselect mode <tui-select-list-multiselect>` when
the user can pick more than one item, such as choosing which files to
delete. For settings that cycle through predefined values, consider
:doc:`/tui/widgets/settings_list` instead.

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

.. versionadded:: 8.2

    The ``multiselect`` argument was introduced in Symfony 8.2.

By default, the user picks a single item and confirms it with **Enter**.
Set the ``multiselect`` constructor argument to ``true`` to let the user
check any number of items instead::

    $items = [
        ['value' => 'apple', 'label' => 'Apple', 'checked' => true],
        ['value' => 'banana', 'label' => 'Banana'],
        ['value' => 'cherry', 'label' => 'Cherry', 'checked' => true],
    ];

    // the 'checked' key checks an item when the list is first displayed
    $list = new SelectListWidget($items, multiselect: true);

In this mode, each item is rendered with a checkbox. **Space** toggles
the highlighted item and **Enter** confirms all the checked items at
once.

Call ``getSelectedItems()`` at any time to get the checked items. In
single-select mode, this method always returns an empty array::

    $checkedItems = $list->getSelectedItems();
    // [
    //     ['value' => 'apple', 'label' => 'Apple', 'checked' => true],
    //     ['value' => 'cherry', 'label' => 'Cherry', 'checked' => true],
    // ]

Filtering the list doesn't change the checked items, so the user can
narrow the list, check some items, clear the filter and continue.
``setItems()`` also discards the checked items; set ``checked`` on the
new items to check them.

Events
------

* ``SelectEvent``: Fired when the user confirms a selection with
  **Enter** in single-select mode::

      use Symfony\Component\Tui\Event\SelectEvent;

      $list->onSelect(function (SelectEvent $event) {
          $value = $event->getValue();
      });

* ``MultiSelectEvent``: Fired when the user confirms the checked items
  with **Enter** in multiselect mode, even when no item is checked::

      use Symfony\Component\Tui\Event\MultiSelectEvent;

      $list->onMultiSelect(function (MultiSelectEvent $event) {
          if ($event->isEmpty()) {
              return; // the user confirmed without checking any item
          }

          $values = $event->getValues(); // ['apple', 'cherry']
          $items = $event->getItems(); // the full item arrays
      });

* ``SelectionToggleEvent``: Fired when the user checks or unchecks an
  item with **Space** in multiselect mode::

      use Symfony\Component\Tui\Event\SelectionToggleEvent;

      $list->onSelectionToggle(function (SelectionToggleEvent $event) {
          $toggledValue = $event->getValue();
          $isNowChecked = $event->isChecked();
          // all the items checked after this toggle
          $checkedItems = $event->getSelectedItems();
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

==========================  ==============================================
Key                         Action
==========================  ==============================================
Up                          Move selection up (wraps)
Down                        Move selection down (wraps)
Page Up, Left, Ctrl+B       Page up
Page Down, Right, Ctrl+F    Page down
Space                       Toggle the highlighted item (multiselect mode)
Enter                       Confirm selection
Escape, Ctrl+C              Cancel
==========================  ==============================================
