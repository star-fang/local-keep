import TreeNode from "./tree-node.js";
import Tree from "./tree.js";

export default class BST extends Tree {

    insert(node) {
        if (node) {
            if (!this.root) {
                this.root = node;
                this.size++;
            } else {
                let parent = null;
                let current = this.root;

                while (current) {
                    parent = current;
                    current = TreeNode.compare(node.key, current.key) < 0 ? current.left : current.right;
                }

                node.parent = parent;
                if (node.key < parent.key) {
                    node.parent = parent;
                    parent.left = node;// Insert left child
                } else {
                    node.parent = parent;
                    parent.right = node; // Insert right child
                }

                this.size++;
                return node;
            }
        }

        return node;
    }

    delete(node) {
        if (node) {
            if (!node.left) {
                this.transplant(node, node.right);
            } else if (!node.right) {
                this.transplant(node, node.left);
            } else {
                let min = this.minimum(node.right);

                if (min.parent !== node) {
                    this.transplant(min, min.right);
                    min.right = node.right;
                    min.right.parent = min;
                }

                this.transplant(node, min);
                min.left = node.left;
                min.left.parent = min;
            }
            this.size--;
            return node;
        }
        return null;
    }

    search(key) {
        let node = this.root;

        while (node) {
            if (TreeNode.compare(key, node.key) < 0) {
                node = node.left;
            } else if (TreeNode.compare(key, node.key) > 0) {
                node = node.right;
            } else {
                return node;
            }
        }

        if (!node) {
            return null;
        }
    } // search()

    /**
    * @description Transplants a subtree to a new parent. This is used when
    *   deleting nodes, and rearranging the BST
    * @param subtreeA {object} Instance of TreeNode object
    * @param subtreeB {object} Instance of TreeNode object
    */
    transplant(subtreeA, subtreeB) {
        if (!subtreeA.parent) {
            this.root = subtreeB;
        } else if (subtreeA === subtreeA.parent.left) {
            subtreeA.parent.left = subtreeB;
        } else {
            subtreeA.parent.right = subtreeB;
        }

        if (subtreeB) {
            subtreeB.parent = subtreeA.parent;
        }
    }

    /**
     * @description Determines the minimum depth of a binary tree node.
     * @param {object} node The node to check.
     * @return {int} The minimum depth of a binary tree node.
     */
    minDepth(node) {
        return node ? 1 + Math.min(this.minDepth(node.left), this.minDepth(node.right)) : 0;
    };

    /**
     * @description Determines the maximum depth of a binary tree node.
     * @param {object} node The node to check.
     * @return {int} The maximum depth of a binary tree node.
     */
    maxDepth(node) {
        return node ? 1 + Math.max(this.maxDepth(node.left), this.maxDepth(node.right)) : 0;
    };

    /**
     * @description Determines whether a binary tree is balanced.
     * @returns {boolean} Whether the tree is balanced.
     */
    isBalanced() {
        return this.root ? this.maxDepth(this.root) - this.minDepth(this.root) <= 1 : false;
    };

}