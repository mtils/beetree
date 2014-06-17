<?php namespace BeeTree\Eloquent;

use Eloquent;
use BeeTree\NodeInterface;

class BeeTreeNode extends Eloquent implements NodeInterface{

    protected $_parentNode;
    
    protected $_childNodes = array();

    protected $_depth;

    public $parentIdColumn = 'parent_id';

    public $rootIdColumn = 'root_id';

    /**
    * @brief Returns if node is a root node
    * 
    * @return void
    */
    public function isRootNode(){
        if(!$this->getParentIdentifier()){
            return TRUE;
        }
        return FALSE;
    }

    /**
    * @brief Returns the parent node of this node
    * 
    * @return NodeInterface
    */
    public function parentNode(){
        return $this->_parentNode;
    }

    /**
    * @brief Returns the parent node of this node
    * 
    * @return NodeInterface
    */
    public function setParentNode(NodeInterface $parent){
        $this->_parentNode = $parent;
        return $this;
    }

    /**
    * @brief Returns the childs of this node
    * 
    * @return array [NodeInterface]
    */
    public function childNodes(){
        return $this->_childNodes;
    }

    /**
    * @brief Clears all childNodes
    * 
    * @return array [NodeInterface]
    */
    public function clearChildNodes(){
        $this->_childNodes = array();
        return $this;
    }

    /**
    * @brief Adds a childNode to this node
    * 
    * @return NodeInterface
    */
    public function addChildNode(NodeInterface $childNode){
        $this->_childNodes[] = $childNode;
        return $this;
    }

    /**
    * @brief Removes a child node
    * 
    * @return NodeInterface
    */
    public function removeChildNode(NodeInterface $childNode){
        $deleteIndex = -1;
        $idx=0;
        foreach($this->_childNodes as $child){
            if($child->getIdentifier() == $childNode->getIdentifier()){
                $deleteIndex = $idx;
                break;
            }
            $idx++;
        }
        if($deleteIndex != -1){
            unset($this->_childNodes[$deleteIndex]);
            $this->_childNodes = array_values($this->_childNodes);
        }
        return $this;
    }

    /**
    * @brief Does this node have children?
    * 
    * @return bool
    */
    public function hasChildNodes(){
        return (bool)count($this->_childNodes);
    }

    /**
    * @brief Does this node has a parent?
    * 
    * @return bool
    */
    public function hasParentNode(){
        if($this->getParentIdentifier()){
            return TRUE;
        }
        return FALSE;
    }

    /**
    * @brief Returns the depth of this node
    * 
    * @return int
    */
    public function getDepth(){

        if($this->_depth === NULL){

            if($this->isRootNode()){
                $this->_depth = -1;
            }
            else{
                $parents = array($this);
                $node = $this;
                while($parent = $node->parentNode()){
                    if(!$parent->isRootNode()){
                        $parents[] = $parent;
                    }
                    $node = $parent;
                }
                $this->_depth = count($parents);
            }
        }

        return $this->_depth;
    }

    /**
    * @brief Set the depth of this node (usually done by BeeTreeModel)
    *
    * @param int $depth
    * @return NodeInterface
    */
    public function setDepth($depth){
        $this->_depth = $depth;
        return $this;
    }

    /**
    * @brief Returns the identifier of this node
    *        Identifiers are used to compare nodes and deceide which
    *        child depends to which parent.
    *        In a filesystem the path would be the identifier, in
    *        a database a id column.
    * 
    * @return mixed
    */
    public function getIdentifier(){
        return $this->__get($this->getKeyName());
    }

    /**
    * @brief Returns the identifier of the parent
    * 
    * @return mixed
    */
    public function getParentIdentifier(){
        return $this->__get($this->parentIdColumn);
    }
}