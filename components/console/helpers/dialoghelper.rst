.. index::
    single: Console Helpers; Dialog Helper

Dialog Helper
=============

The :class:`Symfony\\Component\\Console\\Helper\\DialogHelper` provides
functions to ask the user for more information. It is included in the default
helper set, which you can get by calling
:method:`Symfony\\Component\\Console\\Command\\Command::getHelperSet`::

    $dialog = $this->getHelperSet()->get('dialog');

All the methods inside the Dialog Helper have an 
:class:`Symfony\\Component\\Console\\Output\\OutputInterface` as first the
argument, the question as the second argument and the default value as last
argument.

Asking the User for confirmation
--------------------------------

Suppose you want to confirm an action before actually executing it. Add 
the following to your command::

    // ...
    if (!$dialog->askConfirmation(
            $output,
            '<question>Continue with this action?</question>',
            false
        )) {
        return;
    }

In this case, the user will be asked "Continue with this action", and will return
``true`` if the user answers with ``y`` or false in any other case. The third 
argument to ``askConfirmation`` is the default value to return if the user doesn't 
enter any input.

Asking the User for Information
-------------------------------

You can also ask question with more than a simple yes/no answer. For instance,
if you want to know a bundle name, you can add this to your command::

    // ...
    $bundle = $dialog->ask(
        $output,
        'Please enter the name of the bundle',
        'AcmeDemoBundle'
    );

The user will be asked "Please enter the name of the bundle". She can type
some name which will be returned by the ``ask`` method. If she leaves it empty,
the default value (``AcmeDemoBundle`` here) is returned.

Hiding the User's Response
~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 2.2
    The ``askHiddenResponse`` method was added in Symfony 2.2.

You can also ask question and hide the response. This is particularly
convenient for passwords::

    $dialog = $this->getHelperSet()->get('dialog');
    $password = $dialog->askHiddenResponse(
        $output,
        'What is the database password?',
        false
    );

.. caution::

    When you ask for a hidden response, Symfony will use either a binary, change
    stty mode or use another trick to hide the response. If none is available,
    it will fallback and allow the response to be visible unless you pass ``false``
    as the third argument like in the example above. In this case, a RuntimeException
    would be thrown.

Validating the Answer
---------------------

You can even validate the answer. For instance, in the last example you asked
for the bundle name. Following the Symfony2 naming conventions, it should
be suffixed with ``Bundle``. You can validate that by using the 
:method:`Symfony\\Component\\Console\\Helper\\DialogHelper::askAndValidate` 
method::

    // ...
    $bundle = $dialog->askAndValidate(
        $output,
        'Please enter the name of the bundle',
        function ($answer) {
            if ('Bundle' !== substr($answer, -6)) {
                throw new \RunTimeException(
                    'The name of the bundle should be suffixed with \'Bundle\''
                );
            }
        },
        false,
        'AcmeDemoBundle'
    );

This methods has 2 new arguments, the full signature is::

    askAndValidate(
        OutputInterface $output, 
        string|array $question, 
        callback $validator, 
        integer $attempts = false, 
        string $default = null
    )

The ``$validator`` is a callback which handles the validation. It should
throw an exception if there is something wrong. The exception message is displayed
in the console, so it is a good practice to put some useful information 
in it.

You can set the max number of times to ask in the ``$attempts`` argument.
If you reach this max number it will use the default value, which is given
in the last argument. Using ``false`` means the amount of attempts is infinite.
The user will be asked as long as he provides an invalid answer and will only
be able to proceed if her input is valid.

Hiding the User's Response
~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 2.2
    The ``askHiddenResponseAndValidate`` method was added in Symfony 2.2.

You can also ask and validate a hidden response::

    $dialog = $this->getHelperSet()->get('dialog');

    $validator = function ($value) {
        if (trim($value) == '') {
            throw new \Exception('The password can not be empty');
        }
    }

    $password = $dialog->askHiddenResponseAndValidate(
        $output,
        'Please enter the name of the widget',
        $validator,
        20,
        false
    );

If you want to allow the response to be visible if it cannot be hidden for
some reason, pass true as the fifth argument.

Let the user choose from a list of answers
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 2.2
    The ``select`` method was added in Symfony 2.2.

If you have a predefined set of answers the user can choose from, you
could use the ``ask`` method described above or, to make sure the user
provided a correct answer, the ``askAndValidate`` method. Both have
the disadvantage that you need to handle incorrect values yourself.

Instead, you can use the 
:method:`Symfony\\Component\\Console\\Helper\\DialogHelper::select`
method, which makes sure that the user can only enter a valid string
from a predefined list::

    $dialog = $app->getHelperSet()->get('dialog');
    $colors = array('red', 'blue', 'yellow');
    
    $colorKey = $dialog->select($output, 'Please select your favorite color (default to red)', $colors, 0);
    $output->writeln('You have just selected: ' . $colors[$color]);
    
    // ... do something with the color
    
If the user enters an invalid string, an error message is shown and the user
is asked to provide the answer another time, till he enters a valid string.

The ``select`` method takes 6 parameters:

* ``output``: The output instance
* ``question``: The question to ask
* ``choices``: An array of strings with the choices the user can pick
* ``default``: The index of the default value in the array or ``null`` if no 
    default should be provided (default ``null``)
* ``attempts``: Maximum number of times to ask or ``false`` for infinite times 
    (default ``false``)
* ``errorMessage``: Error message to display when wrong answer is entered (default
    ``Value "%s" is invalid``)