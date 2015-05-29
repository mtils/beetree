<?php namespace BeeTree\Contracts;


interface SortableNodeRepository extends Repository
{

    /**
     * Insert $moved after $newAncestor. The parents of both nodes are the same
     * after this operation
     *
     * @param \BeeTree\Contracts\Node $movedNode
     * @param \BeeTree\Contracts\Node $newAncestor
     * @return self
     **/
    public function insertAfter(Node $movedNode, Node $newAncestor);

    /**
     * Insert $moved before $newAncestor. The parents of both nodes are the same
     * after this operation.
     *
     * @param \BeeTree\Contracts\Node $movedNode
     * @param \BeeTree\Contracts\Node $newAncestor
     * @return self
     **/
    public function insertBefore(Node $movedNode, Node $newDescendant);

}