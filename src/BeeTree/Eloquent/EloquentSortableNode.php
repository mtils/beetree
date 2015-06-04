<?php namespace BeeTree\Eloquent;

use BeeTree\Contracts\DatabaseNode;
use BeeTree\Contracts\DatabaseSortable;

class EloquentSortableNode implements DatabaseNode, DatabaseSortable
{
    use ActAsEloquentSortableNode;
}