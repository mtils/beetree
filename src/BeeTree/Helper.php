<?php namespace BeeTree;

use DomainException;
use RuntimeException;

class Helper{

    public static function flatify(NodeInterface $node, &$flatArray=NULL){

        if($flatArray === NULL){
            $flatArray = array();
        }

        $flatArray[] = $node;

        foreach($node->childNodes() as $child){
            static::flatify($child, $flatArray);
        }

        return $flatArray;
    }

    public static function ids($node){
        $ids = array();
        foreach(self::flatify($node) as $node){
            $ids[] = $node->getIdentifier();
        }
        return $ids;
    }

    public static function childIds($node, $recursive=TRUE){
        if(!$recursive){
            $ids = array();
            foreach($node->childNodes() as $child){
                $ids[] = $child->getIdentifier();
            }
            return $ids;
        }

        return array_filter(self::ids($node),function($id) use ($node){
            if($id == $node->getIdentifier()){
                return FALSE;
            }
            return TRUE;
        });
    }

    public static function toHierarchy($flatIterable){

        $nodesByIdentifier = array();
        $root = NULL;

        //First all into array by id
        foreach($flatIterable as $node){

            if(!$node instanceof NodeInterface){
                throw new DomainException(__METHOD__." works only with NodeInterface");
            }

            if($node->isRootNode()){
                $root = $node;
            }

            $nodesByIdentifier[$node->getIdentifier()] = $node;
        }

        foreach($nodesByIdentifier as $id=>$node){

            // Assign Parent/Child relationship
            $parentId = $node->getParentIdentifier();

            if($parentId && isset($nodesByIdentifier[$parentId])){
                $nodesByIdentifier[$parentId]->addChildNode($node);
                $node->setParentNode($nodesByIdentifier[$parentId]);
            }
        }

        if($root === NULL){
            throw new RuntimeException('No Rootnode found');
        }

        return $root;
    }

}