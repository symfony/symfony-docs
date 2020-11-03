#!/usr/bin/env php
<?php
require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use SymfonyDocsBuilder\BuildConfig;
use SymfonyDocsBuilder\DocBuilder;

(new Application('Symfony Docs Builder', '1.0'))
    ->register('build-docs')
    ->addOption('generate-fjson-files', null, InputOption::VALUE_NONE, 'Use this option to generate docs both in HTML and JSON formats')
    ->addOption('disable-cache', null, InputOption::VALUE_NONE, 'Use this option to force a full regeneration of all doc contents')
    ->setCode(function(InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);
        $io->text('Building all Symfony Docs...');

        $outputDir = __DIR__.'/output';
        $buildConfig = (new BuildConfig())
            ->setSymfonyVersion('4.4')
            ->setContentDir(__DIR__.'/..')
            ->setOutputDir($outputDir)
            ->setImagesDir(__DIR__.'/output/_images')
            ->setImagesPublicPrefix('_images')
            ->setTheme('rtd')
        ;

        $buildConfig->setExcludedPaths(['.github/', '_build/']);

        if (!$generateJsonFiles = $input->getOption('generate-fjson-files')) {
            $buildConfig->disableJsonFileGeneration();
        }

        if ($isCacheDisabled = $input->getOption('disable-cache')) {
            $buildConfig->disableBuildCache();
        }

        $io->comment(sprintf('cache: %s / output file type(s): %s', $isCacheDisabled ? 'disabled' : 'enabled', $generateJsonFiles ? 'HTML and JSON' : 'HTML'));
        if (!$isCacheDisabled) {
            $io->comment('Tip: add the --disable-cache option to this command to force the re-build of all docs.');
        }

        $result = (new DocBuilder())->build($buildConfig);

        if ($result->isSuccessful()) {
            $io->success(sprintf("The Symfony Docs were successfully built at %s", realpath($outputDir)));
        } else {
            $io->error(sprintf("There were some errors while building the docs:\n\n%s\n", $result->getErrorTrace()));
            $io->newLine();
            $io->comment('Tip: you can add the -v, -vv or -vvv flags to this command to get debug information.');
        }
    })
    ->getApplication()
    ->setDefaultCommand('build-docs', true)
    ->run();
