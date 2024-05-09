<?php namespace BeeTree\Support;


use OutOfBoundsException;
use BeeTree\Contracts\Node;
use BeeTree\Contracts\DatabaseNode;

class Sorter
{

    public function toHierarchy($iterable)
    {

        $nodesById = $this->byId($iterable);
        $root = NULL;

        foreach ($nodesById as $id=>$node) {

            $parentId = $node->getParentId();

            if ($node->isRoot()) {
                $root = $node;
            }

            if ($parentId && isset($nodesById[$parentId])) {
                $node->setParent($nodesById[$parentId]);
            }
            if ($node instanceof DatabaseNode) {
                $node->setTreeIsPopulated(true);
            }

        }

        if (!$root) {
            throw new OutOfBoundsException("No root node found");
        }

        return $root;
    }

    public function flatify(Node $node, &$flatArray=NULL){

        if($flatArray === NULL){
            $flatArray = [];
        }

        $flatArray[] = $node;

        if (!$node->hasChildren()) {
            return $flatArray;
        }

        foreach($node->getChildren() as $child){
            $this->flatify($child, $flatArray);
        }

        return $flatArray;
    }

    public function ids($node)
    {
        return array_map(function($node){
            return $node->getId();
        }, $this->flatify($node));
    }

    public function byId($iterable)
    {
        $byId = [];
        foreach ($iterable as $node) {
            $byId[$node->getId()] = $node;
        }
        return $byId;
    }

    public function byDepth($iterable)
    {

        $byDepth = [];

        foreach ($iterable as $node) {

            $depth = $node->getLevel();

            if (!isset($byDepth[$depth])) {
                $byDepth[$depth] = [];
            }

            $byDepth[$depth][] = $node;
        }

        return $byDepth;

    }

    public function byDepthReversed($iterable)
    {
        $byDepth = $this->byDepth($iterable);
        krsort($byDepth, SORT_NUMERIC);
        return $byDepth;
    }

    public function childIds($node, $recursive=true)
    {
        if (!$recursive) {
            return $this->ids($node);
        }

        return array_filter(self::ids($node), function($id) use ($node) {
            if($id == $node->getId()){
                return false;
            }
            return true;
        });
    }

    public static function __callStatic($method, array $params=[])
    {
        return call_user_func_array([new static, $method], $params);
    }

}