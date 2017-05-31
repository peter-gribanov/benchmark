#!/usr/bin/env php
<?php

/**
 * GpsLab component.
 *
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/MIT
 */
require __DIR__.'/bootstrap.php';

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

$input = new ArgvInput();
$output = new SymfonyStyle($input, new ConsoleOutput());
$N = $input->getFirstArgument();


$tests = [
    'strrpos' => function($string) {
        $pos = strrpos($string, 'Command');
        if ($pos == strlen($string) - 7) {
            $string = substr($string, 0, -7);
        }

        return $string;
    },
    'substr' => function($string) {
        if ('Command' === substr($string, -7)) {
            $string = substr($string, 0, -7);
        }

        return $string;
    },
];

$results = [];

$output->section('Enum benchmark');
$output->progressStart(count($tests));

foreach ($tests as $title => $test) {
    $sum_memory = 0;
    $sum_duration = 0;
    for ($i = 0; $i < $N; ++$i) {
        $memory = memory_get_usage();
        $duration = microtime(true);
        call_user_func($test, 'CommandCommand'); // run test
        $sum_duration += microtime(true) - $duration;
        $sum_memory += memory_get_usage() - $memory;
    }

    $results[] = [
        $title,
        sprintf('%.2F KiB', $sum_memory / $N / 1024),
        sprintf('%d ms', round($sum_duration * 1000)),
    ];
    $output->progressAdvance();
}

$output->progressFinish();
$output->table(['Test', 'Memory Avg', 'Duration All'], $results);
