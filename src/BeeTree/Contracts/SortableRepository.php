<?php namespace BeeTree\Contracts;


interface SortableRepository extends Repository
{

    /**
     * Create a node with $attributes in $parent at $position
     *
     * @param array $attributes
     * @param \BeeTree\Contracts\Sortable
     * @param int $position
     * @return self The created sortable
     **/
    public function createAt(array $attributes, Sortable $parent, $position);

    /**
     * Move the $movedNode inside $newParent to position $position
     *
     * @param \BeeTree\Contracts\Sortable $movedNode
     * @param \BeeTree\Contracts\Sortable $newParent
     * @param int $position
     * @return self
     **/
    public function placeAt(Sortable $movedNode, Sortable $newParent, $position);

}