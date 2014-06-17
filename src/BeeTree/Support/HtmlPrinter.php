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

    public function ul(NodeInterface $node, $titleProperty, $currentIdentifier='', &$string=NULL){

        if($string === NULL){
            $string = '<ul>';
        }

        $liClasses = array('jstree-open');

        if($node->isRootNode()){
            $liClasses[] = 'root-node';
        }

        if($currentIdentifier == $node->getIdentifier()){
            $liClasses[] = 'active';
        }

        $classes = implode(' ', $liClasses);
        $title = $node->$titleProperty;
        $id = $node->getIdentifier();

        $string .= "\n    <li id=\"node-{$id}\" class=\"$classes\">{$title}";

        if(count($node->childNodes())){
            $string .= "\n    <ul>";
            foreach($node->childNodes() as $child){
                $this->ul($child, $titleProperty, $currentIdentifier, $string);
            }
            $string .= "\n    </ul>";
        }

        $string .= "</li>";

        return $string . "\n</ul>";
    }
}