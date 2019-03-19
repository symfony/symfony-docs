.. index::
single: Console Helpers; Pretty Word Wrapper Helper

Pretty Word Wrapper Helper
==========================

The Pretty word wrapper provides a :method:`Symfony\\Component\\Console\\Helper\\PrettyWordWrapperHelper::wordwrap`
function to create well wrapped text messages.

The :class:`Symfony\\Component\\Console\\Helper\\PrettyWordWrapperHelper` is included
in the default helper set, which you can get by calling
:method:`Symfony\\Component\\Console\\Command\\Command::getHelperSet`::

    $wrapper = $this->getHelper('prettywordwrapper');

The method returns a string, which you'll usually render to the console by
passing it to the
:method:`OutputInterface::writeln <Symfony\\Component\\Console\\Output\\OutputInterface::writeln>` method.

Other using
-----------

You can use it with the default :class:`OutputFormatter <Symfony\\Component\\Console\\Formatter\\OutputFormatter>` or
other formatter that implements
:class:`WrappableOutputFormatterInterface <Symfony\\Component\\Console\\Formatter\\WrappableOutputFormatterInterface>`

.. note::

    See the ``CUT_*`` options below.

.. code-block:: php

    $text = '...';
    $wrappedText = $output->getFormatter()->format(
        $output->getFormatter()->wordwrap($text, 30, PrettyWordWrapperHelper::CUT_ALL)
    );

    // OR, you can set the default cutting option
    $output->getFormatter()->setDefaultWrapCutOption(PrettyWordWrapperHelper::CUT_ALL);
    $wrappedText = $output->getFormatter()->formatAndWrap($text, 30);

You can use it with :class:`Table <Symfony\\Component\\Console\\Helper\\Table>` also:

.. code-block:: php

    $table = $this->getHelper('table');
    $table
        ->setDefaultColumnWordWrapCutOption(PrettyWordWrapperHelper::CUT_DISABLE)
        ->setColumnMaxWidth(1, 40)                                    // Here will use the set default cut option
        ->setColumnMaxWidth(2, 20, PrettyWordWrapperHelper::CUT_ALL)  // Here will use the CUT_ALL option
    ;

.. note::

    More information about table helper: :doc:`/components/console/helpers/table`

Cut options
-----------

You can configure how to behave the wrapper.

========================  ======  ======================================================================================
Name                      Value   Description
========================  ======  ======================================================================================
``CUT_DISABLE``           ``0``   It disables all of options. Always break the text at word boundary.
``CUT_LONG_WORDS``        ``1``   "Long words" means the length of word is longer than one line. If it is set, the
                                  long words will cut.
``CUT_WORDS``             ``3``   Always break at set length, it will cut all words. It would be useful if you have
                                  little space. (Info: It "contains" the ``CUT_LONG_WORDS`` option)
``CUT_URLS``              ``4``   Lots of terminal can recognize URL-s in text and make them clickable (if there isn't
                                  break inside the URL) The URLs can be long, default we keep it in one block even if
                                  it gets ugly response. You can switch this behavior off with this option. The result
                                  will be pretty, but the URL won't be clickable.
``CUT_ALL``               ``7``   Switch every "word cut" options on.
``CUT_FILL_UP_MISSING``   ``8``   End of lines will fill up with spaces in order to every line to be same length.
``CUT_NO_REPLACE_EOL``    ``16``  The program will replace the PHP_EOL in the input string to $break by default. You
                                    switch it off with this.
========================  ======  ======================================================================================

Examples
~~~~~~~~

.. note::

    The default option is ``PrettyWordWrapperHelper::CUT_LONG_WORDS``.

Default parameters:

==============  ===========================================================================================
Parameter       Value
==============  ===========================================================================================
``$width``      ``120``
``$cutOption``  ``PrettyWordWrapperHelper::CUT_LONG_WORDS``
``$break``      ``\n``
==============  ===========================================================================================

.. code-block:: php

    $text = "Lorem ipsum dolor sit amet.";
    // Default parameters
    $default = $wrapper->wordwrap($text);

.. code-block:: text

    Lorem ipsum dolor sit amet.

Short lines, **disable** cuts ( ``CUT_DISABLE`` )

.. code-block:: php

    $text = "Lorem ipsum dolor sit amet.";
    $default = $wrapper->wordwrap($text, 4, PrettyWordWrapperHelper::CUT_DISABLE);

.. code-block:: text

    Lorem
    ipsum
    dolor
    sit
    amet.

Short lines, **enable** word cutting only for long words ( ``CUT_LONG_WORDS`` )

.. note::

    Long words in this example: ``Lorem``, ``ipsum`` and ``dolor``. The ``sit`` won't be cut here.

.. code-block:: php

    $text = "Lorem ipsum dolor sit amet.";
    $default = $wrapper->wordwrap($text, 4, PrettyWordWrapperHelper::CUT_LONG_WORDS);

.. code-block:: text

    Lore
    m ip
    sum
    dolo
    r
    sit
    amet
    .

Short lines, **enable** cut for every word ( ``CUT_WORDS`` )

.. code-block:: php

    $text = "Lorem ipsum dolor sit amet.";
    $default = $wrapper->wordwrap($text, 4, PrettyWordWrapperHelper::CUT_WORDS);

.. code-block:: text

    Lore
    m ip
    sum
    dolo
    r si
    t am
    et.

Short lines, **disable** word cutting  ( ``CUT_DISABLE`` ) + **set** custom break

.. code-block:: php

    $text = "Lorem ipsum dolor sit amet.";
    // The width is 6 here, not 4 like above.
    $default = $wrapper->wordwrap($text, 4, PrettyWordWrapperHelper::CUT_DISABLE, "<\n") . "<";

.. code-block:: text

    Lorem<
    ipsum<
    dolor<
    sit<
    amet.<

Short lines, **disable** word cutting ( ``CUT_DISABLE`` ) + **enable** fill up ( ``CUT_FILL_UP_MISSING`` ) + **set** custom break

.. code-block:: php

    $text = "Lorem ipsum dolor sit amet.";
    // The width is 6 here, not 4 like above.
    $default = $wrapper->wordwrap(
        $text,
        6,
        PrettyWordWrapperHelper::CUT_DISABLE | PrettyWordWrapperHelper::CUT_FILL_UP_MISSING,
        "<\n"
    ) . "<";

.. code-block:: text

    Lorem <
    ipsum <
    dolor <
    sit   <
    amet. <

Text with URL

.. code-block:: php

    $text = "Lorem ipsum dolor sit amet: http://symfony.com";
    $default = $wrapper->wordwrap($text, 4, PrettyWordWrapperHelper::CUT_DISABLE);

.. code-block:: text

    Lorem
    ipsum
    dolor
    sit
    amet:
    http://symofny.com

Text with URL, **enable** word cut ( ``CUT_WORDS`` ) + **disable** URL cut

.. code-block:: php

    $text = "Lorem ipsum dolor sit amet: http://symfony.com";
    $default = $wrapper->wordwrap($text, 4, PrettyWordWrapperHelper::CUT_WORDS);

.. code-block:: text

    Lore
    m ip
    sum
    dolo
    r si
    t am
    et:
    http://symofny.com

Text with URL, **disable** word cut + **enable** URL cut ( ``CUT_URLS`` )

.. code-block:: php

    $text = "Lorem ipsum dolor sit amet: http://symfony.com";
    $default = $wrapper->wordwrap($text, 4, PrettyWordWrapperHelper::CUT_URLS);

.. code-block:: text

    Lorem
    ipsum
    dolor
    sit
    amet:
    http
    ://s
    ymof
    ny.c
    om

Text with style tags

.. code-block:: php

    $text = "<comment>Lorem ipsum <fg=white;bg=blue>dolor</> sit amet.</comment>";
    $default = $wrapper->wordwrap($text, 4, PrettyWordWrapperHelper::CUT_WORDS);

.. code-block:: text

    <comment>Lore
    m ip
    sum
    <fg=white;bg=blue>dolo
    r</> si
    t am
    et.</comment>

Line endings
------------

There are different line endings on different operating systems. It can cause some problems, so we unify it to ``\n``.
You can switch this behavior off with ``PrettyWordWrapperHelper::CUT_NO_REPLACE_EOL``.
