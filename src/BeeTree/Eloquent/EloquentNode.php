<?php namespace BeeTree\Eloquent;

use BeeTree\Contracts\DatabaseNode;

class EloquentNode implements DatabaseNode
{
    use ActAsEloquentNode;
}