.. index::
    single: Translation; Adding Custom Format Support

Adding Custom Format Support
============================

Sometimes, you need to deal with custom formats for translation files. The
Translation component is flexible enough to support this. Just create a
loader (to load translations) and, optionally, a dumper (to dump translations).

Imagine that you have a custom format where translation messages are defined
using one line for each translation and parentheses to wrap the key and the
message. A translation file would look like this:

.. code-block:: text

    (welcome)(accueil)
    (goodbye)(au revoir)
    (hello)(bonjour)

.. _components-translation-custom-loader:

Creating a Custom Loader
------------------------

To define a custom loader that is able to read these kinds of files, you must create a
new class that implements the
:class:`Symfony\\Component\\Translation\\Loader\\LoaderInterface`. The
:method:`Symfony\\Component\\Translation\\Loader\\LoaderInterface::load`
method will get a filename and parse it into an array. Then, it will
create the catalog that will be returned::

    use Symfony\Component\Translation\MessageCatalogue;
    use Symfony\Component\Translation\Loader\LoaderInterface;

    class MyFormatLoader implements LoaderInterface
    {
        public function load($resource, $locale, $domain = 'messages')
        {
            $messages = array();
            $lines = file($resource);

            foreach ($lines as $line) {
                if (preg_match('/\(([^\)]+)\)\(([^\)]+)\)/', $line, $matches)) {
                    $messages[$matches[1]] = $matches[2];
                }
            }

            $catalogue = new MessageCatalogue($locale);
            $catalogue->add($messages, $domain);

            return $catalogue;
        }

    }

Once created, it can be used as any other loader::

    use Symfony\Component\Translation\Translator;

    $translator = new Translator('fr_FR');
    $translator->addLoader('my_format', new MyFormatLoader());

    $translator->addResource('my_format', __DIR__.'/translations/messages.txt', 'fr_FR');

    var_dump($translator->trans('welcome'));

It will print *"accueil"*.

.. _components-translation-custom-dumper:

Creating a Custom Dumper
------------------------

It is also possible to create a custom dumper for your format, which is
useful when using the extraction commands. To do so, a new class
implementing the
:class:`Symfony\\Component\\Translation\\Dumper\\DumperInterface`
must be created. To write the dump contents into a file, extending the
:class:`Symfony\\Component\\Translation\\Dumper\\FileDumper` class
will save a few lines::

    use Symfony\Component\Translation\MessageCatalogue;
    use Symfony\Component\Translation\Dumper\FileDumper;

    class MyFormatDumper extends FileDumper
    {
        public function formatCatalogue(MessageCatalogue $messages, $domain, array $options = array())
        {
            $output = '';

            foreach ($messages->all($domain) as $source => $target) {
                $output .= sprintf("(%s)(%s)\n", $source, $target);
            }

            return $output;
        }

        protected function getExtension()
        {
            return 'txt';
        }
    }

.. sidebar:: Format a message catalogue

    .. versionadded:: 2.8
        The ability to format a message catalogue without dumping it was introduced in Symfony 2.8.

    In some cases, you want to send the dump contents as a response instead of writing them in files.
    To do this, you can use the ``formatCatalogue`` method. In this case, you must pass the domain argument,
    which determines the list of messages that should be dumped.

The :method:`Symfony\\Component\\Translation\\Dumper\\FileDumper::formatCatalogue`
method creates the output string, that will be used by the
:method:`Symfony\\Component\\Translation\\Dumper\\FileDumper::dump` method
of the FileDumper class to create the file. The dumper can be used like any other
built-in dumper. In the following example, the translation messages defined in the
YAML file are dumped into a text file with the custom format::

    use Symfony\Component\Translation\Loader\YamlFileLoader;

    $loader = new YamlFileLoader();
    $catalogue = $loader->load(__DIR__ . '/translations/messages.fr_FR.yml' , 'fr_FR');

    $dumper = new MyFormatDumper();
    $dumper->dump($catalogue, array('path' => __DIR__.'/dumps'));
