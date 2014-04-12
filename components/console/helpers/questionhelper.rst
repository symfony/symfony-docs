.. index::
    single: Console Helpers; Question Helper

Question Helper
===============

.. versionadded:: 2.5
    The Question Helper was introduced in Symfony 2.5.

The :class:`Symfony\\Component\\Console\\Helper\\QuestionHelper` provides
functions to ask the user for more information. It is included in the default
helper set, which you can get by calling
:method:`Symfony\\Component\\Console\\Command\\Command::getHelperSet`::

    $helper = $this->getHelperSet()->get('question');

The Question Helper has a single method
:method:`Symfony\\Component\\Console\\Command\\Command::ask` that needs an
:class:`Symfony\\Component\\Console\\Output\\InputInterface` instance as the
first argument, an :class:`Symfony\\Component\\Console\\Output\\OutputInterface`
instance as the second argument and a
:class:`Symfony\\Component\\Console\\Question\\Question` as last argument.

Asking the User for Confirmation
--------------------------------

Suppose you want to confirm an action before actually executing it. Add
the following to your command::

    use Symfony\Component\Console\Question\ConfirmationQuestion;
    // ...

    $helper = $this->getHelperSet()->get('question');
    $question = new ConfirmationQuestion('Continue with this action?', false);

    if (!$helper->ask($input, $output, $question)) {
        return;
    }

In this case, the user will be asked "Continue with this action?". If the user
answers with ``y`` it returns ``true`` or ``false`` if they answer with ``n``.
The second argument to
:method:`Symfony\\Component\\Console\\Question\\ConfirmationQuestion::__construct`
is the default value to return if the user doesn't enter any input. Any other
input will ask the same question again.

Asking the User for Information
-------------------------------

You can also ask a question with more than a simple yes/no answer. For instance,
if you want to know a bundle name, you can add this to your command::

    use Symfony\Component\Console\Question\Question;
    // ...

    $question = new Question('Please enter the name of the bundle', 'AcmeDemoBundle');

    $bundle = $helper->ask($input, $output, $question);

The user will be asked "Please enter the name of the bundle". They can type
some name which will be returned by the
:method:`Symfony\\Component\\Console\\Helper\\QuestionHelper::ask` method.
If they leave it empty, the default value (``AcmeDemoBundle`` here) is returned.

Let the User Choose from a List of Answers
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you have a predefined set of answers the user can choose from, you
could use a :class:`Symfony\\Component\\Console\\Question\\ChoiceQuestion`
which makes sure that the user can only enter a valid string
from a predefined list::

    use Symfony\Component\Console\Question\ChoiceQuestion;
    // ...

    $helper = $app->getHelperSet()->get('question');
    $question = new ChoiceQuestion(
        'Please select your favorite color (defaults to red)',
        array('red', 'blue', 'yellow'),
        'red'
    );
    $question->setErrorMessage('Color %s is invalid.');

    $color = $helper->ask($input, $output, $question);
    $output->writeln('You have just selected: '.$color);

    // ... do something with the color

The option which should be selected by default is provided with the third
argument of the constructor. The default is ``null``, which means that no
option is the default one.

If the user enters an invalid string, an error message is shown and the user
is asked to provide the answer another time, until they enter a valid string
or reach the maximum number of attempts. The default value for the maximum number
of attempts is ``null``, which means infinite number attempts. You can define
your own error message using
:method:`Symfony\\Component\\Console\\Question\\ChoiceQuestion::setErrorMessage`.

Multiple Choices
................

Sometimes, multiple answers can be given. The ``ChoiceQuestion`` provides this
feature using comma separated values. This is disabled by default, to enable
this use :method:`Symfony\\Component\\Console\\Question\\ChoiceQuestion::setMultiselect`::

    use Symfony\Component\Console\Question\ChoiceQuestion;
    // ...

    $helper = $app->getHelperSet()->get('question');
    $question = new ChoiceQuestion(
        'Please select your favorite color (defaults to red)',
        array('red', 'blue', 'yellow'),
        'red'
    );
    $question->setMultiselect(true);

    $colors = $helper->ask($input, $output, $question);
    $output->writeln('You have just selected: ' . implode(', ', $colors));

Now, when the user enters ``1,2``, the result will be:
``You have just selected: blue, yellow``.

Autocompletion
~~~~~~~~~~~~~~

You can also specify an array of potential answers for a given question. These
will be autocompleted as the user types::

    use Symfony\Component\Console\Question\Question;
    // ...

    $bundles = array('AcmeDemoBundle', 'AcmeBlogBundle', 'AcmeStoreBundle');
    $question = new Question('Please enter the name of a bundle', 'FooBundle');
    $question->setAutocompleterValues($bundles);

    $name = $helper->ask($input, $output, $question);

Hiding the User's Response
~~~~~~~~~~~~~~~~~~~~~~~~~~

You can also ask a question and hide the response. This is particularly
convenient for passwords::

    use Symfony\Component\Console\Question\Question;
    // ...

    $question = new Question('What is the database password?');
    $question->setHidden(true);
    $question->setHiddenFallback(false);

    $password = $helper->ask($input, $output, $question);

.. caution::

    When you ask for a hidden response, Symfony will use either a binary, change
    stty mode or use another trick to hide the response. If none is available,
    it will fallback and allow the response to be visible unless you set this
    behavior to ``false`` using
    :method:`Symfony\\Component\\Console\\Question\\Question::setHiddenFallback`
    like in the example above. In this case, a ``RuntimeException``
    would be thrown.

Validating the Answer
---------------------

You can even validate the answer. For instance, in a previous example you asked
for the bundle name. Following the Symfony naming conventions, it should
be suffixed with ``Bundle``. You can validate that by using the
:method:`Symfony\\Component\\Console\\Question\\Question::setValidator`
method::

    use Symfony\Component\Console\Question\Question;
    // ...

    $question = new Question('Please enter the name of the bundle', 'AcmeDemoBundle');
    $question->setValidator(function ($answer) {
        if ('Bundle' !== substr($answer, -6)) {
            throw new \RuntimeException(
                'The name of the bundle should be suffixed with \'Bundle\''
            );
        }
        return $answer;
    });
    $question->setMaxAttempts(2);

    $name = $helper->ask($input, $output, $question);

The ``$validator`` is a callback which handles the validation. It should
throw an exception if there is something wrong. The exception message is displayed
in the console, so it is a good practice to put some useful information in it. The
callback function should also return the value of the user's input if the validation
was successful.

You can set the max number of times to ask with the
:method:`Symfony\\Component\\Console\\Question\\Question::setMaxAttempts` method.
If you reach this max number it will use the default value. Using ``null`` means
the amount of attempts is infinite. The user will be asked as long as they provide an
invalid answer and will only be able to proceed if their input is valid.

Validating a Hidden Response
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can also use a validator with a hidden question::

    use Symfony\Component\Console\Question\Question;
    // ...

    $helper = $this->getHelperSet()->get('question');

    $question = new Question('Please enter your password');
    $question->setValidator(function ($value) {
        if (trim($value) == '') {
            throw new \Exception('The password can not be empty');
        }

        return $value;
    });
    $question->setHidden(true);
    $question->setMaxAttempts(20);

    $password = $helper->ask($input, $output, $question);


Testing a Command that Expects Input
------------------------------------

If you want to write a unit test for a command which expects some kind of input
from the command line, you need to set the helper input stream::

    use Symfony\Component\Console\Helper\QuestionHelper;
    use Symfony\Component\Console\Helper\HelperSet;
    use Symfony\Component\Console\Tester\CommandTester;

    // ...
    public function testExecute()
    {
        // ...
        $commandTester = new CommandTester($command);

        $helper = $command->getHelper('question');
        $helper->setInputStream($this->getInputStream('Test\\n'));
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

By setting the input stream of the ``QuestionHelper``, you imitate what the
console would do internally with all user input through the cli. This way
you can test any user interaction (even complex ones) by passing an appropriate
input stream.
