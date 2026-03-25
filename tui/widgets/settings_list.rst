SettingsListWidget
==================

``SettingsListWidget`` displays a navigable list of settings where
each item has a label and a current value. Values can be cycled through
predefined options or chosen via a submenu.

When to Use
-----------

Use ``SettingsListWidget`` for preference panels, configuration screens
or any UI where the user picks values for a set of named options. For
free-form text input, use :doc:`input` or :doc:`editor`. For picking a
single item from a plain list, use :doc:`select_list`.

Basic Usage
-----------

.. code-block:: php

    use Symfony\Component\Tui\Widget\SettingItem;
    use Symfony\Component\Tui\Widget\SettingsListWidget;

    $items = [
        new SettingItem(
            id: 'theme',
            label: 'Theme',
            currentValue: 'dark',
            description: 'Choose the color theme',
            values: ['dark', 'light', 'auto'],
        ),
        new SettingItem(
            id: 'font_size',
            label: 'Font Size',
            currentValue: '14',
            values: ['12', '14', '16', '18'],
        ),
    ];

    $settings = new SettingsListWidget($items);
    $tui->add($settings);
    $tui->setFocus($settings);

Setting Items
-------------

Each ``SettingItem`` is defined with:

* ``id``: a unique identifier for the setting.
* ``label``: the display name shown in the list.
* ``currentValue``: the initial value.
* ``description``: optional help text shown when the item is selected.
* ``values``: a list of allowed values for cycling. When empty, the
  item must use a submenu instead.
* ``submenu``: a callable that creates a focusable widget for picking
  a value (see below).

Cycling Values
--------------

When an item has predefined ``values``, the user cycles through them
with **Enter**, **Space**, **Left** or **Right**::

    new SettingItem(
        id: 'difficulty',
        label: 'Difficulty',
        currentValue: 'normal',
        values: ['easy', 'normal', 'hard'],
    );

Submenus
--------

For settings that require a richer picker (e.g. a search-and-select
list), provide a ``submenu`` callable. The callable receives the
current value and a done callback::

    new SettingItem(
        id: 'language',
        label: 'Language',
        currentValue: 'en',
        submenu: function (string $current, callable $done) {
            $list = new SelectListWidget([
                ['value' => 'en', 'label' => 'English'],
                ['value' => 'fr', 'label' => 'French'],
                ['value' => 'de', 'label' => 'German'],
            ]);

            return $list;
        },
    );

The submenu widget must implement ``FocusableInterface``. When it
dispatches a ``SelectEvent``, the selected value is applied. When it
dispatches a ``CancelEvent``, the change is discarded.

Reading and Updating Values
---------------------------

Read or update values programmatically::

    $settings->getValue('theme');        // 'dark'
    $settings->updateValue('theme', 'light');

Events
------

* ``SettingChangeEvent``: Fired whenever a setting value changes
  (cycling or submenu selection)::

      use Symfony\Component\Tui\Event\SettingChangeEvent;

      $settings->onChange(function (SettingChangeEvent $event) {
          $id = $event->getId();
          $newValue = $event->getValue();
      });

* ``CancelEvent``: Fired when the user presses **Escape** or
  **Ctrl+C**::

      $settings->onCancel(function () {
          // close the settings panel
      });

Default Keybindings
-------------------

========================  ==================================
Key                       Action
========================  ==================================
Up                        Move selection up
Down                      Move selection down
Page Up                   Page up
Page Down                 Page down
Enter, Space              Activate (cycle or open submenu)
Right                     Cycle value forward
Left                      Cycle value backward
Escape, Ctrl+C            Cancel
========================  ==================================
