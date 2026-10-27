.. _key-management-blind-index:

Searching Encrypted Data
========================

An encrypted column cannot be searched. Encryption draws a fresh nonce
every time, so the same address encrypts to different bytes on every write
and ``WHERE email = ?`` never matches.

The answer is a **blind index**: a second column holding a keyed digest of
the value, called a tag. Equal values give equal tags, so the row is found
by searching for the tag instead of the value. The tag is not reversible
and the database never holds the key.

.. include:: /key_management/_experimental.rst.inc

Registering an Index
--------------------

Start by creating the key that the tags are derived from:

.. code-block:: terminal

    $ php bin/console key-management:generate-data-key alias/app-key

Among other values, the command prints the wrapped key and the ``key_id`` of
the master key that wrapped it. Keep both: the key id is the one the
provider reported, which on AWS is a key ARN rather than the alias that was
asked for.

Then register one service per index. It needs the client that unwraps that
key, the wrapped key and its key id, and a **projection** saying which part
of the value is indexed:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            app.blind_index.user_email:
                class: Symfony\Component\KeyManagement\BlindIndex
                arguments:
                    $kms: '@key_management.app'
                    $wrappedKey: !service
                        class: Symfony\Component\KeyManagement\Ciphertext
                        arguments:
                            # the "wrapped" and "key_id" values the command printed
                            - '%env(base64:KMS_INDEX_KEY)%'
                            - '%env(KMS_INDEX_KEY_ID)%'
                    $projection: !service
                        class: Symfony\Component\KeyManagement\BlindIndex\Projection\Email
                tags:
                    - { name: 'key_management.blind_index', index: 'user-email' }

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use Symfony\Component\KeyManagement\BlindIndex;
        use Symfony\Component\KeyManagement\BlindIndex\Projection\Email;
        use Symfony\Component\KeyManagement\Ciphertext;

        return static function (ContainerConfigurator $container): void {
            $container->services()
                ->set('app.blind_index.user_email', BlindIndex::class)
                    ->arg('$kms', service('key_management.app'))
                    ->arg('$wrappedKey', inline_service(Ciphertext::class)->args([
                        env('base64:KMS_INDEX_KEY'),
                        env('KMS_INDEX_KEY_ID'),
                    ]))
                    ->arg('$projection', inline_service(Email::class))
                    ->tag('key_management.blind_index', ['index' => 'user-email'])
            ;
        };

The ``index`` attribute of the tag is the name of the index. It is what an
entity refers to, and it is also mixed into the derivation, so several
indexes can share one key without ever giving a value the same tag. With
``symfony/doctrine-orm-key-management`` installed, the tag passes that name
as the ``$name`` argument of the service, and refuses a service that sets
another one. Without that package, pass the name as ``$name`` yourself.

.. warning::

    That key must never be replaced, and the index must never be renamed.
    Every tag already written was derived under both, and a new key or a new
    name matches none of them. Changing either means recomputing every tag,
    which means reading and rewriting every row.

.. _key-management-blind-index-doctrine:

Filling the Column
------------------

Say on the column that holds the tag where its value comes from. With
``symfony/doctrine-orm-key-management`` installed, a listener then fills it
on every flush, so no write path has to remember it::

    // src/Entity/User.php
    namespace App\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Symfony\Component\KeyManagement\Bridge\DoctrineOrm\Attribute\BlindIndexed;
    use Symfony\Component\KeyManagement\Bridge\DoctrineOrm\Attribute\Encrypted;

    #[ORM\Entity]
    #[ORM\Index(columns: ['email_index'])]
    class User
    {
        #[ORM\Column]
        #[Encrypted(key: 'user.email')]
        private string $email = '';

        #[ORM\Column(length: 64)]
        #[BlindIndexed('email', 'user-email')]
        private string $emailIndex = '';
    }

The first argument of the attribute is the property being indexed, and the
second one is the name of the index, as set in the ``index`` attribute of
its tag. It goes on the column
holding the tag rather than on the source property, so a value can be
indexed by several columns, each under an index of its own.

A ``null`` value gives a ``null`` tag, so the property holding the tag must
be nullable when the indexed one is. The listener fills the tag of the
entities that a flush inserts or updates, and no other: fill the tags of the
rows written before the index existed with a script of your own.

.. warning::

    The attribute covers the write path of the ORM and nothing else. A row
    inserted through DBAL, or a bulk ``UPDATE ... SET email = ...``, never
    reaches the listener and leaves the tag untouched. That is worse than an
    empty tag: the search then returns the row that used to hold the value.

Searching
---------

A query has no entity to read the attribute on, so it computes the tag
itself, through the same index service::

    // src/Repository/UserRepository.php
    namespace App\Repository;

    use App\Entity\User;
    use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
    use Doctrine\Persistence\ManagerRegistry;
    use Symfony\Component\DependencyInjection\Attribute\Autowire;
    use Symfony\Component\KeyManagement\BlindIndexInterface;

    final class UserRepository extends ServiceEntityRepository
    {
        public function __construct(
            ManagerRegistry $registry,
            #[Autowire(service: 'app.blind_index.user_email')]
            private readonly BlindIndexInterface $index,
        ) {
            parent::__construct($registry, User::class);
        }

        public function findOneByEmail(string $email): ?User
        {
            return $this->findOneBy(['emailIndex' => $this->index->of($email)]);
        }
    }

.. warning::

    Equal values give equal tags, so anyone who can read the column learns
    which rows share a value and how often each occurs. On a column with few
    distinct values, or one whose distribution is known, that is enough to
    recover the values themselves by counting: a country, a status, a year
    of birth. Index what has high entropy and is looked up by equality, such
    as an address or an account number, and leave the rest to a decrypted
    scan.

A tag isn't authenticated either. A database writer can copy a tag to another
row together with the encrypted value. Finding a row by its tag therefore
doesn't prove that the value belongs to that row.

Choosing a Projection
---------------------

A projection decides which part of a value is indexed, both when writing
the tag and when searching for it. It is a class rather than a step each
call site performs, because a tag computed on a trimmed value and searched
on an untrimmed one silently matches nothing, which is the failure a blind
index exists to avoid. Three ship with the component:

:class:`Symfony\\Component\\KeyManagement\\BlindIndex\\Projection\\Verbatim`
    Indexes the value byte for byte. ``Ada`` and ``ada`` are then two
    values, and a search has to present the value exactly as written.

:class:`Symfony\\Component\\KeyManagement\\BlindIndex\\Projection\\Email`
    Indexes an email address, folding the domain only. This is what RFC 5321
    says: a domain is case-insensitive, a local part is not.

:class:`Symfony\\Component\\KeyManagement\\BlindIndex\\Projection\\EmailDomain`
    Indexes the domain alone, which answers "every account of that company"
    without the database holding a single address. It accepts a full
    address or a bare domain, because the row is indexed from the address
    and a query has only the domain to go on.

Folding a domain means converting it to its ASCII form, as UTS #46
describes, rather than lowercasing it. ``café.example`` written with a
precomposed ``é``, with an ``e`` followed by a combining accent, in
uppercase or as ``xn--caf-dma.example`` therefore all share one tag. A
domain that is not a valid internationalized one is lowercased instead.
The two email projections also trim the surrounding whitespace and take the
domain after the last ``@``.

``Email`` leaves the local part alone because the standard says the
receiving server owns it. Most providers treat it as case-insensitive in
practice, so an application letting someone register as ``Ada@example.com``
and sign in as ``ada@example.com`` won't find the row. Fold the address
once where it enters the application, or write a projection that folds it
and accept that two addresses the standard considers distinct are indexed
as one.

Anything more specific belongs in a projection of your own, which is a
class with a single ``project()`` method::

    // src/BlindIndex/PhoneNumber.php
    namespace App\BlindIndex;

    use Symfony\Component\KeyManagement\BlindIndex\ProjectionInterface;

    final class PhoneNumber implements ProjectionInterface
    {
        public function project(#[\SensitiveParameter] string $value): string
        {
            return preg_replace('/\D+/', '', $value);
        }
    }

Indexing Several Forms of One Value
-----------------------------------

Exact equality is as far as one tag goes. A prefix, substring or range
search is the same construction over several tags per row: every prefix of
a name, or every bucket a number falls in. A projection implementing
:class:`Symfony\\Component\\KeyManagement\\BlindIndex\\CoveringProjectionInterface`
returns those forms, and
:method:`Symfony\\Component\\KeyManagement\\BlindIndexInterface::allOf`
returns a tag for each::

    $tags = $this->index->allOf($name);   // one tag per form

It is a separate interface so that such an index is refused where one tag
is expected, for any value: ``of()`` throws an exception pointing to
``allOf()`` instead of silently keeping one tag of several. The row then needs a column
the database can intersect, ``text[]`` with a GIN index on PostgreSQL for
instance, and a query that matches any of the tags.

Writing that column is left to the application. ``#[BlindIndexed]`` fills
one column and refuses an index of this kind rather than keeping one tag of
several. What the forms are, and how much more they leak, is a decision
only the application can make.

Deriving the Key from the Store
-------------------------------

:class:`Symfony\\Component\\KeyManagement\\BlindIndex` unwraps its key
through the KMS, once per index and per process. When a
:doc:`store </key_management/store>` is configured, use
:class:`Symfony\\Component\\KeyManagement\\StoredKeyBlindIndex` instead: it
names a key the store holds, which the store decrypts once for every index
using it and for everything else encrypting under it.

It takes a reference, not a scope, because the key behind a scope is
replaced as it ages and no tag already written would match the new one.
Read the reference once and record it::

    // once, in a script or a migration; the reference is 16 binary bytes
    $reference = base64_encode($store->current('blind-index/tenant-42')->reference);

Then pass the ``key_management.store`` service and that reference, decoded
by the ``base64:`` environment variable processor, as the ``$store`` and
``$reference`` arguments of the service, in place of ``$kms`` and
``$wrappedKey``. Everything else (the service tag, the projection and the
attribute) stays the same.

Choosing the Keyed Function
---------------------------

A tag is 32 bytes, which
:class:`Symfony\\Component\\KeyManagement\\BlindIndex\\AlgorithmInterface`
fixes and the component checks on every derivation, since a column sized
once would truncate a wider one and a truncated tag matches rows that do
not hold the value. ``of()`` and ``allOf()`` return each tag as 64
hexadecimal characters, hence the ``length: 64`` of the column holding it.

:class:`Symfony\\Component\\KeyManagement\\BlindIndex\\HmacSha256` is the
default and needs nothing beyond what PHP always has.
:class:`Symfony\\Component\\KeyManagement\\BlindIndex\\Blake2b` is faster
for the same tag width but needs ``ext-sodium``. The difference only
matters when a row carries many tags. Pass it as the last argument of the
index.

Which one is in use is part of the stored data, so it is chosen explicitly
rather than probed at runtime: the same value gives a different tag under
each, and hosts disagreeing about which extensions are loaded would
otherwise write tags that only match on some of them. Changing it means
recomputing every tag.
