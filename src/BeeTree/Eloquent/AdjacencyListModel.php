<?php namespace BeeTree\Eloquent;

use BeeTree\ModelInterface;
use BeeTree\NodeInterface as Node;
use BeeTree\Helper;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;
use DomainException;
use InvalidArgumentException;

class AdjacencyListModel implements ModelInterface{

    /**
    * @brief The classname of the node
    * @var string
    */
    protected $_nodeClassName;

    /**
    * @brief A node prototype object
    * @var NodeInterface
    */
    protected $_nodePrototype;

    /**
    * @brief A Reflection object of class nodeClassName
    * @var ReflectionClass
    */
    protected $_nodeClassReflection;

    protected $_parentCol;

    protected $_rootCol;

    protected $_idLookup = array();

    protected $_rootNodeCache = array();

    protected $_constraints = array();

    protected $_selectColumns = NULL;

    /**
     * @var \Illuminate\Database\Eloquent\Model
     **/
    protected $model;

    public function nodeClassName()
    {
        return $this->_nodeClassName;
    }

    public function setNodeClassName($className)
    {
        return $this->setModel(new $className);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model
     **/
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return self
     **/
    public function setModel(Model $model)
    {
        $this->checkInterface($model);
        $this->model = $model;
        $this->_nodeClassName = get_class($model);
        $this->_nodePrototype = $this->model;
        $this->_nodeClassReflection = new ReflectionClass($this->model);
        return $this;
    }

    public function getConstraints(){
        return $this->_constraints;
    }

    public function setConstraints($constraints){
        $this->_constraints = array();
        foreach($_constraints as $column=>$value){
            $this->_constraints[$column] = $value;
        }
        return $this;
    }

    public function get($identifier, $rootId=NULL)
    {

        if (isset($this->_idLookup[$identifier])) {
            return $this->_idLookup[$identifier];
        }

        if($rootId){
            $tree = $this->tree($rootId);
        }
        else{
            $result = $this->getHierarchyByIdQuery($identifier)->get($this->getSelectColumns());
            $this->fillNodeCache($result);
            Helper::toHierarchy($result);
        }

        return $this->_idLookup[$identifier];
    }

    /**
     * @brief Retrieve all root nodes
     *
     * @return \Traversable
     **/
    public function rootNodes(){
        return $this->getRootNodesQuery()->get()->all();
    }

    public function makeNode()
    {
        $node = $this->model->newInstance();
        $node->forceFill($this->_constraints);
        return $node;
    }

    public function saveNode(Node $node){
        $node->save();
    }

    public function makeRootNode(Node $node){

        $node->__set($this->parentCol(), NULL);

        $rootId = $node->__get($this->rootCol());

        if(!$rootId){
            $rootId = $node->__get($this->pkCol());
        }

        if($rootId){
            $node->__set($this->rootCol(), $rootId);
        }

        $node->save();

        if(!$rootId){
            $rootId = $node->__get($this->pkCol());
            $node->__set($this->rootCol(), $rootId);
        }

        $node->__set($this->parentCol(), NULL);

        return $this;
    }

    public function makeChildOf(Node $node, Node $newParent){

        // The node is already a child of newParent
        if( ($node->__get($this->parentCol()) == $newParent->__get($this->pkCol())) &&
            $node->exists ){
            return $this;
        }

        $node->__set($this->parentCol(),
                     $newParent->__get($this->pkCol())
        );

        $node->__set($this->rootCol(),
                     $newParent->__get($this->rootCol())
        );

        $node->setParentNode($newParent);
        $newParent->addChildNode($node);

        $node->save();

        return $this;
    }

    public function delete(Node $node, $deleteChildNodes = TRUE){

        if(!$deleteChildNodes){
            throw new DomainException('Non-recursive delete is not supported right now');
        }

        $subtree = $this->get($node->__get($this->pkCol()),
                              $node->__get($this->rootCol()));

        $byDepth = array();

        foreach(Helper::flatify($subtree) as $deleteNode){
            if(!isset($byDepth[$deleteNode->getDepth()])){
                $byDepth[$deleteNode->getDepth()] = array();
            }
            $byDepth[$deleteNode->getDepth()][] = $deleteNode;
        }

        krsort($byDepth, SORT_NUMERIC);

        foreach($byDepth as $depth=>$depthNodes){
            foreach($depthNodes as $deleteNode){
                $deleteNode->delete();
            }
        }
        return $this;
    }

    /**
     * @brief Retrieve a tree by its rootId
     * 
     * @param mixed $rootId The id of its root node, which is the same as node->getRootId()
     * @return NodeInterface
     **/
    public function tree($rootId = NULL){
        if($rootId === NULL){
            $rootId = '__root';
        }

        if(!isset($this->_rootNodeCache[$rootId])){

            $result = $this->getHierarchyByRootIdQuery($rootId)
                           ->get($this->getSelectColumns());

            $this->fillNodeCache($result);

            Helper::toHierarchy($result);

        }

        return $this->_rootNodeCache[$rootId];
    }

    protected function fillNodeCache($result){
        $rootNode = NULL;
        foreach($result as $node){
            $this->_idLookup[$node->getIdentifier()] = $node;
            if($node->isRootNode()){
                $rootNode = $node;
            }
        }
        if(!$rootNode){
            throw new DomainException('No rootnode found');
        }
        $this->_rootNodeCache[$rootNode->__get($this->rootCol())] = $rootNode;
    }

    public function parentCol(){
        if($this->_parentCol === NULL){
            $properties = $this->_nodeClassReflection->getDefaultProperties();
            if(!isset($properties['parentIdColumn'])){
                throw new DomainException("$className has to have a property named 'parentIdColumn' which returns the parent_id column name");
            }
            $this->_parentCol = $properties['parentIdColumn'];
        }
        return $this->_parentCol;
    }

    public function rootCol(){
        if($this->_rootCol === NULL){
            $properties = $this->_nodeClassReflection->getDefaultProperties();
            if(!isset($properties['rootIdColumn'])){
                throw new DomainException("$className has to have a property named 'rootIdColumn' which returns the root_id column name");
            }
            $this->_rootCol = $properties['rootIdColumn'];
        }
        return $this->_rootCol;
    }

    protected function getRootNodesQuery(){

        $table = $this->nodeTable();
        $rootCol = $this->rootCol();

        if(!$this->_constraints){
            $query = call_user_func(array($this->_nodeClassName, 'orderBy'), "$table.$rootCol");
        }
        else{
            $method = array($this->_nodeClassName, 'whereNested');
            $constraints = $this->_constraints;
            $where = call_user_func($method, function($query) use ($constraints){
                foreach($constraints as $column=>$value){
                    $query->where($column,'=',$value);
                }
            });
            $query = $where->orderBy("$table.$rootCol");
        }

        $query = $query->whereNull($this->parentCol());

        return $query;

    }

    protected function getHierarchyByRootIdQuery($rootId){

        $table = $this->nodeTable();
        $tableAlias = $this->nodeTable().'_join';
        $rootCol = $this->rootCol();

        if(!$this->_constraints){
            $query = call_user_func(array($this->_nodeClassName, 'orderBy'), "$table.".$this->parentCol());
        }
        else{
            $method = array($this->_nodeClassName, 'whereNested');
            $constraints = $this->_constraints;
            $where = call_user_func($method, function($query) use ($constraints){
                foreach($constraints as $column=>$value){
                    $query->where($column,'=',$value);
                }
            });
            $query = $where->orderBy($this->parentCol());
        }

        $query = $query->where($this->rootCol(), $rootId);

        return $query;
    }

    protected function getHierarchyByIdQuery($id){
        $table = $this->nodeTable();
        $tableAlias = $this->nodeTable().'_join';
        $rootCol = $this->rootCol();

        if(!$this->_constraints){
            $query = call_user_func(array($this->_nodeClassName, 'orderBy'), "$table.".$this->parentCol());
        }
        else{
            $method = array($this->_nodeClassName, 'whereNested');
            $constraints = $this->_constraints;
            $where = call_user_func($method, function($query) use ($constraints){
                foreach($constraints as $column=>$value){
                    $query->where($column,'=',$value);
                }
            });
            $query = $where->orderBy("$table.".$this->parentCol());
        }

        $query = $query->join("$table as $tableAlias", "$table.$rootCol",'=',"$tableAlias.$rootCol");

        $query = $query->where("$tableAlias.".$this->pkCol(), $id);

        return $query;
    }

    protected function getSelectColumns(){

        if($this->_selectColumns === NULL){
            $properties = $this->_nodeClassReflection->getDefaultProperties();
            if(isset($properties['wholeTreeColumns'])){
                $columns = array();
                $table = $this->nodeTable();
                foreach($properties['wholeTreeColumns'] as $columnName){
                    $columns[] = "$table.$columnName";
                }
                $this->_selectColumns = $columns;
            }
            else{
                $this->_selectColumns = array($this->nodeTable() . '.*');
            }
        }
        return $this->_selectColumns;

    }

    public function nodeTable(){
        return $this->_nodePrototype->getTable();
    }

    public function pkCol(){
        return $this->_nodePrototype->getKeyName();
    }

    protected function checkInterface(Model $model)
    {
        if (!$model instanceof Node) {
            throw new InvalidArgumentException(get_class($model).' has to implement BeeTree\NodeInterface');
        }
    }
}