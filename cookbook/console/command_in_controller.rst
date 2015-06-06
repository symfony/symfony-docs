.. index::
   single: Console; How to Call a Command from a controller

How to Call a Command from a Controller
=======================================

The :doc:`Console component documentation </components/console/introduction>`
covers how to create a console command. This cookbook article covers how
to use a console command directly from your controller.

You may have the need to execute some function that is only available in a
console command. Usually, you should refactor the command and move some logic
into a service that can be reused in the controller. However, when the command
is part of a third-party library, you wouldn't want to modify or duplicate
their code. Instead, you can execute the command directly.

.. caution::

    In comparison with a direct call from the console, calling a command from
    a controller has a slight performance impact because of the request stack
    overhead.

Imagine you want to send spooled Swift Mailer messages by
:doc:`using the swiftmailer:spool:send command </cookbook/email/spool>`.
Run this command from inside your controller via::

    // src/AppBundle/Controller/SpoolController.php
    namespace AppBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Console\Application;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\Console\Input\ArrayInput;
    use Symfony\Component\Console\Output\BufferedOutput;
    use Symfony\Component\HttpFoundation\Response;

    class SpoolController extends Controller
    {
        public function sendSpoolAction($messages = 10)
        {
            $kernel = $this->get('kernel');
            $application = new Application($kernel);
            $application->setAutoExit(false);

            $input = new ArrayInput(array(
               'command' => 'swiftmailer:spool:send',
               '--message-limit' => $messages,
            ));
            // You can use NullOutput() if you don't need the output
            $output = new BufferedOutput();
            $application->run($input, $output);

            // return the output, don't use if you used NullOutput()
            $content = $output->fetch();
            
            // return new Response(""), if you used NullOutput()
            return new Response($content);
        }
    }

Showing Colorized Command Output
--------------------------------

By telling the ``BufferedOutput`` it is decorated via the second parameter,
it will return the Ansi color-coded content. The `SensioLabs AnsiToHtml converter`_
can be used to convert this to colorful HTML.

First, require the package:

.. code-block:: bash

    $ composer require sensiolabs/ansi-to-html

Now, use it in your controller::

    // src/AppBundle/Controller/SpoolController.php
    namespace AppBundle\Controller;

    use SensioLabs\AnsiConverter\AnsiToHtmlConverter;
    use Symfony\Component\Console\Output\BufferedOutput;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\HttpFoundation\Response;
    // ...

    class SpoolController extends Controller
    {
        public function sendSpoolAction($messages = 10)
        {
            // ...
            $output = new BufferedOutput(
                OutputInterface::VERBOSITY_NORMAL,
                true // true for decorated
            );
            // ...

            // return the output
            $converter = new AnsiToHtmlConverter();
            $content = $output->fetch();

            return new Response($converter->convert($content));
        }
    }

The ``AnsiToHtmlConverter`` can also be registered `as a Twig Extension`_,
and supports optional themes.

.. _`SensioLabs AnsiToHtml converter`: https://github.com/sensiolabs/ansi-to-html
.. _`as a Twig Extension`: https://github.com/sensiolabs/ansi-to-html#twig-integration
