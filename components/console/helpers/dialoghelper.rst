.. index::
    single: Console Helpers; Dialog Helper

Dialog Helper
=============

.. caution::

    The Dialog Helper was deprecated in Symfony 2.5 and will be removed in
    Symfony 3.0. You should now use the
    :doc:`Question Helper </components/console/helpers/questionhelper>` instead,
    which is simpler to use.

The :class:`Symfony\\Component\\Console\\Helper\\DialogHelper` provides
functions to ask the user for more information. It is included in the default
helper set, which you can get by calling
:method:`Symfony\\Component\\Console\\Command\\Command::getHelperSet`::

    $dialog = $this->getHelper('dialog');

All the methods inside the Dialog Helper have an
:class:`Symfony\\Component\\Console\\Output\\OutputInterface` as the first
argument, the question as the second argument and the default value as the last
argument.

Asking the User for Confirmation
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

In this case, the user will be asked "Continue with this action?", and will
return ``true`` if the user answers with ``y`` or ``false`` if the user answers
with ``n``. The third argument to
:method:`Symfony\\Component\\Console\\Helper\\DialogHelper::askConfirmation`
is the default value to return if the user doesn't enter any input. Any other
input will ask the same question again.

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

The user will be asked "Please enter the name of the bundle". They can type
some name which will be returned by the
:method:`Symfony\\Component\\Console\\Helper\\DialogHelper::ask` method.
If they leave it empty, the default value (AcmeDemoBundle here) is returned.

Autocompletion
~~~~~~~~~~~~~~

You can also specify an array of potential answers for a given question. These
will be autocompleted as the user types::

    $dialog = $this->getHelper('dialog');
    $bundleNames = array('AcmeDemoBundle', 'AcmeBlogBundle', 'AcmeStoreBundle');
    $name = $dialog->ask(
        $output,
        'Please enter the name of a bundle',
        'FooBundle',
        $bundleNames
    );

Hiding the User's Response
~~~~~~~~~~~~~~~~~~~~~~~~~~

You can also ask a question and hide the response. This is particularly
convenient for passwords::

    $dialog = $this->getHelper('dialog');
    $password = $dialog->askHiddenResponse(
        $output,
        'What is the database password?',
        false
    );

.. caution::

    When you ask for a hidden response, Symfony will use either a binary, change
    stty mode or use another trick to hide the response. If none is available,
    it will fallback and allow the response to be visible unless you pass ``false``
    as the third argument like in the example above. In this case, a ``RuntimeException``
    would be thrown.

Validating the Answer
---------------------

You can even validate the answer. For instance, in the last example you asked
for the bundle name. Following the Symfony naming conventions, it should
be suffixed with ``Bundle``. You can validate that by using the
:method:`Symfony\\Component\\Console\\Helper\\DialogHelper::askAndValidate`
method::

    // ...
    $bundle = $dialog->askAndValidate(
        $output,
        'Please enter the name of the bundle',
        function ($answer) {
            if ('Bundle' !== substr($answer, -6)) {
                throw new \RuntimeException(
                    'The name of the bundle should be suffixed with \'Bundle\''
                );
            }

            return $answer;
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
        string $default = null,
        array $autocomplete = null
    )

The ``$validator`` is a callback which handles the validation. It should
throw an exception if there is something wrong. The exception message is displayed
in the console, so it is a good practice to put some useful information in it. The callback
function should also return the value of the user's input if the validation was successful.

You can set the max number of times to ask in the ``$attempts`` argument.
If you reach this max number it will use the default value.
Using ``false`` means the amount of attempts is infinite.
The user will be asked as long as they provide an invalid answer and will only
be able to proceed if their input is valid.

Validating a Hidden Response
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can also ask and validate a hidden response::

    $dialog = $this->getHelper('dialog');

    $validator = function ($value) {
        if ('' === trim($value)) {
            throw new \Exception('The password can not be empty');
        }

        return $value;
    };

    $password = $dialog->askHiddenResponseAndValidate(
        $output,
        'Please enter your password',
        $validator,
        20,
        false
    );

If you want to allow the response to be visible if it cannot be hidden for
some reason, pass true as the fifth argument.

Let the User Choose from a List of Answers
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you have a predefined set of answers the user can choose from, you
could use the ``ask`` method described above or, to make sure the user
provided a correct answer, the ``askAndValidate`` method. Both have
the disadvantage that you need to handle incorrect values yourself.

Instead, you can use the
:method:`Symfony\\Component\\Console\\Helper\\DialogHelper::select`
method, which makes sure that the user can only enter a valid string
from a predefined list::

    $dialog = $this->getHelper('dialog');
    $colors = array('red', 'blue', 'yellow');

    $color = $dialog->select(
        $output,
        'Please select your favorite color (default to red)',
        $colors,
        0
    );
    $output->writeln('You have just selected: ' . $colors[$color]);

    // ... do something with the color

The option which should be selected by default is provided with the fourth
argument. The default is ``null``, which means that no option is the default one.

If the user enters an invalid string, an error message is shown and the user
is asked to provide the answer another time, until they enter a valid string
or the maximum attempts is reached (which you can define in the fifth
argument). The default value for the attempts is ``false``, which means infinite
attempts. You can define your own error message in the sixth argument.

.. versionadded:: 2.3
    Multiselect support was introduced in Symfony 2.3.

Multiple Choices
................

Sometimes, multiple answers can be given. The DialogHelper provides this
feature using comma separated values. This is disabled by default, to enable
this set the seventh argument to ``true``::

    // ...

    $selected = $dialog->select(
        $output,
        'Please select your favorite color (default to red)',
        $colors,
        0,
        false,
        'Value "%s" is invalid',
        true // enable multiselect
    );

    $selectedColors = array_map(function ($c) use ($colors) {
        return $colors[$c];
    }, $selected);

    $output->writeln(
        'You have just selected: ' . implode(', ', $selectedColors)
    );

Now, when the user enters ``1,2``, the result will be:
``You have just selected: blue, yellow``.

Testing a Command which Expects Input
-------------------------------------

If you want to write a unit test for a command which expects some kind of input
from the command line, you need to overwrite the HelperSet used by the command::

    use Symfony\Component\Console\Application;
    use Symfony\Component\Console\Helper\DialogHelper;
    use Symfony\Component\Console\Helper\HelperSet;
    use Symfony\Component\Console\Tester\CommandTester;

    // ...
    public function testExecute()
    {
        // ...
        $application = new Application();
        $application->add(new MyCommand());
        $command = $application->find('my:command:name');
        $commandTester = new CommandTester($command);

        $dialog = $command->getHelper('dialog');
        $dialog->setInputStream($this->getInputStream("Test\n"));
        // Equals to a user inputting "Test" and hitting ENTER
        // If you need to enter a confirmation, "yes\n" will work

        $commandTester->execute(array('command' => $command->getName()));

        // $this->assertRegExp('/.../', $commandTester->getDisplay());
    }

    protected function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);

        return $stream;
    }

By setting the input stream of the ``DialogHelper``, you imitate what the
console would do internally with all user input through the cli. This way
you can test any user interaction (even complex ones) by passing an appropriate
input stream.

.. seealso::

    You find more information about testing commands in the console component
    docs about :ref:`testing console commands <component-console-testing-commands>`.
