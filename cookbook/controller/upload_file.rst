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
                'data_class' => 'AppBundle\Entity\Product',
            ));
        }
    }

Now, update the template that renders the form to display the new ``brochure``
field (the exact template code to add depends on the method used by your application
to :doc:`customize form rendering </cookbook/form/form_customization>`):

.. code-block:: html+twig

    {# app/Resources/views/product/new.html.twig #}
    <h1>Adding a new product</h1>

    {{ form_start() }}
        {# ... #}

        {{ form_row(form.brochure) }}
    {{ form_end() }}

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

            if ($form->isValid()) {
                // $file stores the uploaded PDF file
                /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
                $file = $product->getBrochure();

                // Generate a unique name for the file before saving it
                $fileName = md5(uniqid()).'.'.$file->guessExtension();

                // Move the file to the directory where brochures are stored
                $brochuresDir = $this->container->getParameter('kernel.root_dir').'/../web/uploads/brochures';
                $file->move($brochuresDir, $fileName);

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

There are some important things to consider in the code of the above controller:

#. When the form is uploaded, the ``brochure`` property contains the whole PDF
   file contents. Since this property stores just the file name, you must set
   its new value before persisting the changes of the entity;
#. In Symfony applications, uploaded files are objects of the
   :class:`Symfony\\Component\\HttpFoundation\\File\\UploadedFile` class, which
   provides methods for the most common operations when dealing with uploaded files;
#. A well-known security best practice is to never trust the input provided by
   users. This also applies to the files uploaded by your visitors. The ``Uploaded``
   class provides methods to get the original file extension
   (:method:`Symfony\\Component\\HttpFoundation\\File\\UploadedFile::getExtension`),
   the original file size (:method:`Symfony\\Component\\HttpFoundation\\File\\UploadedFile::getClientSize`)
   and the original file name (:method:`Symfony\\Component\\HttpFoundation\\File\\UploadedFile::getClientOriginalName`).
   However, they are considered *not safe* because a malicious user could tamper
   that information. That's why it's always better to generate a unique name and
   use the :method:`Symfony\\Component\\HttpFoundation\\File\\UploadedFile::guessExtension`
   method to let Symfony guess the right extension according to the file MIME type;
#. The ``UploadedFile`` class also provides a :method:`Symfony\\Component\\HttpFoundation\\File\\UploadedFile::move`
   method to store the file in its intended directory. Defining this directory
   path as an application configuration option is considered a good practice that
   simplifies the code: ``$this->container->getParameter('brochures_dir')``.

You can now use the following code to link to the PDF brochure of an product:

.. code-block:: html+twig

    <a href="{{ asset('uploads/brochures/' ~ product.brochure) }}">View brochure (PDF)</a>

.. _`VichUploaderBundle`: https://github.com/dustin10/VichUploaderBundle
