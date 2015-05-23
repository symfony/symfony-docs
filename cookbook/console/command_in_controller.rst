.. index::
   single: Console; How to Call a Command from a controller

How to Call a Command from a Controller
=======================================

The :doc:`Console component documentation </components/console/introduction>` covers
how to create a console command. This cookbook article covers how to use a console command
directly from your controller. 

You may have the need to execute some function that is only available in a console command. 
Usually, you should refactor the command and move some logic into a service that can be 
reused in the controller. However, when the command is part of a third-party library, you 
wouldn't want to modify or duplicate their code, but want to directly execute the command 
instead.

.. caution::

    In comparison with a direct call from the console, calling a command from a controller
    has a slight performance impact because of the request stack overhead. This way of
    calling a command is only useful for small tasks.

An example of this is sending the emails that Swift Mailer spooled earlier
:doc:`using the swiftmailer:spool:send command </cookbook/email/spool>`. Symfony
allows you to directly execute a registered command inside your controller::

    // src/AppBundle/Controller/SpoolController.php
    namespace AppBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Console\Application;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\Console\Input\ArrayInput;
    use Symfony\Component\Console\Output\StreamOutput;

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
            $output = new StreamOutput(tmpfile(), StreamOutput::VERBOSITY_NORMAL);
            $application->run($input, $output);

            rewind($output->getStream());
            $content = stream_get_contents($output->getStream());
            fclose($output->getStream());

            return $content;
        }
    }

Showing Colorized Command Output
--------------------------------

By telling the ``StreamOutput`` it is decorated via the third parameter, it will return 
the Ansi color-coded content. The `SensioLabs AnsiToHtml converter`_ can be required 
using ``Composer`` and helps you getting colorful HTML::

    // src/AppBundle/Controller/SpoolController.php
    namespace AppBundle\Controller;

    use SensioLabs\AnsiConverter\AnsiToHtmlConverter;
    // ...

    class SpoolController extends Controller
    {
        public function sendSpoolAction($messages = 10)
        {
            // ...

            $converter = new AnsiToHtmlConverter();
            return $converter->convert($content);
        }
    }

The ``AnsiToHtmlConverter`` can also be registered `as a Twig Extension`_, 
and supports optional themes.

.. _`SensioLabs AnsiToHtml converter`: https://github.com/sensiolabs/ansi-to-html
.. _`as a Twig Extension`: https://github.com/sensiolabs/ansi-to-html#twig-integration
