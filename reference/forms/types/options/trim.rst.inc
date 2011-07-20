trim
~~~~

**type**: ``Boolean`` **default**: ``true``

If true, the whitespace of the submitted string value will be stripped
via the ``trim()`` function when the data is bound. This guarantees that
if a value is submitted with extra whitespace, it will be removed before
the value is merged back onto the underlying object.