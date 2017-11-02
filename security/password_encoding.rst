.. index::
    single: Security; Encoding Passwords

How to Manually Encode a Password
=================================

.. note::

    For historical reasons, Symfony uses the term *"password encoding"* when it
    should really refer to *"password hashing"*. The "encoders" are in fact
    `cryptographic hash functions`_.

If, for example, you're storing users in the database, you'll need to encode
the users' passwords before inserting them. No matter what algorithm you
configure for your user object, the hashed password can always be determined
in the following way from a controller::

    use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

    public function registerAction(UserPasswordEncoderInterface $encoder)
    {
        // whatever *your* User object is
        $user = new App\Entity\User();
        $plainPassword = 'ryanpass';
        $encoded = $encoder->encodePassword($user, $plainPassword);

        $user->setPassword($encoded);
    }

In order for this to work, just make sure that you have the encoder for your
user class (e.g. ``AppBundle\Entity\User``) configured under the ``encoders``
key in ``app/config/security.yml``.

The ``$encoder`` object also has an ``isPasswordValid()`` method, which takes
the ``User`` object as the first argument and the plain password to check
as the second argument.

.. caution::

    When you allow a user to submit a plaintext password (e.g. registration
    form, change password form), you *must* have validation that guarantees
    that the password is 4096 characters or fewer. Read more details in
    :ref:`How to implement a simple Registration Form <registration-password-max>`.

.. _`cryptographic hash functions`: https://en.wikipedia.org/wiki/Cryptographic_hash_function
