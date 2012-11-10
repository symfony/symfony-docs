.. index::
    single: Console Helpers; Dialog Helper

Dialog Helper
=============

The Dialog Helper provides functions to ask the user for more information.

The DialogHelper is included in the default helper set, which you can get
by calling :method:`Symfony\\Component\\Console\\Command\\Command::getHelperSet`::

    $dialog = $this->getHelperSet()->get('dialog');

All the methods inside the Dialog Helper have an 
:class:`Symfony\\Component\\Console\\Output\\OutputInterface` as first argument,
the question as second argument and the default value as last argument.

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

In this case, the user will be asked "Continue with this action", and unless
they answer with ``y``, the task will stop running. The third argument to
``askConfirmation`` is the default value to return if the user doesn't enter
any input.

Asking the User for information
-------------------------------

You can also ask question with more than a simple yes/no answer. For instance,
you want to know a bundle name, you can add this to your command::

    // ...
    $bundle = $dialog->ask(
        $output,
        'Please enter the name of the bundle',
        'AcmeDemoBundle'
    );

The user will be asked "Please enter the name of the bundle". They can type
some name or if they leave it empty the default value (``AcmeDemoBundle`` here)
is used. This value will be returned.

Validating the answer
---------------------

You can even validate the answer. For instance, in our last example we asked
for the bundle name. Following the Symfony2 naming conventions, it should
be suffixed with ``Bundle``. We can validate that by using the 
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
throw an exception if there is something wrong. The exception message displayed
in the console, so it is a good practice to put some usefull information 
in it.

You can set the max number of times to ask in the ``$attempts`` argument.
If we reach this max number it will use the default value, which is given
in the last argument. This is ``false`` by default, which means it is infinite.
