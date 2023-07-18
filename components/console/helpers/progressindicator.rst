Progress Indicator
==================

When executing longer-running commands without knowing if the the processing
is nearly done or not, it may be helpful to show that something is actually
happening and that updates as your command runs.

To do so, use the
:class:`Symfony\\Component\\Console\\Helper\\ProgressIndicator` and advance the
progress as the command executes::

    use Symfony\Component\Console\Helper\ProgressIndicator;

    // creates a new progress indicator
    $progressIndicator = new ProgressIndicator($output);

    // starts and displays the progress indicator with a custom message
    $progressIndicator->start('Processing...');

    $i = 0;
    while ($i++ < 50) {
        // ... do some work

        // advances the progress indicator
        $progressIndicator->advance();
    }

    // ensures that the progress indicator shows a final message
    $progressIndicator->finish('Finished');

Customizing the Progress Indicator
----------------------------------

Built-in Formats
~~~~~~~~~~~~~~~~

By default, the information rendered on a progress indicator depends on the current
level of verbosity of the ``OutputInterface`` instance:

.. code-block:: text

    # OutputInterface::VERBOSITY_NORMAL (CLI with no verbosity flag)
     \ Processing...
     | Processing...
     / Processing...
     - Processing...

    # OutputInterface::VERBOSITY_VERBOSE (-v)
     \ Processing... (1 sec)
     | Processing... (1 sec)
     / Processing... (1 sec)
     - Processing... (1 sec)

    # OutputInterface::VERBOSITY_VERY_VERBOSE (-vv) and OutputInterface::VERBOSITY_DEBUG (-vvv)
     \ Processing... (1 sec, 6.0 MiB)
     | Processing... (1 sec, 6.0 MiB)
     / Processing... (1 sec, 6.0 MiB)
     - Processing... (1 sec, 6.0 MiB)

.. note::

    If you call a command with the quiet flag (``-q``), the progress indicator won't
    be displayed.

Instead of relying on the verbosity mode of the current command, you can also
force a format via the second argument of the ``ProgressIndicator``
constructor::

    $progressIndicator = new ProgressIndicator($output, 'verbose');

The built-in formats are the following:

* ``normal``
* ``verbose``
* ``very_verbose``

If your terminal doesn't support ANSI, use the ``no_ansi`` variants:

* ``normal_no_ansi``
* ``verbose_no_ansi``
* ``very_verbose_no_ansi``

Custom Indicator Values
~~~~~~~~~~~~~~~~~~~~~~~

Instead of using the built-in indicator values, you can also set your own::

    $progressIndicator = new ProgressIndicator($output, 'verbose', 100, ['⠏', '⠛', '⠹', '⢸', '⣰', '⣤', '⣆', '⡇']);

The progress indicator will now look like this:

.. code-block:: text

     ⠏ Processing...
     ⠛ Processing...
     ⠹ Processing...
     ⢸ Processing...

Customize Placeholders
~~~~~~~~~~~~~~~~~~~~~~

A progress indicator uses placeholders (a name enclosed with the ``%``
character) to determine the output format. Here is a list of the
built-in placeholders:

* ``indicator``: The current indicator;
* ``elapsed``: The time elapsed since the start of the progress indicator;
* ``memory``: The current memory usage;
* ``message``: used to display arbitrary messages in the progress indicator.

If you want to customize a placeholder, for example the ``message`` one, here
is how you should do this::

    ProgressIndicator::setPlaceholderFormatterDefinition(
        'message',
        static function (ProgressIndicator $progressIndicator): string {
            // Return any arbitrary string
            return 'My custom message';
        }
    );

.. note::

    Placeholders customization is applied globally, which means that any
    progress indicator displayed after the
    ``setPlaceholderFormatterDefinition()`` call will be affected.
