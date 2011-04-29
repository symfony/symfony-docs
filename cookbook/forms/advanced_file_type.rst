Advanced File Type Usage
========================

The following cookbook recipe outlines how to extend the FileType field
to customize the location of your uploads

Create a new field type
-----------------------

We will first need to create a new field type class.  We will extend ``FileType``
for this example, and call our class ``AssetType``, as it represents the
location we will be placing our assets.  Our field type will support the
options ``path`` and ``keep_filename``.  The ``path`` option will specify
where the asset should be placed, and the ``keep_filename`` option, when 
set to ``true`` will retain the uploaded file's name.

.. code-block:: html+php

    <?php

    namespace Acme\MyBundle\Form;

    use Acme\MyBundle\EventListener;

    // ... other namespaces

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
                    $this->assetDir . '/' . $options['path'], 
                    $options['keep_filename']), 10)
                ->add('file', 'field')
                ->add('token', 'hidden')
                ->add('name', 'hidden');
        }

        public function getDefaultOptions(array $options)
        {
            return array(
                'path' => 'uploads',
                'keep_filename' => true,
            );
        }
    }

The main difference between our class and ``FileType`` is the ``MoveFileUploadListener``
class.  This replaces the ``FixFileUploadListener`` that was there before.  
The difference is that ``MoveFileUploadListener`` is a class listening to
the form event, and will execute before the field values are returned. This
class takes a destination as its first argument, and whether or not to preserve
the original filename as the second argument.

.. code-block:: html+php

    <?php

    namespace Acme\MyBundle\EventListener;

    // ... other namespaces

    class MoveFileUploadListener implements EventSubscriberInterface
    {
        protected $newLocation;
        protected $keepFilename;

        public function __construct($newLocation, $keepFilename = false)
        {
            $this->newLocation = $newLocation;
            $this->keepFilename = $keepFilename;
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
                $filename = $this->keepFilename ? $data['file']->getOriginalName() : null;
                $data['file']->move($this->newLocation, $filename);
                $data['name'] = $data['file']->getName();
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
the ``uploads/attachments`` directory, and to preserve the filename.

.. code-block:: php

    class GenericBlogType extends AbstractType
    {
        public function buildForm(FormBuilder $builder, array $options)
        {
            $builder->add('name');
            $builder->add('attachment', 'file', array(
                'path' => 'uploads/attachments', 
                'keep_filename' => true
            ));
        }
        
        public function getDefaultOptions(array $options)
        {
            return array(
                'data_class' => 'Acme\MyBundle\Entity\GenericBlog'
            );
        }
    }
