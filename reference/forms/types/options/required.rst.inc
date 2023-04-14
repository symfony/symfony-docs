``required``
~~~~~~~~~~~~

**type**: ``boolean`` **default**: ``true``

If true, an `HTML5 required attribute`_ will be rendered. The corresponding
``label`` will also render with a ``required`` class.

This is superficial and independent of validation. At best, if you let
Symfony guess your field type, then the value of this option will be guessed
from your validation information.

.. note::

    The required option also affects how empty data for each field is
    handled. For more details, see the `empty_data`_ option.

.. _`HTML5 required attribute`: https://html.spec.whatwg.org/multipage/input.html#attr-input-required
