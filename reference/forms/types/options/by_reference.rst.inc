by_reference
~~~~~~~~~~~~

**type**: ``Boolean`` **default**: ``true``

If the underlying value of a field is an object and this option is set to
``true``, then the resulting object won't actually be set when binding the
form. For example, if you have a protected ``author`` field on your underlying
object which is an instance of some ``Author`` object, then if ``by_reference``
is ``false``, that ``Author`` object will be updated with the submitted data,
but ``setAuthor`` will not actually be called on the main object. Since the
``Author`` object is a reference, this only really makes a difference if
you have some custom logic in your ``setAuthor`` method that you want to
guarantee will be run. In that case, set this option to ``false``.