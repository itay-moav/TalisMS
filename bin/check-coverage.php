#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Reads a PHPUnit clover coverage report and fails if line coverage is
 * below the given minimum percentage.
 *
 * Usage: php bin/check-coverage.php <clover.xml> <minPercent>
 */

/**
 * @param list<string> $argv
 */
function run(array $argv): int
{
    if (count($argv) < 3) {
        fwrite(STDERR, "Usage: check-coverage.php <clover> <minPercent>\n");

        return 1;
    }

    $cloverPath = $argv[1];
    $minPercent = (float) $argv[2];

    $contents = file_get_contents($cloverPath);

    if ($contents === false) {
        fwrite(STDERR, "Could not read clover file: {$cloverPath}\n");

        return 1;
    }

    $xml = simplexml_load_string($contents);

    if ($xml === false) {
        fwrite(STDERR, "Could not parse clover file: {$cloverPath}\n");

        return 1;
    }

    $metrics = $xml->project->metrics;

    if ($metrics === null || count($metrics) === 0) {
        fwrite(STDERR, "No project metrics found in clover file: {$cloverPath}\n");

        return 1;
    }

    $statements = (int) $metrics['statements'];
    $coveredStatements = (int) $metrics['coveredstatements'];

    $percent = $statements > 0 ? ($coveredStatements / $statements) * 100 : 100.0;

    printf("Line coverage: %s%%\n", round($percent, 2));

    if ($percent < $minPercent) {
        return 1;
    }

    return 0;
}

/** @var list<string> $cliArgv */
$cliArgv = $_SERVER['argv'];

exit(run($cliArgv));
