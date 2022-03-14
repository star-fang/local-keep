export default class TreeNode {
    constructor(data, key) {
        this.data = data;
        this.key = key;
        this.parent = null;
        this.left = null;
        this.right = null;
        this.height = 1;
    }

    leftHeight() {
        return this.left ? this.left.height : -1;
    }

    rightHeight() {
        return this.right ? this.right.height : -1;
    }

    rotateWithLeftChild() {
        let tempNode = this.left;
        this.left = tempNode.right;
        tempNode.right = this;
        this.height = TreeNode.getMaxHeight(this.leftHeight(), this.rightHeight());
        tempNode.height = TreeNode.getMaxHeight(tempNode.leftHeight(), this.height);
        return tempNode;
    }

    rotateWithRightChild() {
        let tempNode = this.right;

        this.right = tempNode.left;
        tempNode.left = this;
        this.height = TreeNode.getMaxHeight(this.leftHeight(), this.rightHeight());
        tempNode.height = TreeNode.getMaxHeight(tempNode.rightHeight(), this.height);
        return tempNode;
    }

    doubleRotateLeft() {
        this.left = this.left.rotateWithRightChild();
        return this.rotateWithLeftChild();
    }

    doubleRotateRight() {
        this.right = this.right.rotateWithLeftChild();
        return this.rotateWithRightChild();
    }

    static getMaxHeight(a, b) {
        return Math.max(a, b) + 1;
    }

    /**
    * @description Compares keys of objects
    * @param a {int} Key from first object to compare
    * @param b {int} Key from second object to compare
    * @returns {number} Returns 1 if a > b, -1 if a < b, and 0 if a === b
    */
    static compare(a, b) {
        return a - b;
    };
}