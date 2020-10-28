#!/usr/bin/env php
<?php
require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Console\Input\InputOption;

(new Application('Symfony Docs Builder', '1.0'))
    ->register('build-docs')
    ->addOption('generate-fjson-files', null, InputOption::VALUE_NONE, 'Use this option to generate docs both in HTML and JSON formats')
    ->addOption('disable-cache', null, InputOption::VALUE_NONE, 'Use this option to force a full regeneration of all doc contents')
    ->setCode(function(InputInterface $input, OutputInterface $output) {
        $command = [
            'php',
            'vendor/symfony/docs-builder/bin/console',
            'build:docs',
            sprintf('--save-errors=%s', __DIR__.'/logs.txt'),
            __DIR__.'/../',
            __DIR__.'/output/',
        ];

        if ($input->getOption('generate-fjson-files')) {
            $command[] = '--output-json';
        }

        if ($input->getOption('disable-cache')) {
            $command[] = '--disable-cache';
        }

        $process = new Process($command);
        $process->setTimeout(3600);

        $this->getHelper('process')->run($output, $process);

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    })
    ->getApplication()
    ->setDefaultCommand('build-docs', true)
    ->run();
