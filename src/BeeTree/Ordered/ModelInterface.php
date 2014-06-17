<?php namespace BeeTree\Ordered;

use BeeTree\ModelInterface as BaseModelInterface;
use BeeTree\NodeInterface;

interface ModelInterface extends BaseModelInterface{
    public function insertAfter(NodeInterface $movedNode,
                                NodeInterface $newAncestor);
    public function insertBefore(NodeInterface $movedNode,
                                 NodeInterface $newDescendant);
}