.. index::
   single: Form; Empty data

How to configure Empty Data for a Form Class
============================================

The ``empty_data`` option allows you to specify an empty data set for your
form class. This empty data set would be used if you bind your form, but
haven't yet called ``setData()``.

By default, ``empty_data`` is set to ``null``. Or, if you have specified
a ``data_class`` option for your form class, it will default to a new instance
of that class. That instance will be created by calling the constructor
with no arguments.

If you want to override this default behavior, there are two ways to do this.

Option 1: Instantiate a new Class
---------------------------------

One reason you might use this option is if you want to use a constructor
that takes arguments. Remember, the default ``data_class`` option calls
that constructor with no arguments::

    public function getDefaultOptions()
    {
        return array(
            'empty_data' => new User($this->someDependency),
        );
    }

Option 2: Provide a Closure
---------------------------

Using a closure is the preferred method, since it will only create the object
if it is needed.

The closure must accept a ``FormInterface`` instance as the first argument::

    public function getDefaultOptions()
    {
        return array(
            'empty_data' => function (FormInterface $form) {
                return new User($form->get('username')->getData());
            },
        );
    }
