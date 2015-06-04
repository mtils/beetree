<?php namespace BeeTree\Eloquent\AdjacencyList;

use Illuminate\Database\Eloquent\Model;
use BeeTree\Contracts\Node;
use BeeTree\Contracts\Sortable;
use BeeTree\Contracts\SortableRepository;
use BeeTree\Eloquent\ActAsEloquentSortableNode;

class WholeOrderedTreeModel extends Model implements Node, Sortable, SortableRepository
{
    use ActAsEloquentSortableNode;
    use WholeOrderedTreeModelTrait;
}