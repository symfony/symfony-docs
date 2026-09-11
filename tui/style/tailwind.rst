Tailwind Utility Classes
========================

``TailwindStylesheet`` extends the standard stylesheet with support
for Tailwind-like utility classes. Instead of writing explicit rules,
you add short class names directly to widgets for quick, composable
styling.

When to Use
-----------

Use utility classes for rapid prototyping, one-off styling or when a
full stylesheet rule feels like overkill. For consistent theming
across many widgets, prefer :doc:`/tui/style/stylesheets`.

Setup
-----

``TailwindStylesheet`` is a drop-in replacement for ``StyleSheet``.
Pass it to the Tui the same way::

    use Symfony\Component\Tui\Style\TailwindStylesheet;

    $stylesheet = new TailwindStylesheet();
    $tui->addStyleSheet($stylesheet);

Adding utility classes to widgets::

    $widget->addStyleClass('p-2');
    $widget->addStyleClass('bg-red-500');
    $widget->addStyleClass('bold');
    $widget->addStyleClass('border');
    $widget->addStyleClass('border-rounded');

Utility classes compose naturally; each one sets a specific property
and they are merged together.

Cascade Position
----------------

Utility classes sit above regular stylesheet rules and below inline
styles in the cascade:

1. Stylesheet rules (universal, FQCN, CSS class, state, breakpoints)
2. **Utility class styles**
3. Instance style (``widget->setStyle()``)

This means utility classes always override stylesheet rules but can
still be overridden by an inline style.

Padding
-------

======================  ==========================================
Class                   Effect
======================  ==========================================
``p-{n}``               All sides
``px-{n}``              Left and right
``py-{n}``              Top and bottom
``pt-{n}``              Top only
``pr-{n}``              Right only
``pb-{n}``              Bottom only
``pl-{n}``              Left only
======================  ==========================================

.. code-block:: php

    $widget->addStyleClass('p-2');
    $widget->addStyleClass('px-4');

Border
------

Width classes:

=========================================  ========================
Class                                      Effect
=========================================  ========================
``border``                                 All sides, width 1
``border-{n}``                             All sides, width n
``border-t``, ``border-r``, ``border-b``,
``border-l``                               Individual side, width 1
``border-t-{n}``, ``border-r-{n}``,
``border-b-{n}``, ``border-l-{n}``         Individual side, width n
``border-none``                            Remove border
=========================================  ========================

Pattern classes:

==========================  ==========================================
Class                       Pattern
==========================  ==========================================
``border-normal``           Standard box-drawing
``border-rounded``          Rounded corners
``border-double``           Double-line box
``border-tall``             Block-style vertical
``border-wide``             Block-style horizontal
``border-tall-medium``      ~4px balanced (tall)
``border-wide-medium``      ~4px balanced (wide)
``border-tall-large``       ~8px balanced (tall)
``border-wide-large``       ~8px balanced (wide)
==========================  ==========================================

Border color classes use the same color syntax as text and background
(see below)::

    $widget->addStyleClass('border');
    $widget->addStyleClass('border-rounded');
    $widget->addStyleClass('border-cyan-400');

Colors
------

Both text and background colors support three formats:

**Tailwind shades**, ``{family}-{shade}`` where family is one of
``slate``, ``gray``, ``zinc``, ``neutral``, ``stone``, ``red``,
``orange``, ``amber``, ``yellow``, ``lime``, ``green``, ``emerald``,
``teal``, ``cyan``, ``sky``, ``blue``, ``indigo``, ``violet``,
``purple``, ``fuchsia``, ``pink``, ``rose`` and shade is ``50``,
``100``, ``200``, ``300``, ``400``, ``500``, ``600``, ``700``,
``800``, ``900`` or ``950``::

    $widget->addStyleClass('text-blue-700');
    $widget->addStyleClass('bg-emerald-100');

**Hex colors**, wrap in brackets::

    $widget->addStyleClass('text-[#ff5500]');
    $widget->addStyleClass('bg-[#1e1e2e]');

**256-palette index**::

    $widget->addStyleClass('text-202');
    $widget->addStyleClass('bg-236');

Text Decoration
---------------

========================  ==========================================
Class                     Effect
========================  ==========================================
``bold``                  Enable bold
``not-bold``              Disable bold
``dim``                   Enable dim/faint
``not-dim``               Disable dim
``italic``                Enable italic
``not-italic``            Disable italic
``underline``             Enable underline
``no-underline``          Disable underline
``line-through``          Enable strikethrough
``no-line-through``       Disable strikethrough
``reverse``               Enable reverse video
``no-reverse``            Disable reverse video
========================  ==========================================

Text Alignment
--------------

========================  ==========================================
Class                     Effect
========================  ==========================================
``text-left``             Left-align text (default)
``text-center``           Center-align text
``text-right``            Right-align text
========================  ==========================================

Font
----

Set a FIGlet font for text rendering::

    $widget->addStyleClass('font-big');
    $widget->addStyleClass('font-slant');

Available bundled fonts: ``big``, ``small``, ``slant``, ``standard``,
``mini``.

Layout
------

========================  ====================================================
Class                     Effect
========================  ====================================================
``flex-row``              Horizontal layout direction
``flex-col``              Vertical layout direction
``flex-{n}``              Flex grow (``0`` = intrinsic, ``1+`` = proportional)
``gap-{n}``               Gap between children
``hidden``                Hide the widget
``visible``               Show the widget
========================  ====================================================

Alignment
---------

========================  ==========================================
Class                     Effect
========================  ==========================================
``align-left``            Left-align child widgets
``align-center``          Center child widgets horizontally
``align-right``           Right-align child widgets
``valign-top``            Top-align child widgets
``valign-center``         Center child widgets vertically
``valign-bottom``         Bottom-align child widgets
========================  ==========================================

Combining with Stylesheet Rules
-------------------------------

Utility classes and regular CSS-class rules coexist. A class name
that isn't recognized as a utility is treated as a CSS class for
stylesheet matching::

    $stylesheet = new TailwindStylesheet();
    $stylesheet->addRule('.panel', new Style(
        background: '#1e1e2e',
    ));

    $widget->addStyleClass('panel');  // matched by the rule
    $widget->addStyleClass('p-2');    // parsed as utility
    $widget->addStyleClass('bold');   // parsed as utility
