required
~~~~~~~~

**type**: ``Boolean`` **default**: ``true``

If true, an `HTML5 required attribute`_ will be rendered. The corresponding
``label`` will also render with a ``required`` class.

This is superficial and independent from validation. At best, if you let Symfony
guess your field type, then the value of this option will be guessed from
your validation information.

.. _`HTML5 required attribute`: http://diveintohtml5.info/forms.html