<?php

namespace Kambo\Tests\Karsk\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;

use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\Factory as ComparatorFactory;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;

/**
 * Constraint for execution an external program.
 *
 * @author  Bohuslav Simek <bohuslav@simek.si>
 * @license BSD-3-Clause
 */
class ExecutionResult extends Constraint
{
    /**
     *
     *
     * @var array
     */
    private $command;

    /**
     *
     *
     * @var array
     */
    private $executionResult = [];

    public function __construct($command)
    {
        parent::__construct();

        $this->command = $command;
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param mixed $other Value or object to evaluate.
     *
     * @return bool
     */
    public function matches($other) : bool
    {
        exec(
            $this->command,
            $this->executionResult
        );

        $comparatorFactory = ComparatorFactory::getInstance();

        try {
            $comparator = $comparatorFactory->getComparatorFor(
                $this->executionResult,
                $other
            );

            $comparator->assertEquals(
                $this->executionResult,
                $other
            );
        } catch (ComparisonFailure $f) {
            return false;
        }

        return true;
    }

    /**
     * Throws an exception for the given compared value and test description
     *
     * @param mixed             $other             evaluated value or object
     * @param string            $description       Additional information about the test
     * @param ComparisonFailure $comparisonFailure
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    protected function fail($other, $description, ComparisonFailure $comparisonFailure = null) : void
    {
        $failureDescription = 'Failed asserting that the returned value from execution is equal to provided value.';

        $additionalFailureDescription = $this->additionalFailureDescription($other);

        if ($additionalFailureDescription) {
            $failureDescription .= "\n" . $additionalFailureDescription;
        }

        if (!empty($description)) {
            $failureDescription = $description . "\n" . $failureDescription;
        }

        throw new ExpectationFailedException(
            $failureDescription,
            $comparisonFailure
        );
    }

    /**
     * Returns a string representation of the object.
     */
    public function toString() : string
    {
        return '';
    }

    protected function additionalFailureDescription($other) : string
    {
        $diffBuilder = new UnifiedDiffOutputBuilder("--- Expected\n+++ Actual\n");

        $differ = new Differ($diffBuilder);

        foreach ($this->executionResult as &$line) {
            $line = $line. "\n";
        }

        foreach ($other as &$line) {
            $line = $line. "\n";
        }

        return $differ->diff($other, $this->executionResult);
    }
}
