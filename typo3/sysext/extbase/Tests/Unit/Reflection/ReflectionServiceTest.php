<?php
namespace TYPO3\CMS\Extbase\Tests\Unit\Reflection;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Extbase\Reflection\ReflectionService;

/**
 * Test case
 * @firsttest test for reflection
 * @anothertest second test for reflection
 * @anothertest second test for reflection with second value
 */
class ReflectionServiceTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    /**
     * @param array $foo The foo parameter
     * @return string
     */
    public function fixtureMethodForMethodTagsValues(array $foo)
    {
    }

    /**
     * @param bool $dummy
     * @param int $foo
     */
    public function fixtureMethodForMethodTagsValuesWithShortTypes($dummy, $foo)
    {
    }

    /**
     * @test
     */
    public function getClassTagsValues()
    {
        $service = new ReflectionService();
        $classValues = $service->getClassTagsValues(static::class);
        $this->assertEquals([
            'firsttest' => ['test for reflection'],
            'anothertest' => ['second test for reflection', 'second test for reflection with second value']
        ], $classValues);
    }

    /**
     * @test
     */
    public function getClassTagValues()
    {
        $service = new ReflectionService();
        $classValues = $service->getClassTagValues(static::class, 'firsttest');
        $this->assertEquals([
            'test for reflection',
        ], $classValues);
    }

    /**
     * @test
     */
    public function hasMethod()
    {
        $service = new ReflectionService();
        $this->assertTrue($service->hasMethod(static::class, 'fixtureMethodForMethodTagsValues'));
        $this->assertFalse($service->hasMethod(static::class, 'notExistentMethod'));
    }

    /**
     * @test
     */
    public function getMethodTagsValues()
    {
        $service = new ReflectionService();
        $tagsValues = $service->getMethodTagsValues(static::class, 'fixtureMethodForMethodTagsValues');
        $this->assertEquals([
            'param' => ['array $foo The foo parameter'],
            'return' => ['string']
        ], $tagsValues);
    }

    /**
     * @test
     */
    public function getMethodParameters()
    {
        $service = new ReflectionService();
        $parameters = $service->getMethodParameters(static::class, 'fixtureMethodForMethodTagsValues');
        $this->assertSame([
            'foo' => [
                'position' => 0,
                'byReference' => false,
                'array' => true,
                'optional' => false,
                'allowsNull' => false,
                'class' => null,
                'type' => 'array'
            ]
        ], $parameters);
    }

    /**
     * @test
     */
    public function getMethodParametersWithShortTypeNames()
    {
        $service = new ReflectionService();
        $parameters = $service->getMethodParameters(static::class, 'fixtureMethodForMethodTagsValuesWithShortTypes');
        $this->assertSame([
            'dummy' => [
                'position' => 0,
                'byReference' => false,
                'array' => false,
                'optional' => false,
                'allowsNull' => true,
                'class' => null,
                'type' => 'boolean'
            ],
            'foo' => [
                'position' => 1,
                'byReference' => false,
                'array' => false,
                'optional' => false,
                'allowsNull' => true,
                'class' => null,
                'type' => 'integer'
            ]
        ], $parameters);
    }
}
