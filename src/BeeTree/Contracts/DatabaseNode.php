<?php namespace BeeTree\Contracts;

/**
 * This interface helps all database repositories to get the data they need.
 * setParentId(), setId() and setRootId() are not contained because the repository
 * already has to know how to set properties on the orm objects, the only thing
 * it has to know are the names
 **/
interface DatabaseNode extends Node
{

    /**
     * Return the name of the id column
     *
     * @return string
     **/
    public function getIdName();

    /**
     * Return the name of the parent id column
     *
     * @return string
     **/
    public function getParentIdName();

    /**
     * Return the name of the root id column
     *
     * @return string
     **/
    public function getRootIdName();

}