<?php namespace BeeTree\Contracts;


interface SortableRepository extends Repository
{

    /**
     * Insert $moved after $newAncestor. The parents of both nodes are the same
     * after this operation
     *
     * @param \BeeTree\Contracts\Sortable $movedNode
     * @param \BeeTree\Contracts\Sortable $newAncestor
     * @return self
     **/
    public function insertAfter(Sortable $movedNode, Sortable $newAncestor);

    /**
     * Insert $moved before $newAncestor. The parents of both nodes are the same
     * after this operation.
     *
     * @param \BeeTree\Contracts\Sortable $movedNode
     * @param \BeeTree\Contracts\Sortable $newAncestor
     * @return self
     **/
    public function insertBefore(Sortable $movedNode, Sortable $newDescendant);

}