How to Upload Files
===================

.. note::

    Instead of handling file uploading yourself, you may consider using the
    `VichUploaderBundle`_ community bundle. This bundle provides all the common
    operations (such as file renaming, saving and deleting) and it's tightly
    integrated with Doctrine ORM, MongoDB ODM, PHPCR ODM and Propel.

Imagine that you have a ``Product`` entity in your application and you want to
add a PDF brochure for each product. To do so, add a new property called
``brochureFilename`` in the ``Product`` entity::

    // src/Entity/Product.php
    namespace App\Entity;

    use Doctrine\ORM\Mapping as ORM;

    class Product
    {
        // ...

        #[ORM\Column(type: 'string')]
        private string $brochureFilename;

        public function getBrochureFilename(): string
        {
            return $this->brochureFilename;
        }

        public function setBrochureFilename(string $brochureFilename): self
        {
            $this->brochureFilename = $brochureFilename;

            return $this;
        }
    }

Note that the type of the ``brochureFilename`` column is ``string`` instead of
``binary`` or ``blob`` because it only stores the PDF file name instead of the
file contents.

The next step is to add a new field to the form that manages the ``Product``
entity. This must be a ``FileType`` field so the browsers can display the file
upload widget. The trick to make it work is to add the form field as "unmapped",
so Symfony doesn't try to get/set its value from the related entity::

    // src/Form/ProductType.php
    namespace App\Form;

    use App\Entity\Product;
    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\Extension\Core\Type\FileType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;
    use Symfony\Component\Validator\Constraints\File;

    class ProductType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options): void
        {
            $builder
                // ...
                ->add('brochure', FileType::class, [
                    'label' => 'Brochure (PDF file)',

                    // unmapped means that this field is not associated to any entity property
                    'mapped' => false,

                    // make it optional so you don't have to re-upload the PDF file
                    // every time you edit the Product details
                    'required' => false,

                    // unmapped fields can't define their validation using annotations
                    // in the associated entity, so you can use the PHP constraint classes
                    'constraints' => [
                        new File([
                            'maxSize' => '1024k',
                            'mimeTypes' => [
                                'application/pdf',
                                'application/x-pdf',
                            ],
                            'mimeTypesMessage' => 'Please upload a valid PDF document',
                        ])
                    ],
                ])
                // ...
            ;
        }

        public function configureOptions(OptionsResolver $resolver): void
        {
            $resolver->setDefaults([
                'data_class' => Product::class,
            ]);
        }
    }

Now, update the template that renders the form to display the new ``brochure``
field (the exact template code to add depends on the method used by your application
to :doc:`customize form rendering </form/form_customization>`):

.. code-block:: html+twig

    {# templates/product/new.html.twig #}
    <h1>Adding a new product</h1>

    {{ form_start(form) }}
        {# ... #}

        {{ form_row(form.brochure) }}
    {{ form_end(form) }}

Finally, you need to update the code of the controller that handles the form::

    // src/Controller/ProductController.php
    namespace App\Controller;

    use App\Entity\Product;
    use App\Form\ProductType;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\File\Exception\FileException;
    use Symfony\Component\HttpFoundation\File\UploadedFile;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;
    use Symfony\Component\String\Slugger\SluggerInterface;

    class ProductController extends AbstractController
    {
        #[Route('/product/new', name: 'app_product_new')]
        public function new(Request $request, SluggerInterface $slugger): Response
        {
            $product = new Product();
            $form = $this->createForm(ProductType::class, $product);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /** @var UploadedFile $brochureFile */
                $brochureFile = $form->get('brochure')->getData();

                // this condition is needed because the 'brochure' field is not required
                // so the PDF file must be processed only when a file is uploaded
                if ($brochureFile) {
                    $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                    // this is needed to safely include the file name as part of the URL
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();

                    // Move the file to the directory where brochures are stored
                    try {
                        $brochureFile->move(
                            $this->getParameter('brochures_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                    }

                    // updates the 'brochureFilename' property to store the PDF file name
                    // instead of its contents
                    $product->setBrochureFilename($newFilename);
                }

                // ... persist the $product variable or any other work

                return $this->redirectToRoute('app_product_list');
            }

            return $this->render('product/new.html.twig', [
                'form' => $form,
            ]);
        }
    }

Now, create the ``brochures_directory`` parameter that was used in the
controller to specify the directory in which the brochures should be stored:

.. code-block:: yaml

    # config/services.yaml

    # ...
    parameters:
        brochures_directory: '%kernel.project_dir%/public/uploads/brochures'

There are some important things to consider in the code of the above controller:

#. In Symfony applications, uploaded files are objects of the
   :class:`Symfony\\Component\\HttpFoundation\\File\\UploadedFile` class. This class
   provides methods for the most common operations when dealing with uploaded files;
#. A well-known security best practice is to never trust the input provided by
   users. This also applies to the files uploaded by your visitors. The ``UploadedFile``
   class provides methods to get the original file extension
   (:method:`Symfony\\Component\\HttpFoundation\\File\\UploadedFile::getClientOriginalExtension`),
   the original file size (:method:`Symfony\\Component\\HttpFoundation\\File\\UploadedFile::getSize`)
   and the original file name (:method:`Symfony\\Component\\HttpFoundation\\File\\UploadedFile::getClientOriginalName`).
   However, they are considered *not safe* because a malicious user could tamper
   that information. That's why it's always better to generate a unique name and
   use the :method:`Symfony\\Component\\HttpFoundation\\File\\UploadedFile::guessExtension`
   method to let Symfony guess the right extension according to the file MIME type;

You can use the following code to link to the PDF brochure of a product:

.. code-block:: html+twig

    <a href="{{ asset('uploads/brochures/' ~ product.brochureFilename) }}">View brochure (PDF)</a>

.. tip::

    When creating a form to edit an already persisted item, the file form type
    still expects a :class:`Symfony\\Component\\HttpFoundation\\File\\File`
    instance. As the persisted entity now contains only the relative file path,
    you first have to concatenate the configured upload path with the stored
    filename and create a new ``File`` class::

        use Symfony\Component\HttpFoundation\File\File;
        // ...

        $product->setBrochureFilename(
            new File($this->getParameter('brochures_directory').'/'.$product->getBrochureFilename())
        );

Creating an Uploader Service
----------------------------

To avoid logic in controllers, making them big, you can extract the upload
logic to a separate service::

    // src/Service/FileUploader.php
    namespace App\Service;

    use Symfony\Component\HttpFoundation\File\Exception\FileException;
    use Symfony\Component\HttpFoundation\File\UploadedFile;
    use Symfony\Component\String\Slugger\SluggerInterface;

    class FileUploader
    {
        public function __construct(
            private string $targetDirectory,
            private SluggerInterface $slugger,
        ) {
        }

        public function upload(UploadedFile $file): string
        {
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

            try {
                $file->move($this->getTargetDirectory(), $fileName);
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            return $fileName;
        }

        public function getTargetDirectory(): string
        {
            return $this->targetDirectory;
        }
    }

.. tip::

    In addition to the generic :class:`Symfony\\Component\\HttpFoundation\\File\\Exception\\FileException`
    class there are other exception classes to handle failed file uploads:
    :class:`Symfony\\Component\\HttpFoundation\\File\\Exception\\CannotWriteFileException`,
    :class:`Symfony\\Component\\HttpFoundation\\File\\Exception\\ExtensionFileException`,
    :class:`Symfony\\Component\\HttpFoundation\\File\\Exception\\FormSizeFileException`,
    :class:`Symfony\\Component\\HttpFoundation\\File\\Exception\\IniSizeFileException`,
    :class:`Symfony\\Component\\HttpFoundation\\File\\Exception\\NoFileException`,
    :class:`Symfony\\Component\\HttpFoundation\\File\\Exception\\NoTmpDirFileException`,
    and :class:`Symfony\\Component\\HttpFoundation\\File\\Exception\\PartialFileException`.

Then, define a service for this class:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\Service\FileUploader:
                arguments:
                    $targetDirectory: '%brochures_directory%'

    .. code-block:: xml

        <!-- config/services.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                https://symfony.com/schema/dic/services/services-1.0.xsd">
            <!-- ... -->

            <service id="App\Service\FileUploader">
                <argument>%brochures_directory%</argument>
            </service>
        </container>

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\Service\FileUploader;

        return static function (ContainerConfigurator $container): void {
            $services = $container->services();

            $services->set(FileUploader::class)
                ->arg('$targetDirectory', '%brochures_directory%')
            ;
        };

Now you're ready to use this service in the controller::

    // src/Controller/ProductController.php
    namespace App\Controller;

    use App\Service\FileUploader;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;

    // ...
    public function new(Request $request, FileUploader $fileUploader): Response
    {
        // ...

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $brochureFile */
            $brochureFile = $form->get('brochure')->getData();
            if ($brochureFile) {
                $brochureFileName = $fileUploader->upload($brochureFile);
                $product->setBrochureFilename($brochureFileName);
            }

            // ...
        }

        // ...
    }

Using a Doctrine Listener
-------------------------

The previous versions of this article explained how to handle file uploads using
:ref:`Doctrine listeners <doctrine-lifecycle-listener>`. However, this is no longer
recommended, because Doctrine events shouldn't be used for your domain logic.

Moreover, Doctrine listeners are often dependent on internal Doctrine behavior
which may change in future versions. Also, they can introduce performance issues
unwillingly (because your listener persists entities which cause other entities to
be changed and persisted).

As an alternative, you can use :doc:`Symfony events, listeners and subscribers </event_dispatcher>`.

.. _`VichUploaderBundle`: https://github.com/dustin10/VichUploaderBundle
