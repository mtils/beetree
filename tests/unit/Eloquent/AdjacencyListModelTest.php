<?php

namespace BeeTree\Test\Eloquent;

use BeeTree\Eloquent\AdjacencyListModel;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use Mockery;
use TypeError;

/**
 * Class AdjacencyListModelTest
 * This test class is dedicated to test the methods of the AdjacencyListModel class
 *
 * @package BeeTree\Test\Eloquent
 */
class AdjacencyListModelTest extends TestCase
{
    /**
     * This method tests the setNodeClassName method to see if correctly sets the model by class name
     */
    public function testSetNodeClassName(): void
    {

        $model = new AdjacencyListModel();
        $model->setNodeClassName(FakeNode::class);
        $this->assertEquals(FakeNode::class, $model->nodeClassName(FakeNode::class));
        $this->assertInstanceOf(FakeNode::class, $model->getModel());

    }

    /**
     * This method tests the case of providing a wrong className to setNodeClassName
     */
    public function testSetNodeClassNameWithNonExistentClass(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $adjacencyListModel = new AdjacencyListModel();
        $adjacencyListModel->setNodeClassName('NonExistentClass');
    }

    /**
     * This method tests the case of providing a class that does not inherit Model to setNodeClassName
     */
    public function testSetNodeClassNameWithNotModelClass(): void
    {
        $this->expectException(TypeError::class);

        $adjacencyListModel = new AdjacencyListModel();
        $adjacencyListModel->setNodeClassName(self::class); // Reuse current class which is not a Model
    }

}