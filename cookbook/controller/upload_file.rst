.. index::
   single: Controller; Upload; File

How to upload files
===================

Let's begin with the creation of an entity Product having a document property to
which will contain the description of that product. We'll also indicate the
validation needed for each properties of the entity.

So let's say we have a product with a name, a price and a document which must be
a PDF file::

    // src/Vendor/ShopBundle/Entity/Product.php
    namespace Vendor\ShopBundle\Entity;

    use Symfony\Component\Validator\Constraints as Assert;

    class Product
    {
        /**
         * @Assert\NotBlank(message="You must indicate a name to your product.")
         */
        private $name;

        /**
         * @Assert\NotBlank(message="You must indicate a price to your product.")
         * @Assert\Type(type="float", message="Amount must be a valid number.")
         */
        private $price;

        /**
         * @Assert\NotBlank(message="You must upload a description with a PDF file.")
         * @Assert\File(mimeTypes={ "application/pdf" })
         */
        private $document;

        public function getName()
        {
            return $this->name;
        }

        public function setName($name)
        {
            $this->name = $name;

            return $this;
        }

        public function getPrice()
        {
            return $this->price;
        }

        public function setPrice($price)
        {
            $this->price = $price;

            return $this;
        }

        public function getDocument()
        {
            return $this->document;
        }

        public function setDocument($document)
        {
            $this->document = $document;

            return $this;
        }
    }

We also made sure that the user will have to indicate information to each fields.
To know more about validation, take a look at the :doc:`validation book </book/validation>`
chapter.

You have now to create the ``ProductType`` with those three fields as following::

    // src/Vendor/ShopBundle/Form/ProductType.php
    namespace Vendor\ShopBundle\Form;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;

    class ProductType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('name', 'text', array('label' => 'Name:'))
                ->add('price', 'money', array('label' => 'Price:'))
                ->add('document', 'file', array('label' => 'Upload description (PDF file):'))
                ->add('submit', 'submit', array('label' => 'Create!'))
            ;
        }

        public function getName()
        {
            return 'product';
        }
    }

Now, make it as a service so it can be used anywhere easily::

.. configuration-block::

    .. code-block:: yaml

        # src/Vendor/ShopBundle/Resources/config/services.yml
        services:
            vendor.form.product_type:
                class:        Vendor\ShopBundle\Form\ProductType
                tags:
                    -  { name: form.type }

        # Import the services.yml file of your bundle in your config.yml
        imports:
            - { resource: "@VendorShopBundle/Resources/config/services.yml" }

    .. code-block:: xml

            <!-- src/Vendor/ShopBundle/Resources/config/services.xml -->
            <services>
                <service id="vendor.form.product_type" class="Vendor\ShopBundle\Form\ProductType">
                    <tag name="form.type" alias="product" />
                </service>
            </services>

    .. code-block:: php

        // src/Vendor/ShopBundle/DependencyInjection/VendorShopExtension.php
        use Symfony\Component\DependencyInjection\Definition;

        //…

        $definition = new Definition('Vendor\ShopBundle\Form\ProductType');
        $container->setDefinition('vendor.form.product_type', $definition);
        $definition->addTag('form.type');

If you never dealt with services before, take some time to read the
:doc:`book Service </book/service_container>` chapter.


We must display the form to our users. To do that, create the controller as
following::

    // src/Vendor/ShopBundle/Controller/ProductController.php
    namespace Vendor\ShopBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\HttpFoundation\Request;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
    use Vendor\ShopBundle\Entity\Product;

    class ProductController extends Controller
    {
        /**
         * @Route("/product/new", name="vendor_product_new")
         * @Template()
         * @Method({"GET", "POST"})
         */
        public function newAction(Request $request)
        {
            $product = new Product();
            $form = $this->createForm('product', $product);
            $form->handleRequest($request);

            return array('form' => $form->createView());
        }
    }

Create the corresponding template as following::

.. code-block:: html+jinja

    {# src/Vendor/ShopBundle/Resources/views/Product/new.html.twig #}
    {% form_theme form _self %}

    <h1>Creation of a new Product</h1>

    <form action="{{ path('vendor_product_new') }}" method="POST" {{ form_enctype(form) }}>
        {{ form_widget(form) }}
    </form>

    {% block form_row %}
    {% spaceless %}
        <fieldset>
            <legend>{{ form_label(form) }}</legend>
                {{ form_errors(form) }}

                {{ form_widget(form) }}
        </fieldset>
    {% endspaceless %}
    {% endblock form_row %}

We added some sugar by adapting our form with a form theme (take a look at the
:doc:`form themes </cookbook/form/form_customization#what-are-form-themes>` to
know more about the subject).

We now have our form displayed. Let's complete our action to deal with the
upload of our document::

    // src/Vendor/ShopBundle/Controller/ProductController.php

    class ProductController extends Controller
    {
        /**
         * @Route("/product/new", name="vendor_product_new")
         * @Template()
         * @Method({"GET", "POST"})
         */
        public function newAction(Request $request)
        {
            //…

            if ($form->isValid()) {

                $file = $product->getDocument()

                // Compute the name of the file.
                $name = md5(uniqid()).'.'.$file->guessExtension();

                $file = $file->move(__DIR__.'/../../../../web/uploads', $name);
                $product->setDocument($filename);

                // Perform some persistance

                $this->getSession()->getFlashBag()->add('notice', 'The upload has been well uploaded.');

                return $this->redirect($this->generateUrl('vendor_product_new'));
            }

            return array('form' => $form->createView());
        }
    }

The :method:`Symfony\\Component\\HttpFoundation\\File\\UploadedFile::guessExtension()`
returns the extension of the file the user just uploaded.

Note the :method:`Symfony\\Component\\HttpFoundation\\File\\UploadedFile::move`
method allowing movement of the file

We must display the flash message in our template::

    .. code-block:: html+jinja

    {# src/Vendor/ShopBundle/Resources/views/Product/new.html.twig #}

    {# … #}
    {% for flashes in app.session.flashbag.all %}
        {% for flashMessage in flashes %}
            <ul>
                <li>{{ flashMessage }}</li>
            </ul>
        {% endfor %}
    {% endfor %}
    {# … #}

The file is now uploaded in the folder ``web/upload`` of your project.

.. note::

    For the sake of testability and maintainability, it is recommended to put the
    logic inherent to the upload in a dedicated service. You could even make the
    path to the upload folder as a configuration parameter injected to your service.
    That way, you make the upload feature more flexible.
