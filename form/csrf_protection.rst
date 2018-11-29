.. index::
    single: Forms; CSRF protection

How to Implement CSRF Protection
================================

CSRF - or `Cross-site request forgery`_ - is a method by which a malicious
user attempts to make your legitimate users unknowingly submit data that
they don't intend to submit. Fortunately, CSRF attacks can be prevented by
using a CSRF token inside your forms.

The good news is that, by default, Symfony embeds and validates CSRF tokens
automatically for you. This means that you can take advantage of the CSRF
protection without doing anything. In fact, every form in this article has
taken advantage of the CSRF protection!

CSRF protection works by adding a hidden field to your form - called ``_token``
by default - that contains a value that only you and your user knows. This
ensures that the user - not some other entity - is submitting the given data.
Symfony automatically validates the presence and accuracy of this token.

The ``_token`` field is a hidden field and will be automatically rendered
if you include the ``form_end()`` function in your template, which ensures
that all un-rendered fields are output.

.. caution::

    Since the token is stored in the session, a session is started automatically
    as soon as you render a form with CSRF protection.

The CSRF token can be customized on a form-by-form basis. For example::

    // ...
    use AppBundle\Entity\Task;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class TaskType extends AbstractType
    {
        // ...

        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults(array(
                'data_class'      => Task::class,
                'csrf_protection' => true,
                'csrf_field_name' => '_token',
                // a unique key to help generate the secret token
                'csrf_token_id'   => 'task_item',
            ));
        }

        // ...
    }

.. _form-disable-csrf:

To disable CSRF protection, set the ``csrf_protection`` option to false.
Customizations can also be made globally in your project. For more information,
see the :ref:`form configuration reference <reference-framework-form>`
section.

.. note::

    The ``csrf_token_id`` option is optional but greatly enhances the security
    of the generated token by making it different for each form.

.. caution::

    CSRF tokens are meant to be different for every user. This is why you
    need to be cautious if you try to cache pages with forms including this
    kind of protection. For more information, see
    :doc:`/http_cache/form_csrf_caching`.

.. _`Cross-site request forgery`: http://en.wikipedia.org/wiki/Cross-site_request_forgery
