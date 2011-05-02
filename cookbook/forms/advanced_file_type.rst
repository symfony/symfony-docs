Advanced File Type Usage
========================

The following cookbook recipe outlines how to extend the FileType field
to customize the location of your uploads

Create a new field type
-----------------------

We will first need to create a new field type class.  We will extend ``FileType``
for this example, and call our class ``AssetType``, as it represents the
location we will be placing our assets.  Our field type will support a ``path``
option.  This option will specify where the asset should be placed.

.. code-block:: php

    namespace Acme\MyBundle\Form;

    use Symfony\Component\Form\Extension\Core\Type\FileType;
    use Symfony\Component\Form\FormBuilder;
    use Symfony\Component\Form\ReversedTransformer;
    use Symfony\Component\Form\Extension\Core\DataTransformer\FileToStringTransformer;
    use Symfony\Component\Form\Extension\Core\DataTransformer\FileToArrayTransformer;
    use Acme\StoreBundle\EventListener\MoveFileUploadListener;

    class AssetType extends FileType
    {
        private $assetDir;

        public function __construct($assetDir)
        {
            $this->assetDir = $assetDir;
        }

        public function buildForm(FormBuilder $builder, array $options)
        {
            $builder
                ->appendNormTransformer(new ReversedTransformer(
                    new FileToStringTransformer()))
                ->appendNormTransformer(new FileToArrayTransformer())
                ->addEventSubscriber(new MoveFileUploadListener(
                    $this->assetDir . '/' . $options['path']), 10)
                ->add('file', 'field')
                ->add('token', 'hidden')
                ->add('name', 'hidden');
        }

        public function getDefaultOptions(array $options)
        {
            return array(
                'path' => 'uploads',
            );
        }
    }

The main difference between our class and ``FileType`` is the ``MoveFileUploadListener``
class.  This replaces the ``FixFileUploadListener`` that was there before.
The difference is that ``MoveFileUploadListener`` is a class listening to
the form event, and will execute before the field values are returned. This
class takes the new file location as its first argument.

.. code-block:: php

    namespace Acme\MyBundle\EventListener;

    use Symfony\Component\Form\Events;
    use Symfony\Component\Form\Event\FilterDataEvent;
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Symfony\Component\HttpFoundation\File\File;
    use Symfony\Component\HttpFoundation\File\UploadedFile;

    class MoveFileUploadListener implements EventSubscriberInterface
    {
        protected $newLocation;

        public function __construct($newLocation)
        {
            $this->newLocation = $newLocation;
        }

        public static function getSubscribedEvents()
        {
            return Events::onBindClientData;
        }

        public function onBindClientData(FilterDataEvent $event)
        {
            $data = $event->getData();

            // Newly uploaded file
            if ($data['file'] instanceof UploadedFile && $data['file']->isValid()) {
                $data['name'] = $data['file']->getName() . $data['file']->getExtension();
                $data['file']->move($this->newLocation, $data['name']);
            }

            $event->setData($data);
        }
    }

This function moves the file to its new location, sets the new name in the
event data, and returns the data successfully. The final step remaining is
setting our new field type in our service container.  Because all field types
are services, this can be configured in your dependency injection configuration.

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            form.type.file:
                class: Acme\MyBundle\Form\AssetType
                arguments: [path/to/web/dir]
                tags:
                    - { name: form.type, alias: file }

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <services>
            <service id="form.type.file" class="Acme\MyBundle\Form\AssetType">
                <tag name="form.type" alias="file" />
                <argument>path/to/web/dir</argument>
            </service>
        </services>

    .. code-block:: php

        // app/config/services.php
        use Symfony\Component\DependencyInjection\Definition;

        $definition = new Definition('Acme\MyBundle\Form\AssetType', array('path/to/web/dir'));
        $definition->addTag('form.type', array('alias' => 'file'));
        $container->setDefinition('form.type.file', $definition);

.. note::
    The tag ``form.type`` on your service tells the Form Factory to accept
    this service as a field type.  In other words, any service with this
    tag can be loaded as a form type.  Give your tag a unique alias to
    create a new form type, rather than substituting out an existing one.

All ``file`` form types will now use your ``AssetType`` class.  The example
below illustrates the use of the new AssetType class.  We add an ``attachment``
file field to the ``GenericBlog`` class, and tell it to place the files in
the ``uploads/attachments`` directory.

.. code-block:: php

    class GenericBlogType extends AbstractType
    {
        public function buildForm(FormBuilder $builder, array $options)
        {
            $builder->add('name');
            $builder->add('attachment', 'file', array(
                'path' => 'uploads/attachments',
            ));
        }

        public function getDefaultOptions(array $options)
        {
            return array(
                'data_class' => 'Acme\MyBundle\Entity\GenericBlog'
            );
        }
    }
