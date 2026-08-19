Console Input (Arguments & Options)
===================================

The most interesting part of the commands are the arguments and options that
you can make available. These arguments and options allow you to pass dynamic
information from the terminal to the command.

Using Command Arguments
-----------------------

Arguments are the strings - separated by spaces - that
come after the command name itself. They are ordered, and can be optional
or required.

.. _console-input-arguments:

Using Arguments in Invokable Commands
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In :ref:`invokable commands <console_creating-command>`, use the
:class:`Symfony\\Component\\Console\\Attribute\\Argument` attribute
to define arguments directly in the ``__invoke()`` method parameters::

    // ...
    use Symfony\Component\Console\Attribute\Argument;
    use Symfony\Component\Console\Attribute\AsCommand;

    #[AsCommand(name: 'app:greet')]
    class GreetCommand
    {
        public function __invoke(
            // required argument (no default value)
            #[Argument]
            string $name,

            // optional argument (has default value)
            #[Argument]
            string $lastName = '',
        ): int {
            // ...
        }
    }

The ``Argument`` attribute accepts the following parameters:

``description``
    A description of the argument shown when displaying the command help.
    For example: ``#[Argument(description: 'Your username')]``. It also accepts
    a callable returning the string, such as
    ``#[Argument(description: [self::class, 'describeUsername'])]``.

    .. versionadded:: 8.2

        Support for callables in the ``description`` parameter was introduced
        in Symfony 8.2.

``name``
    The name displayed for the argument in the command help and used to retrieve
    the argument value via ``$input->getArgument('some-name')``. By default, it's
    the constructor parameter name converted to ``kebab-case`` (e.g. ``$lastName``
    becomes ``last-name``).

``suggestedValues``
    An array or a callable that provides :ref:`suggested values for the argument <console-input-completion>`.
    For example: ``#[Argument(suggestedValues: ['Alice', 'Bob'])]``.

The argument mode (required, optional, array) is inferred from the parameter type:

* **Required**: Parameters without a default value and not nullable (e.g. ``string $name``);
* **Optional**: Parameters with a default value (e.g. ``string $name = ''`` or ``?string $name = null``);
* **Array**: Parameters with the ``array`` type (e.g. ``array $names = []``);

You can also use ``BackedEnum`` types for arguments and options. The string
input is automatically converted to the corresponding enum case using
``BackedEnum::from()``, and autocompletion is provided based on the enum cases.

Given a backed enum like this::

    // src/Enum/Format.php
    namespace App\Enum;

    enum Format: string
    {
        case Json = 'json';
        case Csv = 'csv';
    }

You can type-hint it directly in your command::

    // ...
    use App\Enum\Format;
    use Symfony\Component\Console\Attribute\Argument;
    use Symfony\Component\Console\Attribute\AsCommand;

    #[AsCommand(name: 'app:export')]
    class ExportCommand
    {
        public function __invoke(
            #[Argument]
            Format $format,
        ): int {
            // $format is an instance of the Format enum
            // ...
        }
    }

If the user provides a value that doesn't match any enum case, an error
message is displayed along with the list of valid values.

Using the Classic configure() Method
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you prefer the classic approach, or need to extend the ``Command`` class,
you can use the ``addArgument()`` method in the ``configure()`` method.
For example, to add an optional ``last_name`` argument to the command
and make the ``name`` argument required::

    // ...
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;

    class GreetCommand extends Command
    {
        // ...

        protected function configure(): void
        {
            $this
                // ...
                ->addArgument('name', InputArgument::REQUIRED, 'Who do you want to greet?')
                ->addArgument('last_name', InputArgument::OPTIONAL, 'Your last name?')
            ;
        }
    }

You now have access to a ``last_name`` argument in your command::

    // ...
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    class GreetCommand extends Command
    {
        // ...

        protected function execute(InputInterface $input, OutputInterface $output): int
        {
            $text = 'Hi '.$input->getArgument('name');

            $lastName = $input->getArgument('last_name');
            if ($lastName) {
                $text .= ' '.$lastName;
            }

            $output->writeln($text.'!');

            return Command::SUCCESS;
        }
    }

The command can now be used in either of the following ways:

.. code-block:: terminal

    $ php bin/console app:greet Fabien
    Hi Fabien!

    $ php bin/console app:greet Fabien Potencier
    Hi Fabien Potencier!

It is also possible to let an argument take a list of values (imagine you want
to greet all your friends). Only the last argument can be a list::

    $this
        // ...
        ->addArgument(
            'names',
            InputArgument::IS_ARRAY,
            'Who do you want to greet (separate multiple names with a space)?'
        )
    ;

To use this, specify as many names as you want:

.. code-block:: terminal

    $ php bin/console app:greet Fabien Ryan Bernhard

You can access the ``names`` argument as an array::

    $names = $input->getArgument('names');
    if (count($names) > 0) {
        $text .= ' '.implode(', ', $names);
    }

There are three argument variants you can use:

``InputArgument::REQUIRED``
    The argument is mandatory. The command doesn't run if the argument isn't
    provided;

``InputArgument::OPTIONAL``
    The argument is optional and therefore can be omitted. This is the default
    behavior of arguments;

``InputArgument::IS_ARRAY``
    The argument can contain any number of values. For that reason, it must be
    used at the end of the argument list.

You can combine ``IS_ARRAY`` with ``REQUIRED`` or ``OPTIONAL`` like this::

    $this
        // ...
        ->addArgument(
            'names',
            InputArgument::IS_ARRAY | InputArgument::REQUIRED,
            'Who do you want to greet (separate multiple names with a space)?'
        )
    ;

.. deprecated:: 8.1

    Combining ``InputArgument::REQUIRED`` and ``InputArgument::OPTIONAL`` was
    deprecated in Symfony 8.1.

Using Command Options
---------------------

Unlike arguments, options are not ordered (meaning you can specify them in any
order) and are specified with two dashes (e.g. ``--yell``). Options are
*always* optional, and can be setup to accept a value (e.g. ``--dir=src``) or
as a boolean flag without a value (e.g.  ``--yell``).

.. _console-input-options:

Using Options in Invokable Commands
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

In :ref:`invokable commands <console_creating-command>`, use the
:class:`Symfony\\Component\\Console\\Attribute\\Option` attribute
to define options directly in the ``__invoke()`` method parameters::

    // ...
    use Symfony\Component\Console\Attribute\Argument;
    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Attribute\Option;

    #[AsCommand(name: 'app:greet')]
    class GreetCommand
    {
        public function __invoke(
            #[Argument]
            string $name,

            // option that accepts a value (--iterations=5)
            #[Option]
            int $iterations = 1,

            // boolean flag (--yell)
            #[Option]
            bool $yell = false,
        ): int {
            // ...
        }
    }

The ``Option`` attribute accepts the following parameters:

``description``
    A description of the option shown when displaying the command help.
    For example: ``#[Option(description: 'Number of iterations')]``. It also
    accepts a callable returning the string, such as
    ``#[Option(description: [self::class, 'describeIterations'])]``.

    .. versionadded:: 8.2

        Support for callables in the ``description`` parameter was introduced
        in Symfony 8.2.

``name``
    The name used when passing the option to the command (e.g. ``--max-retries=5``)
    and when retrieving its value via ``$input->getOption('some-name')``. By default,
    it's the constructor parameter name converted to ``kebab-case`` (e.g.
    ``$maxRetries`` becomes ``max-retries``).

``shortcut``
    A short alternative to the full option name (e.g. ``#[Option(shortcut: 'i')] $iterations``
    allows using ``-i`` instead of ``--iterations``). Multiple shortcuts can be
    defined in an array or using ``|`` as separator (e.g. ``shortcut: 'i|iter'``).

``suggestedValues``
    An array or a callable that provides :ref:`suggested values for the option <console-input-completion>`.
    For example: ``#[Option(suggestedValues: ['low', 'medium', 'high'])]``.

The option mode is inferred from the parameter type and default value:

* **Boolean flag** (``VALUE_NONE``): ``bool`` type with default ``false``.
  Usage: ``--yell`` sets the value to ``true``::

      #[Option] bool $yell = false

* **Negatable flag** (``VALUE_NEGATABLE``): ``bool`` type with default ``true`` or
  nullable ``?bool`` with default ``null``. Usage: ``--yell`` or ``--no-yell``::

      #[Option] bool $yell = true
      #[Option] ?bool $yell = null

* **Value required** (``VALUE_REQUIRED``): ``string``, ``int`` or ``float`` types::

      #[Option] string $format = 'json'
      #[Option] int $limit = 10
      #[Option] ?string $filter = null

* **Array of values** (``VALUE_IS_ARRAY``): ``array`` type.
  Usage: ``--role=ADMIN --role=USER``::

      #[Option(description: 'User roles')] array $roles = []
      #[Option] ?array $tags = null

* **Value optional** (``VALUE_OPTIONAL``): Union types ``string|bool``, ``int|bool``,
  or ``float|bool`` with default ``false``. Usage: ``--output`` (returns ``true``)
  or ``--output=file.txt`` (returns ``'file.txt'``)::

      #[Option] string|bool $output = false

* **BackedEnum** (``VALUE_REQUIRED``): ``BackedEnum`` types. The input value is
  automatically converted to the enum case, and autocompletion is provided
  (e.g. ``Format $format = Format::Json`` or ``?Format $format = null``).

.. seealso::

    The ``#[Option]`` attribute enforces validation rules on type and default
    value combinations. See :ref:`Option Attribute Constraints <console-option-constraints>`
    for the complete list of rules and examples.

Using Objects as Default Values
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. versionadded:: 8.1

    Support for objects as default values was introduced in Symfony 8.1.

You can use objects as default values for arguments and options in invokable
commands. This is useful when you need complex default values that are resolved
at runtime::

    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Attribute\Option;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Style\SymfonyStyle;

    #[AsCommand(name: 'app:report')]
    class ReportCommand
    {
        public function __invoke(
            SymfonyStyle $io,
            #[Option] \DateTimeImmutable $from = new \DateTimeImmutable('-1 week'),
            #[Option] \DateTimeImmutable $to = new \DateTimeImmutable(),
        ): int {
            $io->info(sprintf(
                'Generating report from %s to %s',
                $from->format('Y-m-d'),
                $to->format('Y-m-d'),
            ));

            return Command::SUCCESS;
        }
    }

.. note::

    In invokable commands, options must always have a default value. Object
    default values are especially useful in this context because they allow
    you to define complex, runtime-resolved defaults directly in the method
    signature.

Using the Classic addOption() Method
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you prefer the classic approach, or need to extend the ``Command`` class,
you can use the ``addOption()`` method in the ``configure()`` method.
For example, add a new option to the command that can be used to specify
how many times in a row the message should be printed::

    // ...
    use Symfony\Component\Console\Input\InputOption;

    $this
        // ...
        ->addOption(
            // this is the name that users must type to pass this option (e.g. --iterations=5)
            'iterations',
            // this is the optional shortcut of the option name, which usually is just a letter
            // (e.g. `i`, so users pass it as `-i`); use it for commonly used options
            // or options with long names
            null,
            // this is the type of option (e.g. requires a value, can be passed more than once, etc.)
            InputOption::VALUE_REQUIRED,
            // the option description displayed when showing the command help
            'How many times should the message be printed?',
            // the default value of the option (for those which allow you to pass values)
            1
        )
    ;

Next, use this in the command to print the message multiple times::

    for ($i = 0; $i < $input->getOption('iterations'); $i++) {
        $output->writeln($text);
    }

Now, when you run the command, you can optionally specify a ``--iterations``
flag:

.. code-block:: terminal

    # no --iterations provided, the default (1) is used
    $ php bin/console app:greet Fabien
    Hi Fabien!

    $ php bin/console app:greet Fabien --iterations=5
    Hi Fabien!
    Hi Fabien!
    Hi Fabien!
    Hi Fabien!
    Hi Fabien!

    # the order of options isn't important
    $ php bin/console app:greet Fabien --iterations=5 --yell
    $ php bin/console app:greet Fabien --yell --iterations=5
    $ php bin/console app:greet --yell --iterations=5 Fabien

.. tip::

    You can also declare a one-letter shortcut that you can call with a single
    dash, like ``-i``::

        $this
            // ...
            ->addOption(
                'iterations',
                'i',
                InputOption::VALUE_REQUIRED,
                'How many times should the message be printed?',
                1
            )
        ;

Note that to comply with the `docopt standard`_, long options can specify their
values after a whitespace or an ``=`` sign (e.g. ``--iterations 5`` or
``--iterations=5``), but short options can only use whitespaces or no
separation at all (e.g. ``-i 5`` or ``-i5``).

.. warning::

    While it is possible to separate an option from its value with a whitespace,
    using this form leads to an ambiguity should the option appear before the
    command name. For example, ``php bin/console --iterations 5 app:greet Fabien``
    is ambiguous; Symfony would interpret ``5`` as the command name. To avoid
    this situation, always place options after the command name, or avoid using
    a space to separate the option name from its value.

There are five option variants you can use:

``InputOption::VALUE_IS_ARRAY``
    This option accepts multiple values (e.g. ``--dir=/foo --dir=/bar``);

``InputOption::VALUE_NONE``
    Do not accept input for this option (e.g. ``--yell``). The value returned
    from is a boolean (``false`` if the option is not provided).
    This is the default behavior of options;

``InputOption::VALUE_REQUIRED``
    This value is required (e.g. ``--iterations=5`` or ``-i5``), the option
    itself is still optional;

``InputOption::VALUE_OPTIONAL``
    This option may or may not have a value (e.g. ``--yell`` or
    ``--yell=loud``).

``InputOption::VALUE_NEGATABLE``
    Accept either the flag (e.g. ``--yell``) or its negation (e.g.
    ``--no-yell``). You can set a boolean default value on negatable options::

        $this
            // ...
            ->addOption('yell', null, InputOption::VALUE_NEGATABLE, 'Whether to yell', false)
        ;

    .. versionadded:: 8.1

        The support for setting a boolean default value on
        ``InputOption::VALUE_NEGATABLE`` options (including when combined with
        ``InputOption::VALUE_NONE``) was introduced in Symfony 8.1.

You need to combine ``VALUE_IS_ARRAY`` with ``VALUE_REQUIRED`` or
``VALUE_OPTIONAL`` like this::

    $this
        // ...
        ->addOption(
            'colors',
            null,
            InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
            'Which colors do you like?',
            ['blue', 'red']
        )
    ;

.. deprecated:: 8.1

    Using any combination of ``InputOption::VALUE_NONE``, ``InputOption::VALUE_REQUIRED`` and
    ``InputOption::VALUE_OPTIONAL`` was deprecated in Symfony 8.1.

.. _console-input-map-input:

Mapping Input to Objects
------------------------

When a command has many arguments and options, the ``__invoke()`` method can
become cluttered. To better organize the input, you can use the
:class:`Symfony\\Component\\Console\\Attribute\\MapInput` attribute to group
arguments and options into a dedicated class (a Data Transfer Object, or DTO)::

    // src/Console/Input/CreateUserInput.php
    namespace App\Console\Input;

    use Symfony\Component\Console\Attribute\Argument;
    use Symfony\Component\Console\Attribute\Option;

    class CreateUserInput
    {
        #[Argument]
        public string $email;

        #[Argument]
        public string $password;

        #[Option]
        public bool $admin = false;
    }

Then, use the ``#[MapInput]`` attribute in your command to receive this DTO::

    // src/Console/CreateUserCommand.php
    namespace App\Console;

    use App\Console\Input\CreateUserInput;
    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Attribute\MapInput;

    #[AsCommand(name: 'app:create-user', description: 'Creates a new user')]
    class CreateUserCommand
    {
        public function __invoke(#[MapInput] CreateUserInput $input): int
        {
            // access input values as object properties
            $email = $input->email;
            $password = $input->password;
            $isAdmin = $input->admin;

            // ...

            return 0;
        }
    }

The DTO class must have at least one public property with an ``#[Argument]``
or ``#[Option]`` attribute. Private, protected, and static properties are
ignored. The same rules for argument and option types described earlier in
this article apply to DTO properties.

.. note::

    DTOs are instantiated without calling their constructor and values are
    assigned directly to public properties. This means any logic in the
    constructor (such as initialization) will not run. If you need to
    transform or validate input values, use :ref:`property hooks <console-input-dto-property-hooks>` instead.

Nesting Input DTOs
~~~~~~~~~~~~~~~~~~

You can compose input classes by nesting DTOs. This is useful when you want
to group related arguments and options, or reuse common input definitions
across multiple commands::

    // src/Console/Input/PaginationInput.php
    namespace App\Console\Input;

    use Symfony\Component\Console\Attribute\Option;

    class PaginationInput
    {
        #[Option(description: 'Number of items per page')]
        public int $limit = 10;

        #[Option(description: 'Page number')]
        public int $page = 1;
    }

    // src/Console/Input/ListUsersInput.php
    namespace App\Console\Input;

    use Symfony\Component\Console\Attribute\MapInput;
    use Symfony\Component\Console\Attribute\Option;

    class ListUsersInput
    {
        #[Option(description: 'Filter by role')]
        public ?string $role = null;

        #[MapInput]
        public PaginationInput $pagination;
    }

Then, access nested properties in your command::

    #[AsCommand(name: 'app:list-users')]
    class ListUsersCommand
    {
        public function __invoke(#[MapInput] ListUsersInput $input): int
        {
            $role = $input->role;
            $limit = $input->pagination->limit;
            $page = $input->pagination->page;

            // ...

            return 0;
        }
    }

.. _console-input-dto-property-hooks:

Using Property Hooks for Normalization
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

PHP provides `property hooks`_, which you can use to normalize input values as
they are assigned to the DTO::

    class CreateUserInput
    {
        #[Argument]
        public string $email {
            set(string $value) {
                $this->email = strtolower(trim($value));
            }
        }

        #[Option]
        public array $roles = [] {
            set(array $value) {
                $this->roles = array_map('strtoupper', $value);
            }
        }
    }


With this setup, when the command input is resolved, the email is lowercased
and trimmed, and roles are uppercased.

Validating Input DTOs with Constraints
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When the :doc:`Validator component </validation>` is available, you can add
validation constraints directly to DTO properties. They are automatically
enforced after the input is resolved::

    // src/Console/Input/CreateUserInput.php
    namespace App\Console\Input;

    use Symfony\Component\Console\Attribute\Argument;
    use Symfony\Component\Console\Attribute\Option;
    use Symfony\Component\Validator\Constraints\Email;
    use Symfony\Component\Validator\Constraints\NotBlank;

    class CreateUserInput
    {
        #[Argument]
        #[NotBlank]
        public string $name;

        #[Option]
        #[Email]
        public ?string $email = null;
    }

If any constraint is violated, an
:class:`Symfony\\Component\\Console\\Exception\\InputValidationFailedException`
is thrown with a message listing all violations::

    #[AsCommand(name: 'app:create-user')]
    class CreateUserCommand
    {
        public function __invoke(#[MapInput] CreateUserInput $input): int
        {
            // $input is already validated at this point
            // ...

            return 0;
        }
    }

You can also specify validation groups via the ``validationGroups`` option of
the ``#[MapInput]`` attribute::

    public function __invoke(
        #[MapInput(validationGroups: ['registration'])]
        CreateUserInput $input,
    ): int {
        // ...
    }

.. note::

    When the Validator component is not installed, constraints on DTO
    properties are silently ignored.

.. versionadded:: 8.1

    Validation constraints support for ``#[MapInput]`` was introduced in
    Symfony 8.1.

Validating Input with Constraints
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can validate interactive input by passing
:doc:`Validator constraints </reference/constraints>` to the ``constraints``
option of the ``#[Ask]`` attribute::

    #[Argument]
    #[Ask(constraints: [new Assert\NotBlank, new Assert\Email])]
    string $email,

When the user provides an invalid value, the validation error messages are
displayed in the console and the user is prompted again until valid input is
given.

.. versionadded:: 8.1

    The ``constraints`` option of the ``#[Ask]`` attribute was introduced
    in Symfony 8.1.

.. seealso::

    For more details on validating console questions, see
    :ref:`Validating the Answer <console-validate-question-answer>` in
    the QuestionHelper documentation.

.. _console-interactive-input:

Interactive Input
-----------------

In :ref:`invokable commands <console_creating-command>`, you can use the
:class:`Symfony\\Component\\Console\\Attribute\\Ask` attribute to prompt
users for missing values during the interactive phase, without writing a
custom ``interact()`` method.

Asking for Simple Values
~~~~~~~~~~~~~~~~~~~~~~~~

Add the ``#[Ask]`` attribute to an ``__invoke()`` parameter alongside
``#[Argument]``::

    use Symfony\Component\Console\Attribute\Argument;
    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Attribute\Ask;
    use Symfony\Component\Console\Command\Command;

    #[AsCommand(name: 'app:create-user')]
    class CreateUserCommand
    {
        public function __invoke(
            #[Argument]
            #[Ask('Enter the username')]
            string $username,
        ): int {
            // ...

            return Command::SUCCESS;
        }
    }

When the ``username`` argument is not provided, the user is prompted:

.. code-block:: terminal

    $ php bin/console app:create-user

    Enter the username:
    > johndoe

Use the ``hidden`` option to mask the user's input (e.g. for passwords)::

    public function __invoke(
        #[Argument]
        #[Ask('Enter the password', hidden: true)]
        string $password,
    ): int {
        // ...
    }


When the parameter type is ``bool``, the ``#[Ask]`` attribute automatically
uses a yes/no confirmation question::

    public function __invoke(
        #[Argument]
        #[Ask('Do you want to activate the user?')]
        bool $active,
    ): int {
        // ...
    }

.. tip::

    The ``#[Ask]`` attribute defines many options: ``timeout`` (int, default ``null``)
    (set the max. seconds to wait for an answer), ``maxAttempts`` (int, default: ``null``,
    means unlimited), ``multiline`` (bool, default: ``false`` to allow newlines in responses), etc.

.. note::

    Interactive attributes (``#[Ask]``, ``#[Interact]``) can only be used
    with required console arguments. Using them with options or optional
    arguments is not supported and will raise an exception.

Using ``#[Ask]`` on DTO Properties
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The ``#[Ask]`` attribute also works on properties of
:ref:`input DTOs <console-input-map-input>`::

    use Symfony\Component\Console\Attribute\Argument;
    use Symfony\Component\Console\Attribute\Ask;

    class CreateUserInput
    {
        #[Argument]
        #[Ask('Enter the username')]
        public string $username;

        #[Argument]
        #[Ask('Enter the password', hidden: true)]
        public string $password;
    }

Then use it in your command with ``#[MapInput]``::

    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Attribute\MapInput;
    use Symfony\Component\Console\Command\Command;

    #[AsCommand(name: 'app:create-user')]
    class CreateUserCommand
    {
        public function __invoke(#[MapInput] CreateUserInput $input): int
        {
            // use $input->username and $input->password

            return Command::SUCCESS;
        }
    }

.. _console-interact-attribute:

Custom Interactive Logic with ``#[Interact]``
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

For scenarios that go beyond simple prompts, use the
:class:`Symfony\\Component\\Console\\Attribute\\Interact` attribute to mark
a method that will be called during the interactive phase::

    use Symfony\Component\Console\Attribute\Argument;
    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Attribute\Ask;
    use Symfony\Component\Console\Attribute\Interact;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Style\SymfonyStyle;

    #[AsCommand(name: 'app:create-user')]
    class CreateUserCommand
    {
        #[Interact]
        public function prompt(SymfonyStyle $io): void
        {
            // custom interactive logic
        }

        public function __invoke(
            #[Argument]
            #[Ask('Enter the username')]
            string $username,
        ): int {
            // ...

            return Command::SUCCESS;
        }
    }

The method marked with ``#[Interact]`` must be public and non-static. It
supports the same dependency-injected parameters as ``__invoke()`` (e.g.
``SymfonyStyle``, ``InputInterface``).

You can also use ``#[Interact]`` on a DTO class to handle interaction
logic related to the DTO's own properties::

    use Symfony\Component\Console\Attribute\Argument;
    use Symfony\Component\Console\Attribute\Ask;
    use Symfony\Component\Console\Attribute\Interact;
    use Symfony\Component\Console\Style\SymfonyStyle;

    class CreateUserInput
    {
        #[Argument]
        #[Ask('Enter the username')]
        public string $username;

        #[Argument]
        #[Ask('Enter the password (or press Enter for a random one)', hidden: true)]
        public string $password;

        #[Interact]
        public function prompt(SymfonyStyle $io): void
        {
            if (!isset($this->password)) {
                $this->password = bin2hex(random_bytes(10));
                $io->writeln('Password generated: '.$this->password);
            }
        }
    }

When both ``#[Ask]`` and ``#[Interact]`` are used, they run in the
following order during the interactive phase:

#. ``#[Ask]`` on ``__invoke()`` parameters
#. ``#[Ask]`` on DTO properties
#. ``#[Interact]`` on the DTO class
#. ``#[Interact]`` on the command class

.. note::

    Interactive prompts only run when the command is executed in interactive
    mode. They are skipped when using the ``--no-interaction`` (``-n``) option.

.. _console-input-ask-choice:

Asking Choice Questions
-----------------------

.. versionadded:: 8.1

    The ``#[AskChoice]`` attribute was introduced in Symfony 8.1.

The :class:`Symfony\\Component\\Console\\Attribute\\AskChoice` attribute prompts
the user to select a value from a predefined list of choices. You can use it on
both ``__invoke()`` arguments and DTO properties (via ``#[MapInput]``)::

    use Symfony\Component\Console\Attribute\Argument;
    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Attribute\AskChoice;

    #[AsCommand(name: 'app:create-user')]
    class CreateUserCommand
    {
        public function __invoke(
            #[Argument]
            #[AskChoice('Select a role', ['admin', 'editor', 'viewer'])]
            string $role,
        ): int {
            // $role will be one of: 'admin', 'editor', 'viewer'

            return Command::SUCCESS;
        }
    }

If the user doesn't provide a value for this, it renders the following interactive prompt:

.. code-block:: terminal

    Select a role:
     [0] admin
     [1] editor
     [2] viewer
    >

When the parameter type is a ``BackedEnum``, choices are automatically derived
from the enum cases, so you don't need to pass them explicitly::

    enum Role: string
    {
        case Admin = 'admin';
        case Editor = 'editor';
        case Viewer = 'viewer';
    }

    #[AsCommand(name: 'app:create-user')]
    class CreateUserCommand
    {
        public function __invoke(
            #[Argument]
            #[AskChoice('Select a role')]
            Role $role,
        ): int {
            // ...
        }
    }

When the parameter type is ``array``, multi-select is automatically enabled,
allowing the user to select multiple values::

    #[AsCommand(name: 'app:setup')]
    class SetupCommand
    {
        public function __invoke(
            #[Argument]
            #[AskChoice('Select features', ['auth', 'api', 'cache'])]
            array $features,
        ): int {
            // $features will contain the selected values
        }
    }

.. note::

    You can use the ``default`` option of ``#[AskChoice]`` to set the value when
    the user presses ``Enter`` without selecting any value. However, you can not
    set an array of values and therefore cannot set a default value for multi-select
    choices. This is a limitation of the underlying ``ChoiceQuestion`` class.

.. _console-input-file:

File Input
----------

.. versionadded:: 8.1

    File input support in invokable commands was introduced in Symfony 8.1.

You can ask the user for a file by type-hinting a parameter as
:class:`Symfony\\Component\\Console\\Input\\File\\InputFile` and combining the
``#[Argument]`` and ``#[Ask]`` attributes. The console will automatically prompt
for file input (paste or path)::

    use Symfony\Component\Console\Attribute\Argument;
    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Attribute\Ask;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Style\SymfonyStyle;

    // ...

    #[AsCommand(name: 'app:analyze')]
    class AnalyzeCommand
    {
        public function __invoke(
            SymfonyStyle $io,
            #[Argument(description: 'The image to analyze')]
            #[Ask('Provide an image (paste or enter path):')]
            InputFile $image,
        ): int {
            $io->writeln('MIME type: '.$image->getMimeType());
            $io->writeln('Size: '.$image->getHumanReadableSize());

            if ($image->isTempFile()) {
                $image->move('/path/to/storage', 'image.png');
            }

            return Command::SUCCESS;
        }
    }

In terminals that support image protocols (such as Kitty, Ghostty, iTerm2,
WezTerm, etc.), users can paste images directly from their clipboard. In other
terminals, the question falls back to asking for a file path. In non-interactive
mode, file paths are accepted as regular arguments.

.. seealso::

    For more advanced options (e.g. restricting allowed MIME types or file
    size), see the ``FileQuestion`` class in the
    :doc:`QuestionHelper documentation </components/console/helpers/questionhelper>`.

Options with optional arguments
-------------------------------

There is nothing forbidding you to create a command with an option that
optionally accepts a value, but it's a bit tricky. Consider this example::

    // ...
    use Symfony\Component\Console\Input\InputOption;

    $this
        // ...
        ->addOption(
            'yell',
            null,
            InputOption::VALUE_OPTIONAL,
            'Should I yell while greeting?'
        )
    ;

This option can be used in 3 ways: ``greet --yell``, ``greet --yell=louder``,
and ``greet``. However, it's hard to distinguish between passing the option
without a value (``greet --yell``) and not passing the option (``greet``).

To solve this issue, you have to set the option's default value to ``false``::

    // ...
    use Symfony\Component\Console\Input\InputOption;

    $this
        // ...
        ->addOption(
            'yell',
            null,
            InputOption::VALUE_OPTIONAL,
            'Should I yell while greeting?',
            false // this is the new default value, instead of null
        )
    ;

Now it's possible to differentiate between not passing the option and not
passing any value for it::

    $optionValue = $input->getOption('yell');
    if (false === $optionValue) {
        // in this case, the option was not passed when running the command
        $yell = false;
        $yellLouder = false;
    } elseif (null === $optionValue) {
        // in this case, the option was passed when running the command
        // but no value was given to it
        $yell = true;
        $yellLouder = false;
    } else {
        // in this case, the option was passed when running the command and
        // some specific value was given to it
        $yell = true;
        if ('louder' === $optionValue) {
            $yellLouder = true;
        } else {
            $yellLouder = false;
        }
    }

The above code can be simplified as follows because ``false !== null``::

    $optionValue = $input->getOption('yell');
    $yell = ($optionValue !== false);
    $yellLouder = ($optionValue === 'louder');

Fetching The Raw Command Input
------------------------------

Symfony provides a :method:`Symfony\\Component\\Console\\Input\\ArgvInput::getRawTokens`
method to fetch the raw input that was passed to the command. This is useful if
you want to parse the input yourself or when you need to pass the input to another
command without having to worry about the number of arguments or options::

    // ...
    use Symfony\Component\Process\Process;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // if this command was run as:
        // php bin/console app:my-command foo --bar --baz=3 --qux=value1 --qux=value2

        $tokens = $input->getRawTokens();
        // $tokens = ['app:my-command', 'foo', '--bar', '--baz=3', '--qux=value1', '--qux=value2'];

        // pass true as argument to not include the original command name
        $tokens = $input->getRawTokens(true);
        // $tokens = ['foo', '--bar', '--baz=3', '--qux=value1', '--qux=value2'];

        // pass the raw input to any other command (from Symfony or the operating system)
        $process = new Process(['app:other-command', ...$input->getRawTokens(true)]);
        $process->setTty(true);
        $process->mustRun();

        // ...
    }

Fetching the Raw Arguments and Options
--------------------------------------

.. versionadded:: 8.1

    The ``RawInputInterface`` was introduced in Symfony 8.1.

While ``getRawTokens()`` returns the unparsed CLI tokens as strings,
:class:`Symfony\\Component\\Console\\Input\\RawInputInterface` exposes the
parsed arguments and options as they were explicitly passed by the user, without
default values merged in. This is useful to forward the current command's input
to a child process:

* ``getRawArguments()`` and ``getRawOptions()`` return only the arguments
  and options explicitly passed by the user (default values excluded);
* ``unparse()`` converts the parsed options back to their CLI string form
  (e.g. ``['--format=json', '--verbose']``), ready to spread into a
  ``Process`` call. Pass an array of option names to restrict the output.

Type-hint ``RawInputInterface`` in an invokable command to use these methods::

    use Symfony\Component\Console\Input\RawInputInterface;
    use Symfony\Component\Process\Process;
    // ...

    public function __invoke(RawInputInterface $input): int
    {
        $rawArguments = $input->getRawArguments();
        $rawOptions = $input->getRawOptions();

        $process = new Process([
            'app:child-command',
            ...$input->getRawArguments(),
            ...$input->unparse(),
        ]);
        $process->mustRun();

        // limit unparse() to specific options
        $unparsed = $input->unparse(['format', 'verbose']);

        // ...
    }

.. _console-input-completion:

Adding Argument/Option Value Completion
---------------------------------------

If :ref:`Console completion is installed <console-completion-setup>`,
command and option names will be auto completed by the shell. However, you
can also implement value completion for the input in your commands. For
instance, you may want to complete all usernames from the database in the
``name`` argument of your greet command.

Using Completion with Attributes
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When using the ``#[Argument]`` or ``#[Option]`` attributes in invokable commands,
use the ``suggestedValues`` parameter to provide completion values::

    use Symfony\Component\Console\Attribute\Argument;
    use Symfony\Component\Console\Attribute\Option;
    use Symfony\Component\Console\Completion\CompletionInput;

    public function __invoke(
        // static list of suggested values
        #[Argument(suggestedValues: ['Alice', 'Bob', 'Charlie'])]
        string $name,

        // dynamic values via a callable (method reference)
        #[Option(suggestedValues: [self::class, 'suggestFormats'])]
        string $format = 'json',
    ): int {
        // ...
    }

    public static function suggestFormats(CompletionInput $input): array
    {
        return ['json', 'xml', 'csv'];
    }

.. note::

    You can remove the ``static`` keyword from the suggestion method to access
    instance properties. In that case, the command will call the method
    non-statically, allowing you to return dynamic values based on services
    injected through the constructor::

        public function __construct(
            private UserRepository $userRepository,
        ) {
        }

        // ...

        public function suggestUsers(CompletionInput $input): array
        {
            return $this->userRepository->findAllUsernames();
        }

Using Completion with addArgument()/addOption()
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

When using the classic ``configure()`` method, use the 5th argument
of ``addArgument()`` or the 6th argument of ``addOption()``::

    // ...
    use Symfony\Component\Console\Completion\CompletionInput;
    use Symfony\Component\Console\Completion\CompletionSuggestions;

    class GreetCommand extends Command
    {
        // ...
        protected function configure(): void
        {
            $this
                ->addArgument(
                    'names',
                    InputArgument::IS_ARRAY,
                    'Who do you want to greet (separate multiple names with a space)?',
                    null,
                    function (CompletionInput $input): array {
                        // the value the user already typed, e.g. when typing "app:greet Fa" before
                        // pressing Tab, this will contain "Fa"
                        $currentValue = $input->getCompletionValue();

                        // get the list of username names from somewhere (e.g. the database)
                        // you may use $currentValue to filter down the names
                        $availableUsernames = ...;

                        // then suggested the usernames as values
                        return $availableUsernames;
                    }
                )
            ;
        }
    }

That's all you need! Assuming users "Fabien" and "Fabrice" exist, pressing
tab after typing ``app:greet Fa`` will give you these names as a suggestion.

.. tip::

    The shell script is able to handle huge amounts of suggestions and will
    automatically filter the suggested values based on the existing input
    from the user. You do not have to implement any filter logic in the
    command.

    You may use ``CompletionInput::getCompletionValue()`` to get the
    current input if that helps improving performance (e.g. by reducing the
    number of rows fetched from the database).

Testing the Completion script
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The Console component comes with a special
:class:`Symfony\\Component\\Console\\Tester\\CommandCompletionTester` class
to help you unit test the completion logic::

    // ...
    use Symfony\Component\Console\Application;

    class GreetCommandTest extends TestCase
    {
        public function testComplete(): void
        {
            $application = new Application();
            $application->add(new GreetCommand());

            // create a new tester with the greet command
            $tester = new CommandCompletionTester($application->get('app:greet'));

            // complete the input without any existing input (the empty string represents
            // the position of the cursor)
            $suggestions = $tester->complete(['']);
            $this->assertSame(['Fabien', 'Fabrice', 'Wouter'], $suggestions);

            // If you filter the values inside your own code (not recommended, unless you
            // need to improve performance of e.g. a database query), you can test this
            // by passing the user input
            $suggestions = $tester->complete(['Fa']);
            $this->assertSame(['Fabien', 'Fabrice'], $suggestions);
        }
    }

.. _console-global-options:

Command Global Options
----------------------

The Console component adds some predefined options to all commands:

* ``--verbose``: sets the verbosity level (e.g. ``1`` the default, ``2`` and
  ``3``, or you can use respective shortcuts ``-v``, ``-vv`` and ``-vvv``)
* ``--silent``: disables all output and interaction, including errors
* ``--quiet|-q``: disables output and interaction, but errors are still displayed
* ``--no-interaction|-n``: disables interaction
* ``--version|-V``: outputs the version number of the console application
* ``--help|-h``: displays the command help
* ``--ansi|--no-ansi``: whether to force or disable coloring the output
* ``--profile``: enables the Symfony profiler

When using the ``FrameworkBundle``, two more options are predefined:

* ``--env|-e``: sets the Kernel configuration environment (defaults to ``APP_ENV``)
* ``--no-debug``: disables Kernel debug (defaults to ``APP_DEBUG``)

So your custom commands can use them too out-of-the-box.

.. _`docopt standard`: http://docopt.org/
.. _`property hooks`: https://php.net/language.oop5.property-hooks.php
