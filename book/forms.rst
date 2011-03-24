.. index::
   single: Forms

First, the model:

    class Order
    {
        protected $name;
    
        public function setName($name)
        {
            $this->name = $name;
        }

        public function getName()
        {
            return $this->name;
        }
    }

Next, the form builder (could also just be created in an action):

    use Symfony\Component\Form\Type\AbstractType;
    use Symfony\Component\Form\FormBuilder;

    class OrderFormType extends AbstractType
    {
        public function configure(FormBuilder $builder, array $options)
        {
            $this->add('text', 'name');
        }

        public function getName('form.order');
    }

--> Not shown here, put it into the DIC (still 100% required?)

Rock the action:

    $factory = $this->get('form.factory');
    $form = $factory->create('form.order');
    $order = new Order();
    $form->setData($order);
    
    $form->bindRequest($request);
    
    if ($form->isValid()) {
        $order->send();
        return new RedirectResponse(...);
    }
    
    $this->render(
        'HelloBundle:Hello:index.html.twig',
        array('form' => $form->getRenderer());
    );

And finally the template:

    {{ form.widget }}