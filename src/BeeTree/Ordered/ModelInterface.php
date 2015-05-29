<?php namespace BeeTree\Ordered;


use BeeTree\ModelInterface as BaseModelInterface;
use BeeTree\NodeInterface as Node;

interface ModelInterface extends BaseModelInterface
{

    /**
     * Insert $moved after $newAncestor. The parents of both nodes are the same
     * after this operation
     *
     * @param \BeeTree\NodeInterface $movedNode
     * @param \BeeTree\NodeInterface $newAncestor
     * @return self
     **/
    public function insertAfter(Node $movedNode, Node $newAncestor);

    /**
     * Insert $moved before $newAncestor. The parents of both nodes are the same
     * after this operation.
     *
     * @param \BeeTree\NodeInterface $movedNode
     * @param \BeeTree\NodeInterface $newAncestor
     * @return self
     **/
    public function insertBefore(Node $movedNode, Node $newDescendant);

}