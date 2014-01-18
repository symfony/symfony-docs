.. index::
    single: Descriptors and Descriptor Helper

Descriptors
===========

.. versionadded:: 2.3
    Descriptors and Descriptor Helper were introduced in Symfony 2.3.

Descriptors were introduced as a manner to refactor the self documenting
logic found spread out in the ``Application`` and ``Command`` classes. The
purpose of this logic is to format the help output information of an
application, command, input definition, argument or option in various ways.

The :namespace:`Symfony\\Component\\Console\\Descriptor` class
family has functions to describe applications, commands, input
definitions, arguments and options for console applications in various
formats, namely xml, markdown, json, and txt.

Descriptors act upon the flags passed to the console app commands::

    $ php app.php some:command --format=json --raw=true

You can create a custom descriptor to output these help options or
other output to the user in a different format, like YAML.

Descriptors usage is already built-in for console applications. You
don't need to register the ``DescriptorHelper`` to benefit from the
default descriptors:

* TextDescriptor
* XmlDescriptor
* MarkdownDescriptor
* JsonDescriptor

Whenever you need to use a custom descriptor for your custom application,
you could use it directly. However, the family of Descriptors is meant
to be used via its ``DescriptorHelper``.

Setting the Descriptor Helper
-----------------------------

Descriptor Helper is not included in the default helper set, which you can
get by calling
:method:`Symfony\\Component\\Console\\Command\\Command::getHelperSet`, but
you can include it::

    // within your runner script
    $app = new Application();
    $app->getHelperSet()->set(new DescriptorHelper());

    // or overriding by extending the Application class
    protected function getDefaultHelperSet()
    {
        return new HelperSet(array(
            // ...
            new DescriptorHelper(),
        ));
    }

    // and use it within your command
    $helper = $this->getHelperSet()->get('descriptor');

In order to take advantage of a custom Descriptor class
you should register it with the helper above adding::

    $helper->register('no-color', new NoColorDescriptor());

Custom Descriptors
------------------

With the Descriptor abstraction the work of writing a custom
descriptor is now easy. All default descriptors use an
abstract class ``Descriptor`` which you can also extend to
segregate the describe functionality for application,
command, input definition, input argument and option accordingly.
A better option is to implement the interface
:class:``DescriptorInterface`` since you will be overriding most of it.

In order to create your custom description you can
first create a base abstract class::

    use Symfony\Component\Console\Descriptor\DescriptorInterface;
    use Symfony\Component\Console\Output\OutputInterface;
    use Acme\User;
    use Acme\UserGroup;

    abstract class UserDescriptor implements DescriptorInterface
    {
        protected $output;

        public function describe(OutputInterface $output, $subject, array $options = array())
        {
            $this->output = $output;
            if ($subject instanceof User) {
                $this->describeUser($subject);
            } elseif ($subject instanceof UserGroup) {
                $this->describeUserGroup($subject);
            }
        }

        abstract protected function describeUser(User $subject);
        abstract protected function describeUserGroup(UserGroup $subject);
    }

Let's enable this ``Descriptor`` to represent Users, either
individually or within groups::

    use Symfony\Component\Console\Helper\TableHelper;

    class TextUserDescriptor extends UserDescriptor
    {
        protected function describeUser(User $subject)
        {
            $lines = array();
            $lines[] = 'Name:  '.$subject->getName();
            $lines[] = 'Age:   '.$subject->getAge();
            $lines[] = 'Group: '.$subject->getGroup()->getName();

            $this->output->writeln($lines);
        }

        protected function describeUserGroup(UserGroup $subject)
        {
            $table = new TableHelper();
            $table->setLayout(TableHelper::LAYOUT_COMPACT);
            $table->setHeaders(array('Name', 'Age'));

            foreach ($subject->getUsers() as $user) {
                $table->addRow(array($user->getName(), $user->getAge()));
            }

            $this->output->writeln(array(
                'Group name: '.$subject->getName(),
                '',
            ));
            $table->render($this->output);
        }
    }

Notice the describe method adapts accordingly whether the subject
passed is a ``User`` object or a group of ``User`` objects.