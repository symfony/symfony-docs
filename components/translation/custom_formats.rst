.. index::
    single: Translation; Custom formats

Custom formats
==============

Sometimes, you need to deal with custom formats for translation files. The
Translation component is flexible enough to support this, just creating a
loader (to load translations) and, optionally, a dumper (to dump translations).

Let's imagine you have a custom format where translation messages are defined
using one line for each translation and parenthesis to wrap the key and the
message. A translation file would look like this::

    (welcome)(Bienvenido)
    (goodbye)(Adios)
    (hello)(Hola)

To define a custom loader able to read this kind of files, you must create a
new class that implements the
:class:`Symfony\\Component\\Translation\\Loader\\LoaderInterface` interface,
which defines a
:method:`Symfony\\Component\\Translation\\Loader\\LoaderInterface::load`
method. In the loader, this method will get a filename and parse it to create an
array. Then, it will create the catalog that will be returned::

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

    $translator = new Translator('es_ES');
    $translator->addLoader('my_format', new MyFormatLoader());

    $translator->addResource('my_format', __DIR__.'/translations/messages.txt', 'es_ES');

    echo $translator->trans('welcome');

It will print *"Bienvenido"*.

It is also possible to create a custom dumper for your format. To do so,
a new class implementing the
:class:`Symfony\\Component\\Translation\\Dumper\\DumperInterface`
interface must be created.
To write the dump contents into a file, extending the
:class:`Symfony\\Component\\Translation\\Dumper\\FileDumper` class
will save a few lines::

    use Symfony\Component\Translation\MessageCatalogue;
    use Symfony\Component\Translation\Dumper\FileDumper;

    class MyFormatDumper extends FileDumper
    {

        public function format(MessageCatalogue $messages, $domain = 'messages')
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

The :method:`Symfony\\Component\\Translation\\Dumper\\FileDumper::format`
method creates the output string, that will be used by the
:method:`Symfony\\Component\\Translation\\Dumper\\FileDumper::dump` method
of the :class:`Symfony\\Component\\Translation\\Dumper\\FileDumper` class to
create the file. The dumper can be used like any other
built-in dumper. In this example, the translation messages defined in the YAML file
are dumped into a text file with the custom format::

    use Symfony\Component\Translation\Loader\YamlFileLoader;
    use RaulFraile\Dumper\CustomDumper;

    include_once __DIR__. '/vendor/autoload.php';

    $loader = new YamlFileLoader();
    $catalogue = $loader->load(__DIR__ . '/translations/messages.es_ES.yml' , 'es_ES');

    $dumper = new CustomDumper();
    $dumper->dump($catalogue, array('path' => __DIR__.'/dumps'));
