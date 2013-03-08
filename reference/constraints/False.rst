False
=====

Validates that a value is ``false``. Specifically, this checks to see if
the value is exactly ``false``, exactly the integer ``0``, or exactly the
string "``0``".

Also see :doc:`True <True>`.

+----------------+---------------------------------------------------------------------+
| Applies to     | :ref:`property or method<validation-property-target>`               |
+----------------+---------------------------------------------------------------------+
| Options        | - `message`_                                                        |
+----------------+---------------------------------------------------------------------+
| Class          | :class:`Symfony\\Component\\Validator\\Constraints\\False`          |
+----------------+---------------------------------------------------------------------+
| Validator      | :class:`Symfony\\Component\\Validator\\Constraints\\FalseValidator` |
+----------------+---------------------------------------------------------------------+

Basic Usage
-----------

The ``False`` constraint can be applied to a property or a "getter" method,
but is most commonly useful in the latter case. For example, suppose that
you want to guarantee that some ``state`` property is *not* in a dynamic
``invalidStates`` array. First, you'd create a "getter" method::

    protected $state;

    protected $invalidStates = array();

    public function isStateInvalid()
    {
        return in_array($this->state, $this->invalidStates);
    }

In this case, the underlying object is only valid if the ``isStateInvalid``
method returns **false**:

.. configuration-block::

    .. code-block:: yaml

        # src/BlogBundle/Resources/config/validation.yml
        Acme\BlogBundle\Entity\Author
            getters:
                stateInvalid:
                    - "False":
                        message: You've entered an invalid state.

    .. code-block:: php-annotations

        // src/Acme/BlogBundle/Entity/Author.php
        namespace Acme\BlogBundle\Entity;

        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            /**
             * @Assert\False(
             *     message = "You've entered an invalid state."
             * )
             */
             public function isStateInvalid()
             {
                // ...
             }
        }

    .. code-block:: xml

        <!-- src/Acme/BlogBundle/Resources/config/validation.xml -->
        <class name="Acme\BlogBundle\Entity\Author">
            <getter property="stateInvalid">
                <constraint name="False">
                    <option name="message">You've entered an invalid state.</option>
                </constraint>
            </getter>
        </class>

    .. code-block:: php

        // src/Acme/BlogBundle/Entity/Author.php
        namespace Acme\BlogBundle\Entity;

        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Author
        {
            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addGetterConstraint('stateInvalid', new Assert\False());
            }
        }

.. caution::

    When using YAML, be sure to surround ``False`` with quotes (``"False"``)
    or else YAML will convert this into a Boolean value.

Options
-------

message
~~~~~~~

**type**: ``string`` **default**: ``This value should be false``

This message is shown if the underlying data is not false.
