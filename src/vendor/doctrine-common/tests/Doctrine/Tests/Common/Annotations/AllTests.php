<?php

namespace Doctrine\Tests\Common\Annotations;

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Common_Annotations_AllTests::main');
}

require_once __DIR__ . '/../../TestInit.php';

class AllTests
{
    public static function main()
    {
        \PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new \Doctrine\Tests\DoctrineTestSuite('Doctrine Common Annotations Tests');

        $suite->addTestSuite('Doctrine\Tests\Common\Annotations\LexerTest');
        $suite->addTestSuite('Doctrine\Tests\Common\Annotations\ParserTest');
        $suite->addTestSuite('Doctrine\Tests\Common\Annotations\AnnotationReaderTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Common_Annotations_AllTests::main') {
    AllTests::main();
}