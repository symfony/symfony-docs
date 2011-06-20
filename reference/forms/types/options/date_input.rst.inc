*   ``input`` [type: string, default: ``datetime``]
    The value of the input for the widget.  Can be ``string``, ``datetime``
    or ``array``.  The form type input value will be returned  in the format
    specified.  The input of ``April 21th, 2011`` as an array would return:
    
    .. code-block:: php

        array('month' => 4, 'day' => 21, 'year' => 2011 )