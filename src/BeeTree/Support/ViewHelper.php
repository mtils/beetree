<?php namespace BeeTree\Support;

use BeeTree\Contracts\Node;

class ViewHelper
{

    public function root(Node $node)
    {
        while ($parent = $node->getParent()) {
            $node = $parent;
            if($parent->isRoot()){
                return $parent;
            }
        }
    }

    public function toUnorderedList(Node $node, callable $liCreator, &$string=NULL)
    {

        if ($string === NULL) {
            $string = '';
        }

        $string .= "\n    " . call_user_func($liCreator, $node);

        if (!$node->hasChildren()) {
            $string .= "</li>";
            return $string;
        }

        $string .= "\n    <ul>";

        foreach ($node->getChildren() as $child) {
            $this->toUnorderedList($child, $liCreator, $string);
        }

        $string .= "\n    </ul>";

        $string .= "</li>";

        return $string;
    }

}