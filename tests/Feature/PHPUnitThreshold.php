#!/usr/bin/env php
<?php

declare(strict_types=1);

// Inspired by: https://ocramius.github.io/blog/automated-code-coverage-check-for-github-pull-requests-with-travis/

const XPATH_METRICS = '//metrics';
const XPATH_CLASSES = '//class';
const STATUS_OK = 0;
const STATUS_ERROR = 1;

function formatCoverage(float $number): string
{
	return sprintf('%0.2f%%', $number);
}

function loadMetrics(string $file): array
{
	$xml = new SimpleXMLElement(file_get_contents($file));

	return $xml->xpath(XPATH_METRICS);
}

function loadClasses(string $file): array
{
	$xml = new SimpleXMLElement(file_get_contents($file));

	return $xml->xpath(XPATH_CLASSES);
}

function printStatus(string $msg, int $exit_code = STATUS_OK)
{
	echo "\n";

	if ($exit_code === STATUS_OK) {
		echo colorWithBg(' PASS ', 30, 42);
	} else {
		echo colorWithBg(' FAIL ', 37, 41);
	}

	echo ' ' . $msg . PHP_EOL . PHP_EOL;
	exit($exit_code);
}

function colorWithBg(string $buffer, $fg_color, $bg_color)
{
	if (trim($buffer) === '') {
		return $buffer;
	}

	return "\e[1;{$fg_color};{$bg_color}m{$buffer}\e[0m";
}

function color(string $buffer, $color)
{
	if (trim($buffer) === '') {
		return $buffer;
	}

	return "\e[{$color}m{$buffer}\e[0m";
}

function dim(string $buffer): string
{
	if (trim($buffer) === '') {
		return $buffer;
	}

	return "\e[2m{$buffer}\e[22m";
}

function percent($fraction, $total): float
{
	if ($total > 0) {
		return ($fraction / $total) * 100;
	}

	return 100.0;
}

function printCoverageDetail($criteria, $fraction, $total)
{
	$percent = percent($fraction, $total);

	if ($percent > 85) {
		echo color('  ✓ ', 32);
	} elseif ($percent >= 50) {
		echo color('  ! ', 33);
	}else {
		echo color('  x ', 31);
	}

	echo dim("{$criteria}: {$fraction}/{$total}\t");
}

function printCoverageClass($class)
{
	$metric = $class->xpath('metrics')[0];

	$elements = (int) $metric['elements'];
	$covered_elements = (int) $metric['coveredelements'];
	$statements = (int) $metric['statements'];
	$covered_statements = (int) $metric['coveredstatements'];
	$methods = (int) $metric['methods'];
	$covered_methods = (int) $metric['coveredmethods'];

	if ($statements < 1) {
		return;
	}

	$percent = percent($covered_elements, $elements);

	if ($percent > 85) {
		echo colorWithBg('  OK  ', 30, 42);
	} elseif ($percent >= 50) {
		echo colorWithBg(' WARN ', 30, 43);
	}else {
		echo colorWithBg(' FAIL ', 37, 41);
	}

	echo " {$class['name']} " . dim('(' . sprintf('%0.1f%%', $percent) . ')') . PHP_EOL;

	printCoverageDetail('Methods', $covered_methods, $methods);
	printCoverageDetail('Lines', $covered_statements, $statements);

	echo PHP_EOL;
}

if (!isset($argv[1]) || !file_exists($argv[1])) {
	printStatus("Invalid input file {$argv[1]} provided.", STATUS_ERROR);
}

if (!isset($argv[2])) {
	printStatus(
		'An integer checked percentage must be given as second parameter.',
		STATUS_ERROR
	);
}

echo "\n-------------------- START COVERAGE REPORT --------------------\n\n";

$only_echo_percentage = isset($argv[3]) && $argv[3] === '--only-percentage';

$input_file = $argv[1];
$percentage = min(100, max(0, (float) $argv[2]));

if (!$only_echo_percentage) {
	foreach (loadClasses($input_file) as $class) {
		printCoverageClass($class);
	}

	echo "\n---------------------------------------------------------------\n";
}

$elements = 0;
$covered_elements = 0;
$statements = 0;
$covered_statements = 0;
$methods = 0;
$covered_methods = 0;

foreach (loadMetrics($input_file) as $metric) {
	$elements += (int) $metric['elements'];
	$covered_elements += (int) $metric['coveredelements'];
	$statements += (int) $metric['statements'];
	$covered_statements += (int) $metric['coveredstatements'];
	$methods += (int) $metric['methods'];
	$covered_methods += (int) $metric['coveredmethods'];
}

// See calculation: https://confluence.atlassian.com/pages/viewpage.action?pageId=79986990
$covered_metrics = $covered_statements + $covered_methods + $covered_elements;
$total_metrics = $statements + $methods + $elements;

if ($total_metrics === 0) {
	printStatus('Insufficient data for calculation. Please add more code.', STATUS_ERROR);
}

$total_percentage_coverage = $covered_metrics / $total_metrics * 100;

if ($total_percentage_coverage < $percentage && !$only_echo_percentage) {
	printStatus(
		dim('Total code coverage is ') . formatCoverage($total_percentage_coverage) . dim(' which is below the accepted ') . $percentage . '%',
		STATUS_ERROR
	);
}

if ($total_percentage_coverage < $percentage && $only_echo_percentage) {
	printStatus(formatCoverage($total_percentage_coverage), STATUS_ERROR);
}

printStatus(dim('Total code coverage is ') . formatCoverage($total_percentage_coverage));
