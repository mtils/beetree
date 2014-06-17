BeeTree
=======

A generic library to work with b-trees.

This library offers a few interfaces to work with b-trees.

Until now there are only two implementations of such a b-tree model:

   * Eloquent\AdjacencyListModel and
   * Eloquent\OrderedAdjacencyListModel

I needed this libs for a few database tasks which did not allow nested set or other implementations. One big disadvantage of nested set and especially ClosureTable are the dependecies of all nodes among themselves.

If you like to copy one part of a tree into another tree of another table this is getting very funny with dependencies of lft, rgt or closuretables. So this library is to have a common interface to access trees and in each case you can deceide to use whatever tree implementation without changing the surrounding logic.

The interfaces have slightly different names of other implementations like `etrepat/baum` or `franzose/ClosureTable`. This is intended to write the interface implementations into your existing classes.
