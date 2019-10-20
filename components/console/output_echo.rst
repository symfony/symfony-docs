.. index::
    single: Console; EchoOutput

Echo Output Helper
==================

The Echo Output Helper outputs the messages with the php echo command.

Usage
~~~~~~~~~~~~~~~~~

Just instatiate EchoOutput() with no arguments.

.. code-block:: terminal

    $ cat invoke.php
    <?php
    
    require_once "script-which-loads-symfony.php"; # Autoload or manually: OutputFormatterStyleInterface.php, OutputFormatterStyleStack.php, OutputFormatterStyle.php, OutputFormatterInterface.php, OutputFormatter.php, OutputInterface.php, Output.php, EchoOutput.php
    require_once "TestApp.php";
    
    use Symfony\Component\Console\Output\EchoOutput;
    
    $output = new EchoOutput();
    $output->write("Starting." . PHP_EOL);
    testApp($output);
    
    $ cat TestApp.php 
    <?php
    
    function testApp($output) {
        $output->writeln("Hello, here's testApp!");
    }
    
    
    $ curl https://domain/path/invoke.php
    Starting.
    Hello, here's testApp!
    $ 

