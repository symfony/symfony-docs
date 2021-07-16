priority
~~~~~~~~

**type**: ``integer`` **default**: ``0``

Fields are rendered in the same order as they are included in the form. This
option changes the field rendering priority, allowing you to display fields
earlier or later than their original order.

This option will affect the view order only. The higher this priority, the
earlier the field will be rendered. Priority can also be negative and fields
with the same priority will keep their original order.
