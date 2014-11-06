<?php namespace BeeTree\Support;

use BeeTree\NodeInterface;

class HtmlPrinter{

    public function breadcrumbs(NodeInterface $node){

        $breadcrumbs = array();

        $breadcrumbs[] = $node;
        while($parent = $node->parentNode()){
            $breadcrumbs[] = $parent;
            $node = $parent;
            if($parent->isRootNode()){
                break;
            }
        }

        return array_reverse($breadcrumbs);
    }

    public function toJsTree(NodeInterface $node, $titleProperty, $currentIdentifier='', &$string=NULL){

        if($string === NULL){
            $string = '<ul>';
        }

        $liClasses = ['jstree-open'];

        if($node->isRootNode()){
            $liClasses[] = 'root-node';
        }

        $spanClasses = [];

        if($currentIdentifier == $node->getIdentifier()){
            $spanClasses[] = 'active';
        }

        $liClass = implode(' ', $liClasses);
        $spanClass = implode(' ', $spanClasses);

        $title = $node->$titleProperty;
        $id = $node->getIdentifier();

        $string .= "\n    <li id=\"node-{$id}\" class=\"$liClass\"><span class=\"$spanClass\">{$title}</span>";

        if(count($node->childNodes())){
            $string .= "\n    <ul>";
            foreach($node->childNodes() as $child){
                $this->toJsTree($child, $titleProperty, $currentIdentifier, $string);
            }
            $string .= "\n    </ul>";
        }

        $string .= "</li>";

        return $string . "\n</ul>";
    }
}