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
        string $default = null
    )

The ``$validator`` is a callback which handles the validation. It should
throw an exception if there is something wrong. The exception message is displayed
in the console, so it is a good practice to put some useful information in it. The callback 
function should also return the value of the user's input if the validation was successful.

You can set the max number of times to ask in the ``$attempts`` argument.
If you reach this max number it will use the default value, which is given
in the last argument. Using ``false`` means the amount of attempts is infinite.
The user will be asked as long as he provides an invalid answer and will only
be able to proceed if her input is valid.

Testing a command which expects input
-------------------------------------

If you want to write a unit test for a command which expects some kind of input
from the command line, you need to overwrite the HelperSet used by the command::

    use Symfony\Component\Console\Helper\DialogHelper;
    use Symfony\Component\Console\Helper\HelperSet;
    
    // ...
    
    public function testExecute()
    {
    
        // ..

        $commandTester = new CommandTester($command);
        
        $dialog = new DialogHelper();
        $dialog->setInputStream($this->getInputStream('Test\n')); 
        // Equals to a user inputing "Test" and hitting ENTER
        // If you need to enter a confirmation, "yes\n" will work
        
        $command->setHelperSet(new HelperSet(array($dialog)));
        
        $commandTester->execute(array('command' => $command->getName()));
    
        // assert
        
    }
    
    protected function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);

        return $stream;
    }
    
By setting the inputStream of the `DialogHelper`, you do the same the
console would do internally with all user input through the cli. This way
you can test any user interaction (even complex ones) by passing an appropriate
input stream.