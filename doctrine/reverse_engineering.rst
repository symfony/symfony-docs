How to Generate Entities from an Existing Database
==================================================

When starting a new project that involves using a database, there are naturally two different scenarios. In most cases, the database model is designed and built from scratch. However, there are times when you start with an existing database model that may not be modifiable. In Symfony 7.0, you can use Attributes to describe the database information, and the `doctrine-helper`_ provides tools to help generate model classes from an existing database.

Installing doctrine-helper
--------------------------

.. code-block:: terminal

    $ composer require siburuxue/doctrine-helper


The doctrine-helper supports importing and generating model classes for MySQL, PostgreSQL, SQLServer, Oracle, and SQLlite3 databases. In this article, we'll use MySQL as an example to demonstrate the generation result.

Before diving into the recipe, make sure your database connection parameters are correctly set up in the app/config/parameters.yaml file (or wherever your database configuration is stored).

Assuming your database contains a table called "test," which includes almost all MySQL data types and has unique indexes, composite indexes, and regular indexes:

.. code-block:: sql

    create table test
    (
        id     int                           not null,
        int_1  int                           not null comment 'int',
        int_2  smallint                      null comment 'smallint',
        int_3  tinyint                       null comment 'tinyint',
        int_4  mediumint                     null comment 'mediumint',
        int_5  bigint                        not null comment 'bigint'
            primary key,
        int_6  float                         null comment 'float',
        int_7  double                        null comment 'double',
        int_8  decimal                       null comment 'decimal',
        date_1 date      default (curdate()) null comment 'date',
        date_2 time      default (curtime()) null comment 'time',
        date_3 datetime                      null comment 'datetime',
        date_4 timestamp default (now())     null comment 'timestamp',
        date_5 year                          null comment 'year',
        str_1  char                          null comment 'char',
        str_2  varchar(255)                  null comment 'varchar(255)',
        str_3  binary(1)                     null comment 'binary',
        str_4  varbinary(1)                  null comment 'varbinary(1)',
        str_5  blob                          null comment 'blob',
        str_6  text                          null comment 'text',
        str_8  set ('a', 'b', 'c')           null comment 'set',
        json_1 json                          null comment 'json',
        bool_1 tinyint(1)                    null comment 'bool',
        constraint I_int_2
            unique (int_2) comment '唯一索引'
    )
        comment '测试表';

    create index I_int_1
        on test (int_1)
        comment '普通索引';

    create index I_int_3
        on test (int_3, int_4, int_5)
        comment '联合索引';

    create index I_int_4
        on test (int_6);


To generate the corresponding Entity and Repository class files using the command:

.. code-block:: terminal

    $ php bin/console doctrine-helper:mapping:import --ucfirst=true --table=test


For example, the newly created Test entity class will look like this::

    // src/Entity/Test.php
    namespace App\Entity;

    use App\Repository\TestRepository;
    use Doctrine\DBAL\Types\Types;
    use Doctrine\ORM\Mapping as ORM;

    #[ORM\Table(name: 'test')]
    #[ORM\UniqueConstraint(name: 'I_int_2', columns: ['int_2'])]
    #[ORM\Index(name: 'I_int_1', columns: ['int_1'])]
    #[ORM\Index(name: 'I_int_3', columns: ['int_3', 'int_4', 'int_5'])]
    #[ORM\Index(name: 'I_int_4', columns: ['int_6'])]
    #[ORM\Entity(repositoryClass: TestRepository::class)]
    class Test
    {
        #[ORM\Column(name: "int_5", type: Types::BIGINT, options: ["comment" => "bigint"])]
        #[ORM\Id]
        #[ORM\GeneratedValue(strategy: "NONE")]
        private ?string $int5 = null;

        #[ORM\Column(name: "id")]
        private ?int $id = null;

        #[ORM\Column(name: "int_1", options: ["comment" => "int"])]
        private ?int $int1 = null;

        #[ORM\Column(name: "int_2", type: Types::SMALLINT, nullable: true, options: ["comment" => "smallint"])]
        private ?int $int2 = null;

        #[ORM\Column(name: "int_3", nullable: true, options: ["comment" => "tinyint"])]
        private ?int $int3 = null;

        #[ORM\Column(name: "int_4", nullable: true, options: ["comment" => "mediumint"])]
        private ?int $int4 = null;

        #[ORM\Column(name: "int_6", nullable: true, options: ["comment" => "float"])]
        private ?float $int6 = null;

        #[ORM\Column(name: "int_7", nullable: true, options: ["comment" => "double"])]
        private ?float $int7 = null;

        #[ORM\Column(name: "int_8", type: Types::DECIMAL, precision: 10, scale: 0, nullable: true, options: ["comment" => "decimal"])]
        private ?string $int8 = null;

        #[ORM\Column(name: "date_1", type: Types::DATE_MUTABLE, nullable: true, options: ["comment" => "date"])]
        private ?\DateTimeInterface $date1 = null;

        #[ORM\Column(name: "date_2", type: Types::TIME_MUTABLE, nullable: true, options: ["comment" => "time"])]
        private ?\DateTimeInterface $date2 = null;

        #[ORM\Column(name: "date_3", type: Types::DATETIME_MUTABLE, nullable: true, options: ["comment" => "datetime"])]
        private ?\DateTimeInterface $date3 = null;

        #[ORM\Column(name: "date_4", type: Types::DATETIME_MUTABLE, nullable: true, options: ["comment" => "timestamp"])]
        private ?\DateTimeInterface $date4 = null;

        #[ORM\Column(name: "date_5", type: Types::DATETIME_MUTABLE, nullable: true, options: ["comment" => "year"])]
        private ?\DateTimeInterface $date5 = null;

        #[ORM\Column(name: "str_1", length: 1, nullable: true, options: ["comment" => "char", "fixed" => true])]
        private ?string $str1 = null;

        #[ORM\Column(name: "str_2", length: 255, nullable: true, options: ["comment" => "varchar(255)"])]
        private ?string $str2 = null;

        #[ORM\Column(name: "str_3", type: Types::BINARY, nullable: true, options: ["comment" => "binary"])]
        private $str3 = null;

        #[ORM\Column(name: "str_4", type: Types::BINARY, nullable: true, options: ["comment" => "varbinary(1)"])]
        private $str4 = null;

        #[ORM\Column(name: "str_5", type: Types::BLOB, nullable: true, options: ["comment" => "blob"])]
        private $str5 = null;

        #[ORM\Column(name: "str_6", type: Types::TEXT, nullable: true, options: ["comment" => "text"])]
        private ?string $str6 = null;

        #[ORM\Column(name: "str_8", type: Types::SIMPLE_ARRAY, nullable: true, options: ["comment" => "set"])]
        private ?array $str8 = null;

        #[ORM\Column(name: "json_1", nullable: true, options: ["comment" => "json"])]
        private ?array $json1 = null;

        #[ORM\Column(name: "bool_1", nullable: true, options: ["comment" => "bool"])]
        private ?int $bool1 = null;

        public function getInt5(): ?string
        {
            return $this->int5;
        }

        public function setInt5(string $int5): static
        {
            $this->int5 = $int5;

            return $this;
        }

        public function getId(): ?int
        {
            return $this->id;
        }

        public function setId(int $id): static
        {
            $this->id = $id;

            return $this;
        }

        public function getInt1(): ?int
        {
            return $this->int1;
        }

        public function setInt1(int $int1): static
        {
            $this->int1 = $int1;

            return $this;
        }

        public function getInt2(): ?int
        {
            return $this->int2;
        }

        public function setInt2(?int $int2): static
        {
            $this->int2 = $int2;

            return $this;
        }

        public function getInt3(): ?int
        {
            return $this->int3;
        }

        public function setInt3(?int $int3): static
        {
            $this->int3 = $int3;

            return $this;
        }

        public function getInt4(): ?int
        {
            return $this->int4;
        }

        public function setInt4(?int $int4): static
        {
            $this->int4 = $int4;

            return $this;
        }

        public function getInt6(): ?float
        {
            return $this->int6;
        }

        public function setInt6(?float $int6): static
        {
            $this->int6 = $int6;

            return $this;
        }

        public function getInt7(): ?float
        {
            return $this->int7;
        }

        public function setInt7(?float $int7): static
        {
            $this->int7 = $int7;

            return $this;
        }

        public function getInt8(): ?string
        {
            return $this->int8;
        }

        public function setInt8(?string $int8): static
        {
            $this->int8 = $int8;

            return $this;
        }

        public function getDate1(): ?\DateTimeInterface
        {
            return $this->date1;
        }

        public function setDate1(?\DateTimeInterface $date1): static
        {
            $this->date1 = $date1;

            return $this;
        }

        public function getDate2(): ?\DateTimeInterface
        {
            return $this->date2;
        }

        public function setDate2(?\DateTimeInterface $date2): static
        {
            $this->date2 = $date2;

            return $this;
        }

        public function getDate3(): ?\DateTimeInterface
        {
            return $this->date3;
        }

        public function setDate3(?\DateTimeInterface $date3): static
        {
            $this->date3 = $date3;

            return $this;
        }

        public function getDate4(): ?\DateTimeInterface
        {
            return $this->date4;
        }

        public function setDate4(?\DateTimeInterface $date4): static
        {
            $this->date4 = $date4;

            return $this;
        }

        public function getDate5(): ?\DateTimeInterface
        {
            return $this->date5;
        }

        public function setDate5(?\DateTimeInterface $date5): static
        {
            $this->date5 = $date5;

            return $this;
        }

        public function getStr1(): ?string
        {
            return $this->str1;
        }

        public function setStr1(?string $str1): static
        {
            $this->str1 = $str1;

            return $this;
        }

        public function getStr2(): ?string
        {
            return $this->str2;
        }

        public function setStr2(?string $str2): static
        {
            $this->str2 = $str2;

            return $this;
        }

        public function getStr3()
        {
            return $this->str3;
        }

        public function setStr3($str3): static
        {
            $this->str3 = $str3;

            return $this;
        }

        public function getStr4()
        {
            return $this->str4;
        }

        public function setStr4($str4): static
        {
            $this->str4 = $str4;

            return $this;
        }

        public function getStr5()
        {
            return $this->str5;
        }

        public function setStr5($str5): static
        {
            $this->str5 = $str5;

            return $this;
        }

        public function getStr6(): ?string
        {
            return $this->str6;
        }

        public function setStr6(?string $str6): static
        {
            $this->str6 = $str6;

            return $this;
        }

        public function getStr8(): ?array
        {
            return $this->str8;
        }

        public function setStr8(?array $str8): static
        {
            $this->str8 = $str8;

            return $this;
        }

        public function getJson1(): ?array
        {
            return $this->json1;
        }

        public function setJson1(?array $json1): static
        {
            $this->json1 = $json1;

            return $this;
        }

        public function getBool1(): ?int
        {
            return $this->bool1;
        }

        public function setBool1(?int $bool1): static
        {
            $this->bool1 = $bool1;

            return $this;
        }
    }

.. caution::

    The ``--ucfirst=true`` option is used to ensure compatibility with private properties in entities from Symfony 6.0, so that you can seamlessly migrate from Symfony 6.0 to 7.0 without modifying your business code due to changes in entity descriptions. Refer to the `doctrine-helper`_ documentation for more command parameters.

The generated entities are now ready to be used. Have fun!

.. _`doctrine-helper`: https://github.com/siburuxue/doctrine-helper
