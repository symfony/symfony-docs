Question Helper
===============

The :class:`Symfony\\Component\\Console\\Helper\\QuestionHelper` provides
functions to ask the user for more information. It is included in the default
helper set and you can get it by calling
:method:`Symfony\\Component\\Console\\Command\\Command::getHelper`::

    $helper = $this->getHelper('question');

The Question Helper has a single method
:method:`Symfony\\Component\\Console\\Helper\\QuestionHelper::ask` that needs an
:class:`Symfony\\Component\\Console\\Input\\InputInterface` instance as the
first argument, an :class:`Symfony\\Component\\Console\\Output\\OutputInterface`
instance as the second argument and a
:class:`Symfony\\Component\\Console\\Question\\Question` as last argument.

.. note::

    As an alternative, consider using the
    :ref:`SymfonyStyle <symfony-style-questions>` to ask questions.

Asking the User for Confirmation
--------------------------------

Suppose you want to confirm an action before actually executing it. Add
the following to your command::

    // ...
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Question\ConfirmationQuestion;

    class YourCommand extends Command
    {
        // ...

        public function execute(InputInterface $input, OutputInterface $output): int
        {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('Continue with this action?', false);

            if (!$helper->ask($input, $output, $question)) {
                return Command::SUCCESS;
            }

            // ... do something here

            return Command::SUCCESS;
        }
    }

In this case, the user will be asked "Continue with this action?". If the user
answers with ``y`` it returns ``true`` or ``false`` if they answer with ``n``.
The second argument to
:method:`Symfony\\Component\\Console\\Question\\ConfirmationQuestion::__construct`
is the default value to return if the user doesn't enter any valid input. If
the second argument is not provided, ``true`` is assumed.

.. tip::

    You can customize the regex used to check if the answer means "yes" in the
    third argument of the constructor. For instance, to allow anything that
    starts with either ``y`` or ``j``, you would set it to::

        $question = new ConfirmationQuestion(
            'Continue with this action?',
            false,
            '/^(y|j)/i'
        );

    The regex defaults to ``/^y/i``.

.. note::

    By default, the question helper uses the error output (``stderr``) as
    its default output. This behavior can be changed by passing an instance of
    :class:`Symfony\\Component\\Console\\Output\\StreamOutput` to the
    :method:`Symfony\\Component\\Console\\Helper\\QuestionHelper::ask`
    method.

Asking the User for Information
-------------------------------

You can also ask a question with more than a simple yes/no answer. For instance,
if you want to know a bundle name, you can add this to your command::

    use Symfony\Component\Console\Question\Question;

    // ...
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        // ...
        $question = new Question('Please enter the name of the bundle', 'AcmeDemoBundle');

        $bundleName = $helper->ask($input, $output, $question);

        // ... do something with the bundleName

        return Command::SUCCESS;
    }

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
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        // ...
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Please select your favorite color (defaults to red)',
            // choices can also be PHP objects that implement __toString() method
            ['red', 'blue', 'yellow'],
            0
        );
        $question->setErrorMessage('Color %s is invalid.');

        $color = $helper->ask($input, $output, $question);
        $output->writeln('You have just selected: '.$color);

        // ... do something with the color

        return Command::SUCCESS;
    }

The option which should be selected by default is provided with the third
argument of the constructor. The default is ``null``, which means that no
option is the default one.

If the user enters an invalid string, an error message is shown and the user
is asked to provide the answer another time, until they enter a valid string
or reach the maximum number of attempts. The default value for the maximum number
of attempts is ``null``, which means an infinite number of attempts. You can define
your own error message using
:method:`Symfony\\Component\\Console\\Question\\ChoiceQuestion::setErrorMessage`.

Multiple Choices
................

Sometimes, multiple answers can be given. The ``ChoiceQuestion`` provides this
feature using comma separated values. This is disabled by default, to enable
this use :method:`Symfony\\Component\\Console\\Question\\ChoiceQuestion::setMultiselect`::

    use Symfony\Component\Console\Question\ChoiceQuestion;

    // ...
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        // ...
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Please select your favorite colors (defaults to red and blue)',
            ['red', 'blue', 'yellow'],
            '0,1'
        );
        $question->setMultiselect(true);

        $colors = $helper->ask($input, $output, $question);
        $output->writeln('You have just selected: ' . implode(', ', $colors));

        return Command::SUCCESS;
    }

Now, when the user enters ``1,2``, the result will be:
``You have just selected: blue, yellow``.

If the user does not enter anything, the result will be:
``You have just selected: red, blue``.

Autocompletion
~~~~~~~~~~~~~~

You can also specify an array of potential answers for a given question. These
will be autocompleted as the user types::

    use Symfony\Component\Console\Question\Question;

    // ...
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        // ...
        $helper = $this->getHelper('question');

        $bundles = ['AcmeDemoBundle', 'AcmeBlogBundle', 'AcmeStoreBundle'];
        $question = new Question('Please enter the name of a bundle', 'FooBundle');
        $question->setAutocompleterValues($bundles);

        $bundleName = $helper->ask($input, $output, $question);

        // ... do something with the bundleName

        return Command::SUCCESS;
    }

In more complex use cases, it may be necessary to generate suggestions on the
fly, for instance if you wish to autocomplete a file path. In that case, you can
provide a callback function to dynamically generate suggestions::

    use Symfony\Component\Console\Question\Question;

    // ...
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        // This function is called whenever the input changes and new
        // suggestions are needed.
        $callback = function (string $userInput): array {
            // Strip any characters from the last slash to the end of the string
            // to keep only the last directory and generate suggestions for it
            $inputPath = preg_replace('%(/|^)[^/]*$%', '$1', $userInput);
            $inputPath = '' === $inputPath ? '.' : $inputPath;

            // CAUTION - this example code allows unrestricted access to the
            // entire filesystem. In real applications, restrict the directories
            // where files and dirs can be found
            $foundFilesAndDirs = @scandir($inputPath) ?: [];

            return array_map(function (string $dirOrFile) use ($inputPath): string {
                return $inputPath.$dirOrFile;
            }, $foundFilesAndDirs);
        };

        $question = new Question('Please provide the full path of a file to parse');
        $question->setAutocompleterCallback($callback);

        $filePath = $helper->ask($input, $output, $question);

        // ... do something with the filePath

        return Command::SUCCESS;
    }

Do not Trim the Answer
~~~~~~~~~~~~~~~~~~~~~~

You can also specify if you want to not trim the answer by setting it directly with
:method:`Symfony\\Component\\Console\\Question\\Question::setTrimmable`::

    use Symfony\Component\Console\Question\Question;

    // ...
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        // ...
        $helper = $this->getHelper('question');

        $question = new Question('What is the name of the child?');
        $question->setTrimmable(false);
        // if the users inputs 'elsa ' it will not be trimmed and you will get 'elsa ' as value
        $name = $helper->ask($input, $output, $question);

        // ... do something with the name

        return Command::SUCCESS;
    }

Accept Multiline Answers
~~~~~~~~~~~~~~~~~~~~~~~~

By default, the question helper stops reading user input when it receives a newline
character (i.e., when the user hits ``ENTER`` once). However, you may specify that
the response to a question should allow multiline answers by passing ``true`` to
:method:`Symfony\\Component\\Console\\Question\\Question::setMultiline`::

    use Symfony\Component\Console\Question\Question;

    // ...
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        // ...
        $helper = $this->getHelper('question');

        $question = new Question('How do you solve world peace?');
        $question->setMultiline(true);

        $answer = $helper->ask($input, $output, $question);

        // ... do something with the answer

        return Command::SUCCESS;
    }

Multiline questions stop reading user input after receiving an end-of-transmission
control character (``Ctrl-D`` on Unix systems or ``Ctrl-Z`` on Windows).

Hiding the User's Response
~~~~~~~~~~~~~~~~~~~~~~~~~~

You can also ask a question and hide the response. This is particularly
convenient for passwords::

    use Symfony\Component\Console\Question\Question;

    // ...
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        // ...
        $helper = $this->getHelper('question');

        $question = new Question('What is the database password?');
        $question->setHidden(true);
        $question->setHiddenFallback(false);

        $password = $helper->ask($input, $output, $question);

        // ... do something with the password

        return Command::SUCCESS;
    }

.. caution::

    When you ask for a hidden response, Symfony will use either a binary, change
    ``stty`` mode or use another trick to hide the response. If none is available,
    it will fallback and allow the response to be visible unless you set this
    behavior to ``false`` using
    :method:`Symfony\\Component\\Console\\Question\\Question::setHiddenFallback`
    like in the example above. In this case, a ``RuntimeException``
    would be thrown.

.. note::

    The ``stty`` command is used to get and set properties of the command line
    (such as getting the number of rows and columns or hiding the input text).
    On Windows systems, this ``stty`` command may generate gibberish output and
    mangle the input text. If that's your case, disable it with this command::

        use Symfony\Component\Console\Helper\QuestionHelper;
        use Symfony\Component\Console\Question\ChoiceQuestion;

        // ...
        public function execute(InputInterface $input, OutputInterface $output): int
        {
            // ...
            $helper = $this->getHelper('question');
            QuestionHelper::disableStty();

            // ...

            return Command::SUCCESS;
        }

Normalizing the Answer
----------------------

Before validating the answer, you can "normalize" it to fix minor errors or
tweak it as needed. For instance, in a previous example you asked for the bundle
name. In case the user adds white spaces around the name by mistake, you can
trim the name before validating it. To do so, configure a normalizer using the
:method:`Symfony\\Component\\Console\\Question\\Question::setNormalizer`
method::

    use Symfony\Component\Console\Question\Question;

    // ...
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        // ...
        $helper = $this->getHelper('question');

        $question = new Question('Please enter the name of the bundle', 'AcmeDemoBundle');
        $question->setNormalizer(function (string $value): string {
            // $value can be null here
            return $value ? trim($value) : '';
        });

        $bundleName = $helper->ask($input, $output, $question);

        // ... do something with the bundleName

        return Command::SUCCESS;
    }

.. caution::

    The normalizer is called first and the returned value is used as the input
    of the validator. If the answer is invalid, don't throw exceptions in the
    normalizer and let the validator handle those errors.

.. _console-validate-question-answer:

Validating the Answer
---------------------

You can even validate the answer. For instance, in a previous example you asked
for the bundle name. Following the Symfony naming conventions, it should
be suffixed with ``Bundle``. You can validate that by using the
:method:`Symfony\\Component\\Console\\Question\\Question::setValidator`
method::

    use Symfony\Component\Console\Question\Question;

    // ...
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        // ...
        $helper = $this->getHelper('question');

        $question = new Question('Please enter the name of the bundle', 'AcmeDemoBundle');
        $question->setValidator(function (string $answer): string {
            if (!is_string($answer) || 'Bundle' !== substr($answer, -6)) {
                throw new \RuntimeException(
                    'The name of the bundle should be suffixed with \'Bundle\''
                );
            }

            return $answer;
        });
        $question->setMaxAttempts(2);

        $bundleName = $helper->ask($input, $output, $question);

        // ... do something with the bundleName

        return Command::SUCCESS;
    }

The ``$validator`` is a callback which handles the validation. It should
throw an exception if there is something wrong. The exception message is displayed
in the console, so it is a good practice to put some useful information in it. The
callback function should also return the value of the user's input if the validation
was successful.

You can set the max number of times to ask with the
:method:`Symfony\\Component\\Console\\Question\\Question::setMaxAttempts` method.
If you reach this max number it will use the default value. Using ``null`` means
the number of attempts is infinite. The user will be asked as long as they provide an
invalid answer and will only be able to proceed if their input is valid.

.. tip::

    You can even use the :doc:`Validator </validation>` component to
    validate the input by using the :method:`Symfony\\Component\\Validator\\Validation::createCallable`
    method::

        use Symfony\Component\Validator\Constraints\Regex;
        use Symfony\Component\Validator\Validation;

        $question = new Question('Please enter the name of the bundle', 'AcmeDemoBundle');
        $validation = Validation::createCallable(new Regex([
            'pattern' => '/^[a-zA-Z]+Bundle$/',
            'message' => 'The name of the bundle should be suffixed with \'Bundle\'',
        ]));
        $question->setValidator($validation);

Validating a Hidden Response
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can also use a validator with a hidden question::

    use Symfony\Component\Console\Question\Question;

    // ...
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        // ...
        $helper = $this->getHelper('question');

        $question = new Question('Please enter your password');
        $question->setNormalizer(function (?string $value): string {
            return $value ?? '';
        });
        $question->setValidator(function (string $value): string {
            if ('' === trim($value)) {
                throw new \Exception('The password cannot be empty');
            }

            return $value;
        });
        $question->setHidden(true);
        $question->setMaxAttempts(20);

        $password = $helper->ask($input, $output, $question);

        // ... do something with the password

        return Command::SUCCESS;
    }

Testing a Command that Expects Input
------------------------------------

If you want to write a unit test for a command which expects some kind of input
from the command line, you need to set the inputs that the command expects::

    use Symfony\Component\Console\Tester\CommandTester;

    // ...
    public function testExecute(): void
    {
        // ...
        $commandTester = new CommandTester($command);

        // Equals to a user inputting "Test" and hitting ENTER
        $commandTester->setInputs(['Test']);

        // Equals to a user inputting "This", "That" and hitting ENTER
        // This can be used for answering two separated questions for instance
        $commandTester->setInputs(['This', 'That']);

        // For simulating a positive answer to a confirmation question, adding an
        // additional input saying "yes" will work
        $commandTester->setInputs(['yes']);

        $commandTester->execute(['command' => $command->getName()]);

        // $this->assertRegExp('/.../', $commandTester->getDisplay());
    }

By calling :method:`Symfony\\Component\\Console\\Tester\\CommandTester::setInputs`,
you imitate what the console would do internally with all user input through the CLI.
This method takes an array as only argument with, for each input that the command expects,
a string representing what the user would have typed.
This way you can test any user interaction (even complex ones) by passing the appropriate inputs.

.. note::

    The :class:`Symfony\\Component\\Console\\Tester\\CommandTester` automatically
    simulates a user hitting ``ENTER`` after each input, no need for passing
    an additional input.

.. caution::

    On Windows systems Symfony uses a special binary to implement hidden
    questions. This means that those questions don't use the default ``Input``
    console object and therefore you can't test them on Windows.
