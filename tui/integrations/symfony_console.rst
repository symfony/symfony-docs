Symfony Console
===============

Tui integrates naturally with Symfony Console. Create a Tui inside
a command's ``execute()`` method, run it and return the exit code.

Basic Command
-------------

.. code-block:: php

    namespace App\Command;

    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Tui\Tui;
    use Symfony\Component\Tui\Widget\EditorWidget;
    use Symfony\Component\Tui\Widget\TextWidget;

    #[AsCommand(name: 'app:editor')]
    final class EditorCommand extends Command
    {
        protected function execute(
            InputInterface $input,
            OutputInterface $output,
        ): int {
            $tui = new Tui();
            $tui->quitOn('ctrl+c');

            $editor = new EditorWidget();
            $editor->onSubmit(fn () => $tui->stop());

            $tui->add(new TextWidget('Type your message:'));
            $tui->add($editor);
            $tui->setFocus($editor);

            $tui->run();

            if ($editor->wasSubmitted()) {
                $output->writeln('You wrote:');
                $output->writeln($editor->getText());
            }

            return Command::SUCCESS;
        }
    }

The Tui takes full control of the terminal while it runs. When
``run()`` returns, the terminal is restored and you can use the
Console ``$output`` object normally.

Output After the Tui Stops
--------------------------

Query widget state after ``run()`` returns to produce Console
output:

.. code-block:: php

    $tui->run();

    $selected = $list->getSelectedItem();
    if (null !== $selected) {
        $output->writeln('Selected: '.$selected['label']);
    }

    return Command::SUCCESS;

Using Console Input
-------------------

Read Console arguments and options before starting the Tui to
configure widgets:

.. code-block:: php

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $name = $input->getArgument('name');

        $tui = new Tui();
        $tui->quitOn('ctrl+c');

        $editor = new EditorWidget();
        $editor->setText($name);

        // ...
    }

Stylesheets
-----------

Pass a stylesheet to the Tui constructor to theme your command:

.. code-block:: php

    use Symfony\Component\Tui\Style\Style;
    use Symfony\Component\Tui\Style\StyleSheet;
    use Symfony\Component\Tui\Style\VerticalAlign;

    $stylesheet = new StyleSheet([
        '.root' => new Style(
            gap: 1,
            verticalAlign: VerticalAlign::Bottom,
        ),
    ]);

    $tui = new Tui($stylesheet);

See :doc:`/style/index` for the full styling documentation.

Handling Signals
----------------

Symfony Console registers signal handlers for ``SIGINT`` and
``SIGTERM``. The Tui uses its own terminal handling, so signal
delivery works as expected. ``quitOn('ctrl+c')`` intercepts the
Ctrl+C key press at the input level, before it becomes a signal.
