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
    For example: ``#[Argument(description: 'Your username')]``.

``name``
    The name displayed for the argument in the command help. By default, it's
    the parameter name converted to ``kebab-case`` (e.g. ``$lastName``
    becomes ``last-name``).

``suggestedValues``
    An array or a callable that provides :ref:`suggested values for the argument <console-input-completion>`.
    For example: ``#[Argument(suggestedValues: ['Alice', 'Bob'])]``.

.. versionadded:: 7.3

    The ``#[Argument]`` and ``#[Option]`` attributes were introduced in Symfony 7.3.

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

.. versionadded:: 7.4

    Support for ``BackedEnum`` in ``#[Argument]`` and ``#[Option]`` was
    introduced in Symfony 7.4.

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
    For example: ``#[Option(description: 'Number of iterations')]``.

``name``
    The name used when passing the option to the command (e.g. ``--max-retries=5``).
    By default, it's the parameter name converted to ``kebab-case`` (e.g.
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

.. _console-option-constraints:

The ``#[Option]`` attribute enforces the following rules on the parameter type
and its default value:

* Options **must always have a default value**. Unlike arguments, options cannot
  be required because users may not provide them;
* Nullable bool options (``?bool``) cannot have a ``true`` or ``false`` default.
  Use ``null`` as the default to enable the negatable behavior;
* Nullable non-bool options (e.g. ``?string``) must have ``null`` as the default value;
* Union types are only allowed for ``string|bool``, ``int|bool`` and ``float|bool``,
  and must have ``false`` as the default value.

Examples of valid option definitions::

    public function __invoke(
        #[Option] bool $verbose = false,          // VALUE_NONE
        #[Option] bool $colors = true,            // VALUE_NEGATABLE (--colors or --no-colors)
        #[Option] ?bool $debug = null,            // VALUE_NEGATABLE (--debug or --no-debug)
        #[Option] string $format = 'json',        // VALUE_REQUIRED
        #[Option] ?string $filter = null,         // VALUE_REQUIRED (optional value)
        #[Option] int $limit = 10,                // VALUE_REQUIRED
        #[Option] array $roles = [],              // VALUE_IS_ARRAY
        #[Option] string|bool $output = false,    // VALUE_OPTIONAL (--output or --output=file.txt)
    ): int {
        // ...
    }

Examples of **invalid** option definitions::

    public function __invoke(
        #[Option] string $format,                 // ERROR: no default value
        #[Option] ?bool $debug = true,            // ERROR: nullable bool with true default
        #[Option] ?string $filter = 'default',    // ERROR: nullable with non-null default
        #[Option] string|bool $output = true,     // ERROR: union type with true default
        #[Option] array|bool $items = false,      // ERROR: unsupported union type
    ): int {
        // ...
    }

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
    ``--no-yell``).

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

.. versionadded:: 7.4

    The ``#[MapInput]`` attribute was introduced in Symfony 7.4.

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

.. _console-interactive-input:

Interactive Input
-----------------

.. versionadded:: 7.4

    The ``#[Ask]`` and ``#[Interact]`` attributes were introduced in Symfony 7.4.

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

.. _options-with-optional-arguments:

Options with Optional Values
----------------------------

An option can accept a value optionally. Define it with a union type such as
``string|bool`` and ``false`` as the default value::

    // ...
    public function __invoke(
        #[Option(description: 'Should I yell while greeting?')]
        string|bool $yell = false,
    ): int {
        // ...
    }

This option can be used in three ways and the parameter receives a different
value in each case:

* ``greet``: the option was not passed, so ``$yell`` is ``false``;
* ``greet --yell``: the option was passed without a value, so ``$yell`` is ``true``;
* ``greet --yell=louder``: the option was passed with a value, so ``$yell`` is
  the string ``'louder'``.

.. note::

    When using the classic ``addOption()`` method, define the option with the
    ``InputOption::VALUE_OPTIONAL`` mode and ``false`` as its default value.
    Then, ``$input->getOption('yell')`` returns ``false`` when the option is not
    passed, ``null`` when it's passed without a value and the string otherwise.

.. _console-input-parsing:

How Arguments and Options Are Parsed
------------------------------------

Symfony Console applications follow the same `docopt standard`_ used in most
CLI utility tools. This section explains how the input is parsed in edge cases,
such as when a command defines options with required values, optional values,
etc. Have a look at the following command that defines three options::

    namespace App\Command;

    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Attribute\Option;

    #[AsCommand(name: 'demo:args', description: 'Describe args behaviors')]
    class DemoArgsCommand
    {
        public function __invoke(
            // option without a value
            #[Option(shortcut: 'f')] bool $foo = false,
            // option with a required value
            #[Option(shortcut: 'b')] string $bar = '',
            // option with an optional value
            #[Option(shortcut: 'c')] string|bool $cat = false,
        ): int {
            // ...
        }
    }

Since the ``foo`` option doesn't accept a value, it will be either ``false``
(when it is not passed to the command) or ``true`` (when ``--foo`` was passed
by the user). The value of the ``bar`` option (and its ``b`` shortcut respectively)
is required. It can be separated from the option name either by spaces or
``=`` characters. The ``cat`` option (and its ``c`` shortcut) behaves similar
except that it doesn't require a value. Have a look at the following table
to get an overview of the possible ways to pass options:

=====================  =========  ============  ============
Input                  ``foo``    ``bar``       ``cat``
=====================  =========  ============  ============
``--bar=Hello``        ``false``  ``"Hello"``   ``false``
``--bar Hello``        ``false``  ``"Hello"``   ``false``
``-b=Hello``           ``false``  ``"=Hello"``  ``false``
``-b Hello``           ``false``  ``"Hello"``   ``false``
``-bHello``            ``false``  ``"Hello"``   ``false``
``-fcWorld -b Hello``  ``true``   ``"Hello"``   ``"World"``
``-cfWorld -b Hello``  ``false``  ``"Hello"``   ``"fWorld"``
``-cbWorld``           ``false``  ``""``        ``"bWorld"``
=====================  =========  ============  ============

Things get a little bit more tricky when the command also accepts an optional
argument::

    // ...
    public function __invoke(
        // ...
        #[Argument] ?string $arg = null,
    ): int {
        // ...
    }

You might have to use the special ``--`` separator to separate options from
arguments. Have a look at the fifth example in the following table where it
is used to tell the command that ``World`` is the value for ``arg`` and not
the value of the optional ``cat`` option:

==============================  =================  ===========  ===========
Input                           ``bar``            ``cat``      ``arg``
==============================  =================  ===========  ===========
``--bar Hello``                 ``"Hello"``        ``false``    ``null``
``--bar Hello World``           ``"Hello"``        ``false``    ``"World"``
``--bar "Hello World"``         ``"Hello World"``  ``false``    ``null``
``--bar Hello --cat World``     ``"Hello"``        ``"World"``  ``null``
``--bar Hello --cat -- World``  ``"Hello"``        ``true``     ``"World"``
``-b Hello -c World``           ``"Hello"``        ``"World"``  ``null``
==============================  =================  ===========  ===========

Fetching The Raw Command Input
------------------------------

Symfony provides a :method:`Symfony\\Component\\Console\\Input\\ArgvInput::getRawTokens`
method to fetch the raw input that was passed to the command. This is useful if
you want to parse the input yourself or when you need to pass the input to another
command without having to worry about the number of arguments or options::

    // ...
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Process\Process;

    public function __invoke(InputInterface $input): int
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

.. versionadded:: 7.1

    The :method:`Symfony\\Component\\Console\\Input\\ArgvInput::getRawTokens`
    method was introduced in Symfony 7.1.

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
            $application->addCommand(new GreetCommand());

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

.. versionadded:: 7.2

    The ``--silent`` option was introduced in Symfony 7.2.

When using the ``FrameworkBundle``, two more options are predefined:

* ``--env|-e``: sets the Kernel configuration environment (defaults to ``APP_ENV``)
* ``--no-debug``: disables Kernel debug (defaults to ``APP_DEBUG``)

So your custom commands can use them too out-of-the-box.

.. _`docopt standard`: http://docopt.org/
.. _`property hooks`: https://php.net/language.oop5.property-hooks.php
