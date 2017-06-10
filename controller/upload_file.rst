.. index::
   single: Controller; Upload; File

How to Upload Files
===================

.. note::

    Instead of handling file uploading yourself, you may consider using the
    `VichUploaderBundle`_ community bundle. This bundle provides all the common
    operations (such as file renaming, saving and deleting) and it's tightly
    integrated with Doctrine ORM, MongoDB ODM, PHPCR ODM and Propel.

Imagine that you have a ``Product`` entity in your application and you want to
add a PDF brochure for each product. To do so, add a new property called ``brochure``
in the ``Product`` entity::

    // src/AppBundle/Entity/Product.php
    namespace AppBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Symfony\Component\Validator\Constraints as Assert;

    class Product
    {
        // ...

        /**
         * @ORM\Column(type="string")
         *
         * @Assert\NotBlank(message="Please, upload the product brochure as a PDF file.")
         * @Assert\File(mimeTypes={ "application/pdf" })
         */
        private $brochure;

        public function getBrochure()
        {
            return $this->brochure;
        }

        public function setBrochure($brochure)
        {
            $this->brochure = $brochure;

            return $this;
        }
    }

Note that the type of the ``brochure`` column is ``string`` instead of ``binary``
or ``blob`` because it just stores the PDF file name instead of the file contents.

Then, add a new ``brochure`` field to the form that manages the ``Product`` entity::

    // src/AppBundle/Form/ProductType.php
    namespace AppBundle\Form;

    use AppBundle\Entity\Product;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;
    use Symfony\Component\Form\Extension\Core\Type\FileType;

    class ProductType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                // ...
                ->add('brochure', FileType::class, array('label' => 'Brochure (PDF file)'))
                // ...
            ;
        }

        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults(array(
                'data_class' => Product::class,
            ));
        }
    }

Now, update the template that renders the form to display the new ``brochure``
field (the exact template code to add depends on the method used by your application
to :doc:`customize form rendering </form/form_customization>`):

.. configuration-block::

    .. code-block:: html+twig

        {# app/Resources/views/product/new.html.twig #}
        <h1>Adding a new product</h1>

        {{ form_start(form) }}
            {# ... #}

            {{ form_row(form.brochure) }}
        {{ form_end(form) }}

    .. code-block:: html+php

        <!-- app/Resources/views/product/new.html.twig -->
        <h1>Adding a new product</h1>

        <?php echo $view['form']->start($form) ?>
            <?php echo $view['form']->row($form['brochure']) ?>
        <?php echo $view['form']->end($form) ?>

Finally, you need to update the code of the controller that handles the form::

    // src/AppBundle/Controller/ProductController.php
    namespace AppBundle\ProductController;

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\HttpFoundation\Request;
    use AppBundle\Entity\Product;
    use AppBundle\Form\ProductType;

    class ProductController extends Controller
    {
        /**
         * @Route("/product/new", name="app_product_new")
         */
        public function newAction(Request $request)
        {
            $product = new Product();
            $form = $this->createForm(ProductType::class, $product);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // $file stores the uploaded PDF file
                /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
                $file = $product->getBrochure();

                // Generate a unique name for the file before saving it
                $fileName = md5(uniqid()).'.'.$file->guessExtension();

                // Move the file to the directory where brochures are stored
                $file->move(
                    $this->getParameter('brochures_directory'),
                    $fileName
                );

                // Update the 'brochure' property to store the PDF file name
                // instead of its contents
                $product->setBrochure($fileName);

                // ... persist the $product variable or any other work

                return $this->redirect($this->generateUrl('app_product_list'));
            }

            return $this->render('product/new.html.twig', array(
                'form' => $form->createView(),
            ));
        }
    }

Now, create the ``brochures_directory`` parameter that was used in the
controller to specify the directory in which the brochures should be stored:

.. code-block:: yaml

    # app/config/config.yml

    # ...
    parameters:
        brochures_directory: '%kernel.project_dir%/web/uploads/brochures'

There are some important things to consider in the code of the above controller:

#. When the form is uploaded, the ``brochure`` property contains the whole PDF
   file contents. Since this property stores just the file name, you must set
   its new value before persisting the changes of the entity;
#. In Symfony applications, uploaded files are objects of the
   :class:`Symfony\\Component\\HttpFoundation\\File\\UploadedFile` class. This class
   provides methods for the most common operations when dealing with uploaded files;
#. A well-known security best practice is to never trust the input provided by
   users. This also applies to the files uploaded by your visitors. The ``UploadedFile``
   class provides methods to get the original file extension
   (:method:`Symfony\\Component\\HttpFoundation\\File\\UploadedFile::getExtension`),
   the original file size (:method:`Symfony\\Component\\HttpFoundation\\File\\UploadedFile::getClientSize`)
   and the original file name (:method:`Symfony\\Component\\HttpFoundation\\File\\UploadedFile::getClientOriginalName`).
   However, they are considered *not safe* because a malicious user could tamper
   that information. That's why it's always better to generate a unique name and
   use the :method:`Symfony\\Component\\HttpFoundation\\File\\UploadedFile::guessExtension`
   method to let Symfony guess the right extension according to the file MIME type;

You can use the following code to link to the PDF brochure of a product:

.. configuration-block::

    .. code-block:: html+twig

        <a href="{{ asset('uploads/brochures/' ~ product.brochure) }}">View brochure (PDF)</a>

    .. code-block:: html+php

        <a href="<?php echo $view['assets']->getUrl('uploads/brochures/'.$product->getBrochure()) ?>">
            View brochure (PDF)
        </a>

.. tip::

    When creating a form to edit an already persisted item, the file form type
    still expects a :class:`Symfony\\Component\\HttpFoundation\\File\\File`
    instance. As the persisted entity now contains only the relative file path,
    you first have to concatenate the configured upload path with the stored
    filename and create a new ``File`` class::

        use Symfony\Component\HttpFoundation\File\File;
        // ...

        $product->setBrochure(
            new File($this->getParameter('brochures_directory').'/'.$product->getBrochure())
        );

Creating an Uploader Service
----------------------------

To avoid logic in controllers, making them big, you can extract the upload
logic to a separate service::

    // src/AppBundle/Service/FileUploader.php
    namespace AppBundle\Service;

    use Symfony\Component\HttpFoundation\File\UploadedFile;

    class FileUploader
    {
        private $targetDir;

        public function __construct($targetDir)
        {
            $this->targetDir = $targetDir;
        }

        public function upload(UploadedFile $file)
        {
            $fileName = md5(uniqid()).'.'.$file->guessExtension();

            $file->move($this->targetDir, $fileName);

            return $fileName;
        }

        public function getTargetDir()
        {
            return $this->targetDir;
        }
    }

Then, define a service for this class:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            # ...

            AppBundle\Service\FileUploader:
                arguments:
                    $targetDir: '%brochures_directory%'

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">
            <!-- ... -->

            <service id="AppBundle\FileUploader">
                <argument>%brochures_directory%</argument>
            </service>
        </container>

    .. code-block:: php

        // app/config/services.php
        use AppBundle\Service\FileUploader;

        $container->autowire(FileUploader::class)
            ->setArgument('$targetDir', '%brochures_directory%');

Now you're ready to use this service in the controller::

    // src/AppBundle/Controller/ProductController.php
    use Symfony\Component\HttpFoundation\Request;
    use AppBundle\Service\FileUploader;

    // ...
    public function newAction(Request $request, FileUploader $fileUploader)
    {
        // ...

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $product->getBrochure();
            $fileName = $fileUploader->upload($file);

            $product->setBrochure($fileName);

            // ...
        }

        // ...
    }

Using a Doctrine Listener
-------------------------

If you are using Doctrine to store the Product entity, you can create a
:doc:`Doctrine listener </doctrine/event_listeners_subscribers>` to
automatically upload the file when persisting the entity::

    // src/AppBundle/EventListener/BrochureUploadListener.php
    namespace AppBundle\EventListener;

    use Symfony\Component\HttpFoundation\File\UploadedFile;
    use Doctrine\ORM\Event\LifecycleEventArgs;
    use Doctrine\ORM\Event\PreUpdateEventArgs;
    use AppBundle\Entity\Product;
    use AppBundle\Service\FileUploader;

    class BrochureUploadListener
    {
        private $uploader;

        public function __construct(FileUploader $uploader)
        {
            $this->uploader = $uploader;
        }

        public function prePersist(LifecycleEventArgs $args)
        {
            $entity = $args->getEntity();

            $this->uploadFile($entity);
        }

        public function preUpdate(PreUpdateEventArgs $args)
        {
            $entity = $args->getEntity();

            $this->uploadFile($entity);
        }

        private function uploadFile($entity)
        {
            // upload only works for Product entities
            if (!$entity instanceof Product) {
                return;
            }

            $file = $entity->getBrochure();

            // only upload new files
            if (!$file instanceof UploadedFile) {
                return;
            }

            $fileName = $this->uploader->upload($file);
            $entity->setBrochure($fileName);
        }
    }

Now, register this class as a Doctrine listener:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            _defaults:
                # ... be sure autowiring is enabled
                autowire: true
            # ...

            AppBundle\EventListener\BrochureUploadListener:
                tags:
                    - { name: doctrine.event_listener, event: prePersist }
                    - { name: doctrine.event_listener, event: preUpdate }

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                http://symfony.com/schema/dic/services/services-1.0.xsd">

            <!-- ... be sure autowiring is enabled -->
            <defaults autowire="true" />
            <!-- ... -->

            <service id="AppBundle\EventListener\BrochureUploaderListener">
                <argument type="service" id="app.brochure_uploader"/>

                <tag name="doctrine.event_listener" event="prePersist"/>
                <tag name="doctrine.event_listener" event="preUpdate"/>
            </service>
        </container>

    .. code-block:: php

        // app/config/services.php
        use AppBundle\EventListener\BrochureUploaderListener;

        $container->autowire(BrochureUploaderListener::class)
            ->addTag('doctrine.event_listener', array(
                'event' => 'prePersist',
            ))
            ->addTag('doctrine.event_listener', array(
                'event' => 'preUpdate',
            ))
        ;

This listener is now automatically executed when persisting a new Product
entity. This way, you can remove everything related to uploading from the
controller.

.. tip::

    This listener can also create the ``File`` instance based on the path when
    fetching entities from the database::

        // ...
        use Symfony\Component\HttpFoundation\File\File;

        // ...
        class BrochureUploadListener
        {
            // ...

            public function postLoad(LifecycleEventArgs $args)
            {
                $entity = $args->getEntity();

                if (!$entity instanceof Product) {
                    return;
                }

                if ($fileName = $entity->getBrochure()) {
                    $entity->setBrochure(new File($this->uploader->getTargetDir().'/'.$fileName));
                }
            }
        }

    After adding these lines, configure the listener to also listen for the
    ``postLoad`` event.

.. _`VichUploaderBundle`: https://github.com/dustin10/VichUploaderBundle
