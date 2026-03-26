Styling
=======

The Tui styling system controls how widgets look and how they are laid
out. It draws heavily from CSS concepts: styles are immutable value
objects, stylesheets map selectors to styles and a cascade determines
which rules win when multiple selectors match.

There are three ways to style a widget:

1. **Inline style**: call ``setStyle()`` on a widget instance. This
   has the highest priority and overrides everything else
   (see :doc:`/tui/style/style`).
2. **Stylesheet rules**: add CSS-like rules that match widgets by
   class name, FQCN, state or sub-element
   (see :doc:`/tui/style/stylesheets`).
3. **Utility classes**: add Tailwind-like class names to widgets
   for quick, composable styling without writing stylesheet rules
   (see :doc:`/tui/style/tailwind`).

.. toctree::
    :maxdepth: 1

    style
    stylesheets
    tailwind
