<?php

namespace PixelPay\Sdk\Tests\Feature;

use PHPUnit\Util\Filter;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestFailure;
use PHPUnit\Framework\TestResult;
use PHPUnit\Runner\BaseTestRunner;
use PHPUnit\TextUI\DefaultResultPrinter;
use PHPUnit\Util\Color;

class PrettyPrinterUnit extends DefaultResultPrinter
{
	protected $className;
	protected $previousClassName;

	/**
	 * On start testing
	 *
	 * @param Test $test
	 * @return void
	 */
	public function startTest(Test $test): void
	{
		$this->className = get_class($test);
	}

	/**
	 * On end testing
	 *
	 * @param Test|mixed $test
	 * @param float      $time
	 * @return void
	 */
	public function endTest(Test $test, float $time): void
	{
		parent::endTest($test, $time);

		$testMethodName = \PHPUnit\Util\Test::describe($test);

		$parts = preg_split('/ with data set /', $testMethodName[1]);
		$methodName = array_shift($parts);
		$dataSet = array_shift($parts);

		// Convert capitalized words to lowercase
		$methodName = preg_replace_callback('/([A-Z]{2,})/', function ($matches) {
			return strtolower($matches[0]);
		}, $methodName);

		// Convert non-breaking method name to camelCase
		$methodName = str_replace(' ', '', ucwords($methodName, ' '));

		// Convert snakeCase method name to camelCase
		$methodName = str_replace('_', '', ucwords($methodName, '_'));

		preg_match_all('/((?:^|[A-Z])[a-z0-9]+)/', $methodName, $matches);

		// Prepend all numbers with a space
		$replaced = preg_replace('/(\d+)/', ' $1', $matches[0]);

		$testNameArray = array_map('strtolower', $replaced);

		$name = implode(' ', $testNameArray);

		// check if prefix is test remove it
		$name = preg_replace('/^test /', '', $name, 1);

		// Get the data set name
		if ($dataSet) {
			// Note: Use preg_replace() instead of trim() because the dataset may end with a quote
			// (double quotes) and trim() would remove both from the end. This matches only a single
			// quote from the beginning and end of the dataset that was added by PHPUnit itself.
			$name .= ' [ ' . preg_replace('/^"|"$/', '', $dataSet) . ' ]';
		}

		switch ($test->getStatus()) {
			case BaseTestRunner::STATUS_ERROR:
			case BaseTestRunner::STATUS_FAILURE:
				$this->write(' ' . $name);

				break;
			default:
				$this->write(' ' . Color::dim($name));

				break;
		}

		$this->write(' ');

		$timeColor = $time > 0.5 ? 'fg-yellow' : 'fg-white';
		$this->writeWithColor($timeColor, '[' . number_format($time, 3) . 's]', true);
	}

	/**
	 * Print on screen the progress data
	 *
	 * @param string $progress
	 * @return void
	 */
	protected function writeProgress(string $progress): void
	{
		if ($this->previousClassName !== $this->className) {
			$this->write("\n \e[1;30;43m TEST \e[0m ");
			$this->writeWithColor('bold', $this->className, false);
			$this->writeNewLine();
		}

		$this->previousClassName = $this->className;

		$this->printProgress();

		switch (strtoupper(preg_replace('#\\x1b[[][^A-Za-z]*[A-Za-z]#', '', $progress))) {
			case '.':
				$this->writeWithColor('fg-green', '  ✓', false);

				break;
			case 'S':
				$this->writeWithColor('fg-yellow', '  →', false);

				break;
			case 'I':
				$this->writeWithColor('fg-blue', '  ∅', false);

				break;
			case 'F':
				$this->writeWithColor('fg-red', '  x', false);

				break;
			case 'E':
				$this->writeWithColor('fg-red', '  ⚈', false);

				break;
			case 'R':
				$this->writeWithColor('fg-magenta', '  ⌽', false);

				break;
			case 'W':
				$this->writeWithColor('fg-yellow', '  ¤', false);

				break;
			default:
				$this->writeWithColor('fg-cyan', '  ≈', false);

				break;
		}
	}

	/**
	 * Print logiv\c
	 *
	 * @param TestFailure $defect
	 * @return void
	 */
	protected function printDefectTrace(TestFailure $defect): void
	{
		$this->write($this->formatExceptionMsg($defect->getExceptionAsString()));

		$trace = Filter::getFilteredStacktrace(
			$defect->thrownException(),
		);

		if (!empty($trace)) {
			$this->write("\n" . $trace);
		}

		$exception = $defect->thrownException()->getPrevious();

		while ($exception) {
			$this->write(
				"\nCaused by\n" .
					TestFailure::exceptionToString($exception) . "\n" .
					Filter::getFilteredStacktrace($exception),
			);

			$exception = $exception->getPrevious();
		}
	}

	/**
	 * Set coloring format and text of line
	 *
	 * @param mixed $exceptionMessage
	 * @return string
	 */
	protected function formatExceptionMsg($exceptionMessage): string
	{
		$exceptionMessage = str_replace("+++ Actual\n", '', $exceptionMessage);
		$exceptionMessage = str_replace("--- Expected\n", '', $exceptionMessage);
		$exceptionMessage = str_replace('@@ @@', '', $exceptionMessage);

		if ($this->colors) {
			$exceptionMessage = preg_replace('/^(Exception.*)$/m', "\033[01;31m$1\033[0m", $exceptionMessage);
			$exceptionMessage = preg_replace('/(Failed.*)$/m', "\033[01;31m$1\033[0m", $exceptionMessage);
			$exceptionMessage = preg_replace('/(\-+.*)$/m', "\033[01;32m$1\033[0m", $exceptionMessage);
			$exceptionMessage = preg_replace('/(\++.*)$/m', "\033[01;31m$1\033[0m", $exceptionMessage);
		}

		return $exceptionMessage;
	}

	/**
	 * Output the final information
	 *
	 * @return void
	 */
	private function printProgress()
	{
		if (filter_var(getenv('PHPUNIT_PRETTY_PRINT_PROGRESS'), FILTER_VALIDATE_BOOLEAN)) {
			$this->numTestsRun++;

			$total = $this->numTests;
			$current = str_pad($this->numTestsRun, strlen($total), '0', STR_PAD_LEFT);

			$this->write("[{$current}/{$total}]");
		}
	}

	/**
	 * Print final results footer
	 *
	 * @param TestResult $result
	 * @return void
	 */
	protected function printFooter(TestResult $result): void
	{
		if (count($result) === 0) {
			$this->writeWithColor('fg-black, bg-yellow', ' SKIP ', false);
			$this->write(Color::dim(' No tests executed!') . "\n");

			return;
		}

		if ($result->wasSuccessfulAndNoTestIsRiskyOrSkippedOrIncomplete()) {
			$results = sprintf(
				' %d test%s, %d assertion%s',
				count($result),
				(count($result) === 1) ? '' : 's',
				$this->numAssertions,
				($this->numAssertions === 1) ? '' : 's',
			);

			$this->writeWithColor('fg-black, bg-green', ' PASS ', false);
			$this->write(Color::dim($results) . "\n");

			return;
		}

		if ($result->wasSuccessful()) {
			if ($this->verbose || !$result->allHarmless()) {
				$this->write("\n");
			}

			$this->writeWithColor('fg-black, bg-yellow', ' WARN ', false);
			$this->write(Color::dim(' OK, but incomplete, skipped, or risky tests!') . "\n");
		} else {
			$this->write("\n");

			if ($result->errorCount()) {
				$this->writeWithColor('fg-white, bg-red', ' ERRORS ', false);
			} elseif ($result->failureCount()) {
				$this->writeWithColor('fg-white, bg-red', ' FAILURES ', false);
			} elseif ($result->warningCount()) {
				$this->writeWithColor('fg-black, bg-yellow', ' WARNINGS ', false);
			}
		}

		$this->write(' ');
		$this->writeCountString(count($result), 'Tests', true);
		$this->writeCountString($this->numAssertions, 'Assertions', true);
		$this->writeCountString($result->errorCount(), 'Errors');
		$this->writeCountString($result->failureCount(), 'Failures');
		$this->writeCountString($result->warningCount(), 'Warnings');
		$this->writeCountString($result->skippedCount(), 'Skipped');
		$this->writeCountString($result->notImplementedCount(), 'Incomplete');
		$this->writeCountString($result->riskyCount(), 'Risky');
		$this->write(Color::dim(".\n\n"));
	}

	/**
	 * Print individual results by criteria
	 *
	 * @param integer $count
	 * @param string  $name
	 * @param boolean $always
	 * @return void
	 */
	private function writeCountString(int $count, string $name, bool $always = false): void
	{
		static $first = true;

		if ($always || $count > 0) {
			$this->write(Color::dim(sprintf('%s%s: ', !$first ? ', ' : '', $name)));
			$this->write($count);

			$first = false;
		}
	}
}
