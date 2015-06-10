.. index::
   single: Controller; Upload; File

How to Upload Files
===================

Let's begin with the creation of an entity Product having a document property to
which will contain the description of that product.

First of all, you need to create a `Product` entity that has a `document` property
which will contain the description of the product. You'll also indicate the
validation needed for each properties of the entity.

Assume you need to have a product with a name, a price and a document which must
be a PDF file::

    // src/Acme/ShopBundle/Entity/Product.php
    namespace Acme\ShopBundle\Entity;

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

To make sure that the user will have to indicate information to each fields by
adding the `NotBlank` constraint.

.. seealso::

    To know more about validation, take a look at the :doc:`validation book </book/validation>`
    chapter.

You have now to create the ``ProductType`` with those three fields as following::

    // src/Acme/ShopBundle/Form/ProductType.php
    namespace Acme\ShopBundle\Form;

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

Now, make it as a service so it can be used anywhere easily:

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/ShopBundle/Resources/config/services.yml
        services:
            acme.form.product_type:
                class: Acme\ShopBundle\Form\ProductType
                tags:
                    -  { name: form.type }

        # Import the services.yml file of your bundle in your config.yml
        imports:
            - { resource: "@AcmeShopBundle/Resources/config/services.yml" }

    .. code-block:: xml

            <!-- src/Acme/ShopBundle/Resources/config/services.xml -->

            <?xml version="1.0" encoding="UTF-8" ?>
            <container xmlns="http://symfony.com/schema/dic/services">
            <services>
                <service id="acme.form.product_type" class="Acme\ShopBundle\Form\ProductType">
                    <tag name="form.type" alias="product" />
                </service>
            </services>

    .. code-block:: php

        // src/Acme/ShopBundle/DependencyInjection/AcmeShopExtension.php
        use Symfony\Component\DependencyInjection\Definition;

        //...

        $definition = new Definition('Acme\ShopBundle\Form\ProductType');
        $definition->addTag('form.type');
        $container->setDefinition('acme.form.product_type', $definition);

.. seealso::

    If you never dealt with services before, take some time to read the
    :doc:`book Service </book/service_container>` chapter.

Now, time to display the form to our users. To do that, create the controller as
following::

    // src/Acme/ShopBundle/Controller/ProductController.php
    namespace Acme\ShopBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Symfony\Component\HttpFoundation\Request;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
    use Acme\ShopBundle\Entity\Product;

    class ProductController extends Controller
    {
        /**
         * @Route("/product/new", name="acme_product_new")
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

Then, create the corresponding template as following:

.. code-block:: html+jinja

    {# src/Acme/ShopBundle/Resources/views/Product/new.html.twig #}
    {% form_theme form _self %}

    <h1>Creation of a new Product</h1>

    <form action="{{ path('acme_product_new') }}" method="POST" {{ form_enctype(form) }}>
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

Some sugar has been added by adapting our form with a form theme (take a look at
the :doc:`form themes </cookbook/form/form_customization#what-are-form-themes>`
to know more about the subject).

The form is now displayed. You have to complete our action to deal with the
upload of our document::

    // src/Acme/ShopBundle/Controller/ProductController.php

    class ProductController extends Controller
    {
        /**
         * @Route("/product/new", name="acme_product_new")
         * @Template()
         * @Method({"GET", "POST"})
         */
        public function newAction(Request $request)
        {
            //...

            if ($form->isValid()) {

                $file = $product->getDocument()

                // Compute the name of the file.
                $name = md5(uniqid()).'.'.$file->guessExtension();

                $file = $file->move(__DIR__.'/../../../../web/uploads', $name);
                $product->setDocument($filename);

                // ... perform some persistance

                $this->getSession()->getFlashBag()->add('notice', 'The upload has been well uploaded.');

                return $this->redirect($this->generateUrl('acme_product_new'));
            }

            return array('form' => $form->createView());
        }
    }

The :method:`Symfony\\Component\\HttpFoundation\\File\\UploadedFile::guessExtension()`
returns the extension of the file the user just uploaded.

Note the :method:`Symfony\\Component\\HttpFoundation\\File\\UploadedFile::move`
method allowing movement of the file.

To display the flash message in our template, you have to add the following code:

.. code-block:: html+jinja

    {# src/Acme/ShopBundle/Resources/views/Product/new.html.twig #}

    {# ... #}
    {% for flashes in app.session.flashbag.all %}
        {% for flashMessage in flashes %}
            <ul>
                <li>{{ flashMessage }}</li>
            </ul>
        {% endfor %}
    {% endfor %}
    {# ... #}

The file is now uploaded in the folder ``web/upload`` of your project.

.. note::

    For the sake of testability and maintainability, it is recommended to put the
    logic inherent to the upload in a dedicated service. You could even make the
    path to the upload folder as a configuration parameter injected to your service.
    That way, you make the upload feature more flexible.
