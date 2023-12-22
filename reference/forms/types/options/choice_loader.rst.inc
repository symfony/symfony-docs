choice_loader
~~~~~~~~~~~~~

**type**: :class:`Symfony\\Component\\Form\\ChoiceList\\Loader\\ChoiceLoaderInterface`

The ``choice_loader`` option can be used instead of the ``choices`` option. It
allows to create a list lazily or partially when fetching only the choices for a
set of submitted values (i.e. querying a search engine like ``ElasticSearch``
can be a heavy process).

You can use an instance of :class:`Symfony\\Component\\Form\\ChoiceList\\Loader\\CallbackChoiceLoader`
if you want to take advantage of lazy loading::

    use App\StaticClass;
    use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    // ...

    $builder->add('loaded_choices', ChoiceType::class, [
        'choice_loader' => new CallbackChoiceLoader(static function (): array {
            return StaticClass::getConstants();
        }),
    ]);

This will cause the call of ``StaticClass::getConstants()`` to not happen if the
request is redirected and if there is no pre set or submitted data. Otherwise
the choice options would need to be resolved thus triggering the callback.

If the built-in ``CallbackChoiceLoader`` doesn't fit your needs, you can create
your own loader by implementing the
:class:`Symfony\\Component\\Form\\ChoiceList\\Loader\\ChoiceLoaderInterface`
or by extending the
:class:`Symfony\\Component\\Form\\ChoiceList\\Loader\\AbstractChoiceLoader`.
This abstract class saves you some boilerplate by implementing some methods of
the interface so you'll only have to implement the
:method:`Symfony\\Component\\Form\\ChoiceList\\Loader\\AbstractChoiceLoader::loadChoices`
method to have a fully functional choice loader.

When you're defining a custom choice type that may be reused in many fields
(like entries of a collection) or reused in multiple forms at once, you
should use the :class:`Symfony\\Component\\Form\\ChoiceList\\ChoiceList`
static methods to wrap the loader and make the choice list cacheable for
better performance::

    use App\Form\ChoiceList\CustomChoiceLoader;
    use App\StaticClass;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\ChoiceList\ChoiceList;
    use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
    use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
    use Symfony\Component\OptionsResolver\Options;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class ConstantsType extends AbstractType
    {
        public function getParent(): string
        {
            return ChoiceType::class;
        }

        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                // the example below will create a CallbackChoiceLoader from the callable
                'choice_loader' => ChoiceList::lazy($this, function () {
                    return StaticClass::getConstants();
                }),

                // you can pass your own loader as well, depending on other options
                'some_key' => null,
                'choice_loader' => function (Options $options): ChoiceLoaderInterface {
                    return ChoiceList::loader(
                        // pass the instance of the type or type extension which is
                        // currently configuring the choice list as first argument
                        $this,
                        // pass the other option to the loader
                        new CustomChoiceLoader($options['some_key']),
                        // ensure the type stores a loader per key
                        // by using the special third argument "$vary"
                        // an array containing anything that "changes" the loader
                        [$options['some_key']]
                    );
                },
            ]);
        }
    }
