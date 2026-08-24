Progress Indicator
==================

Progress indicators are useful to let users know that a command isn't stalled.
Unlike :doc:`progress bars </components/console/helpers/progressbar>`, these
indicators are used when the command duration is indeterminate (e.g. long-running
commands, unquantifiable tasks, etc.)

They work by instantiating the :class:`Symfony\\Component\\Console\\Helper\\ProgressIndicator`
class and advancing the progress as the command executes::

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
     ✔ Finished

    # OutputInterface::VERBOSITY_VERBOSE (-v)
     \ Processing... (1 sec)
     | Processing... (1 sec)
     / Processing... (1 sec)
     - Processing... (1 sec)
     ✔ Finished (1 sec)

    # OutputInterface::VERBOSITY_VERY_VERBOSE (-vv) and OutputInterface::VERBOSITY_DEBUG (-vvv)
     \ Processing... (1 sec, 6.0 MiB)
     | Processing... (1 sec, 6.0 MiB)
     / Processing... (1 sec, 6.0 MiB)
     - Processing... (1 sec, 6.0 MiB)
     ✔ Finished (1 sec, 6.0 MiB)

.. tip::

    Call a command with the quiet flag (``-q``) to not display any progress indicator.

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
     ✔ Finished

Once the progress finishes, it displays a special finished indicator (which defaults
to ✔). You can replace it with your own::

    $progressIndicator = new ProgressIndicator($output, finishedIndicatorValue: '🎉');

    try {
        /* do something */
        $progressIndicator->finish('Finished');
    } catch (\Exception) {
        $progressIndicator->finish('Failed', '🚨');
    }

The progress indicator will now look like this:

.. code-block:: text

     \ Processing...
     | Processing...
     / Processing...
     - Processing...
     🎉 Finished

Customize Placeholders
~~~~~~~~~~~~~~~~~~~~~~

A progress indicator uses placeholders (a name enclosed with the ``%``
character) to determine the output format. Here is a list of the
built-in placeholders:

* ``indicator``: The current indicator;
* ``elapsed``: The time elapsed since the start of the progress indicator;
* ``memory``: The current memory usage;
* ``message``: used to display arbitrary messages in the progress indicator.

For example, this is how you can customize the ``message`` placeholder::

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
