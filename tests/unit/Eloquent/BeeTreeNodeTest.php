<?php

namespace BeeTree\Test\Eloquent;

use BeeTree\Eloquent\BeeTreeNode;
use PHPUnit\Framework\TestCase;

/**
 * Class BeeTreeNodeTest
 * This class implements the test cases for the `isRootNode` method of the `BeeTreeNode` class.
 * BeeTreeNode implements NodeInterface. A node can be root if it does not have a parent.
 *
 * @package BeeTree\Test\Eloquent
 */
class BeeTreeNodeTest extends TestCase
{
    /**
     * Tests whether the `isRootNode` method returns true when a node
     * does not have parent node (i.e., it is a root node).
     */
    public function testIsRootNodeWhenNodeHasNoParent(): void
    {
        $node = new BeeTreeNode();
        $this->assertTrue($node->isRootNode());
    }

    /**
     * Tests whether the `isRootNode` method returns false when a node
     * has a parent node (i.e., it is not a root node).
     */
    public function testIsRootNodeWhenNodeHasParent(): void
    {
        $node = new BeeTreeNode();
        $node->__set($node->parentIdColumn, 12);
        $this->assertFalse($node->isRootNode());
    }
}