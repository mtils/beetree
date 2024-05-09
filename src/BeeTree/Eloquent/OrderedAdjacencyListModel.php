<?php namespace BeeTree\Eloquent;

use BeeTree\ModelInterface;
use BeeTree\NodeInterface;
use BeeTree\Helper;
use Illuminate\Database\Eloquent\Model;
use \ReflectionClass;
use \DomainException;
use DB;

use function get_class;

class OrderedAdjacencyListModel extends AdjacencyListModel implements ModelInterface{

    protected $_sortCol;

    public function insertAfter(NodeInterface $movedNode,
                                NodeInterface $newAncestor) {

    }

    public function insertBefore(NodeInterface $movedNode,
                                 NodeInterface $newDescendant) {
        // 1. movedNode.parentId = newDescendant.parentId, movedNode.position = MAX(newDescendant.position,1)
        // 2. removeFromSort(movedNode) like delete without delete
        // 2. UPDATE SET position = position+1 WHERE parentId = newAncestor.parentId
        //    AND position >= movedNode.position
        // 3. movedNode.save()

        $targetParentId = $newDescendant->__get($this->parentCol());
        $targetPosition = max($newDescendant->__get($this->sortCol()),1);
        $oldParentId = $movedNode->__get($this->parentCol());
        $oldPosition = $movedNode->__get($this->sortCol());

        // Nothing to do
        if($targetParentId == $oldParentId && $targetPosition == $oldPosition && $movedNode->exists){
            return $this;
        }

        $movedNode->__set($this->parentCol(), $targetParentId);
        $movedNode->__set($this->sortCol(), $targetPosition);

        // Remove node from old parent
        $this->decrementOrderAfter($oldParentId, $oldPosition);

        // Create space for new position
        $this->incrementOrderAfter($targetParentId, $targetPosition-1);

        $movedNode->save();

        return $this;

    }

    public function makeChildOf(NodeInterface $node, NodeInterface $newParent){

        // Collect some informations before the node is changed to correct
        // the nodes in its old position
        $nodeWasNew = !$node->exists;
        $oldParentId = $node->__get($this->parentCol());
        $oldPosition = $node->__get($this->sortCol());

        // Set the position of this node. Default is last position in new Parent
        $node->__set($this->sortCol(), $this->getLastChildPosition($node, $newParent));

        parent::makeChildOf($node, $newParent);

        // If the node existed correct the positions of the old parent
        if(!$nodeWasNew && $oldParentId){
            $this->decrementOrderAfter($oldParentId, $oldPosition);
        }

        return $this;
    }

    public function delete(NodeInterface $node, $deleteChildNodes = true)
    {

        // Collect the parentId to correct the positions of its old ancestors
        $parentId = $node->__get($this->parentCol());
        $position = $node->__get($this->sortCol());

        parent::delete($node, $deleteChildNodes);

        if($parentId){
            $this->decrementOrderAfter($parentId, $position);
        }

        return $this;
    }

    protected function getLastChildPosition(NodeInterface $node, NodeInterface $newParent)
    {

        $pos = $this->getModel()->newQuery()
            ->where($this->parentCol(), '=', $newParent->__get($this->pkCol()))
            ->max($this->sortCol());

        if(is_numeric($pos) && $pos){
            return (int)$pos+1;
        }

        return 1;
    }

    protected function getHierarchyByRootIdQuery($id)
    {
        return parent::getHierarchyByRootIdQuery($id)->orderBy($this->nodeTable().'.'.$this->sortCol());
    }

    protected function getHierarchyByIdQuery($id){
        return parent::getHierarchyByIdQuery($id)->orderBy($this->nodeTable().'.'.$this->sortCol());
    }

    protected function incrementOrderAfter($parentId, $position)
    {
        $connection = $this->getModel()->getConnection();
        $connection->table($this->nodeTable())
            ->where($this->parentCol(),'=',$parentId)
            ->where($this->sortCol(),'>',$position)
            ->increment($this->sortCol());

    }

    protected function decrementOrderAfter($parentId, $position)
    {
        $connection = $this->getModel()->getConnection();
        $connection->table($this->nodeTable())
            ->where($this->parentCol(),'=',$parentId)
            ->where($this->sortCol(),'>',$position)
            ->decrement($this->sortCol());

    }

    public function sortCol()
    {
        if (!$this->_sortCol !== null) {
            return $this->_sortCol;
        }
        $properties = $this->_nodeClassReflection->getDefaultProperties();
        if(!isset($properties['sortColumn'])){
            $className = get_class($this);
            throw new DomainException("$className has to have a property named 'sortColumn' which returns the sort column name");
        }
        $this->_sortCol = $properties['sortColumn'];
        return $this->_sortCol;
    }
}