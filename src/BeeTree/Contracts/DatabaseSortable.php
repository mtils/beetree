<?php namespace BeeTree\Contracts;

interface DatabaseSortable extends Sortable
{

    /**
     * Return the name of the position column
     *
     * @return string
     **/
    public function getPositionName();

}