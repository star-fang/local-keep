export default class Tree {
    constructor() {
        this.root = null;
        this.size = 0;
    }

    getSize() {
      return this.size;
    }

    inOrderCallback( callback ) {
        const traverse = function ( node ) {
            if ( node !== null ) {

              traverse( node.left );
              if( callback( node ) ) {
                  return;
              }
              traverse( node.right );
            }
          };
        traverse( this.root );
    }

    inOrderWalk() {
        let result = [],
        traverse = function ( node ) {
          if ( node !== null ) {
            traverse( node.left );
            result.push( node.key );
            traverse( node.right );
          }
        };
      traverse( this.root );
      return result;
    }

    preOrderWalk() {
        let result = [],
        traverse = function ( node ) {
          if ( node !== null ) {
            result.push( node.key );
            traverse( node.left );
            traverse( node.right );
          }
        };
      traverse( this.root );
      return result;
    }

    postOrderWalk() {
        let result = [],
        traverse = function ( node ) {
          if ( node !== null ) {
            traverse( node.left );
            traverse( node.right );
            result.push( node.key );
          }
        };
      traverse( this.root );
      return result;
    }

    // Retrieves the minimum key value of nodes in the tree
    minimum( node = this.root ) {
        while ( node.left !== null ) {
            node = node.left;
          }
          return node;
    }

    // Retrieves the maximum key value of nodes in the tree
    maximum( node = this.root ) {
        while ( node.right !== null ) {
            node = node.right;
          }
          return node;
    }

     /**
   * @description If all keys are distinct, the successor of the node is that
   *   which has the smallest key greater than 'node'.
   * @param node {object} Instance of a TreeNode node
   * @returns {*} Returns the successor node for the provided node
   */
  successor( node ) {
    if ( node.right !== null ) {
      return this.minimum( node.right );
    }
    let successor = node.parent;
  
    while ( successor !== null && node === successor.right ) {
      node = successor;
      successor = successor.parent;
    }
    return successor;
  }

  
  /**
   * @description If all keys are distinct, the predecessor of the node is that
   *   which has the smallest key less than 'node'.
   * @param node {object} Instance of a TreeNode node
   * @returns {*} Returns the predecessor node for the provided node
   */
  predecessor( node ) {
    if ( node.left !== null ) {
      return this.maximum( node.left );
    }
    let predecessor = node.parent;
  
    while ( predecessor !== null && node === predecessor.left ) {
      node = predecessor;
      predecessor = predecessor.parent;
    }
    return predecessor;
  }
  
  /**
   * @description Performs a recursive search for the value provided, across all
   *   nodes in the tree, starting from 'node'
   * @param value {*} Key to search for, must match data type of the key
   * @returns {*} Returns the node found matching the key, or null if no node was
   *   found
   */
  get( value ) {
    let node = this.root;
  
    let traverse = function ( node ) {
      if ( !node || node.key === value ) {
        return node;
      }
  
      if ( value < node.key ) {
        return traverse( node.left, value );
      } else {
        return traverse( node.right, value );
      }
    };
  
    return traverse( node );
  };
}