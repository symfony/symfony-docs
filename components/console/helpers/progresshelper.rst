.. index::
    single: Console Helpers; Progress Helper

Progress Helper
===============

.. versionadded:: 2.3
    The ``setCurrent`` method was introduced in Symfony 2.3.

.. versionadded:: 2.4
    The ``clear`` method was introduced in Symfony 2.4.

.. caution::

    The Progress Helper was deprecated in Symfony 2.5 and will be removed in
    Symfony 3.0. You should now use the
    :doc:`Progress Bar </components/console/helpers/progressbar>` instead which
    is more powerful.

When executing longer-running commands, it may be helpful to show progress
information, which updates as your command runs:

.. image:: /images/components/console/progress.png

To display progress details, use the :class:`Symfony\\Component\\Console\\Helper\\ProgressHelper`,
pass it a total number of units, and advance the progress as your command executes::

    $progress = $this->getHelperSet()->get('progress');

    $progress->start($output, 50);
    $i = 0;
    while ($i++ < 50) {
        // ... do some work

        // advances the progress bar 1 unit
        $progress->advance();
    }

    $progress->finish();

.. tip::

    You can also set the current progress by calling the
    :method:`Symfony\\Component\\Console\\Helper\\ProgressHelper::setCurrent`
    method.

If you want to output something while the progress bar is running,
call :method:`Symfony\\Component\\Console\\Helper\\ProgressHelper::clear` first.
After you're done, call
:method:`Symfony\\Component\\Console\\Helper\\ProgressHelper::display`
to show the progress bar again.

The appearance of the progress output can be customized as well, with a number
of different levels of verbosity. Each of these displays different possible
items - like percentage completion, a moving progress bar, or current/total
information (e.g. 10/50)::

    $progress->setFormat(ProgressHelper::FORMAT_QUIET);
    $progress->setFormat(ProgressHelper::FORMAT_NORMAL);
    $progress->setFormat(ProgressHelper::FORMAT_VERBOSE);
    $progress->setFormat(ProgressHelper::FORMAT_QUIET_NOMAX);
    // the default value
    $progress->setFormat(ProgressHelper::FORMAT_NORMAL_NOMAX);
    $progress->setFormat(ProgressHelper::FORMAT_VERBOSE_NOMAX);

You can also control the different characters and the width used for the
progress bar::

    // the finished part of the bar
    $progress->setBarCharacter('<comment>=</comment>');
    // the unfinished part of the bar
    $progress->setEmptyBarCharacter(' ');
    $progress->setProgressCharacter('|');
    $progress->setBarWidth(50);

To see other available options, check the API documentation for
:class:`Symfony\\Component\\Console\\Helper\\ProgressHelper`.

.. caution::

    For performance reasons, be careful if you set the total number of steps
    to a high number. For example, if you're iterating over a large number of
    items, consider setting the redraw frequency to a higher value by calling
    :method:`Symfony\\Component\\Console\\Helper\\ProgressHelper::setRedrawFrequency`,
    so it updates on only some iterations::

        $progress->start($output, 50000);

        // updates every 100 iterations
        $progress->setRedrawFrequency(100);

        $i = 0;
        while ($i++ < 50000) {
            // ... do some work

            $progress->advance();
        }
