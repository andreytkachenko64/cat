<?php
/*
 * This file is part of ONP.
 *
 * Copyright (c) 2013 Opensoft (http://opensoftdev.com)
 *
 * The unauthorized use of this code outside the boundaries of
 * Opensoft is prohibited.
 *
 */

namespace AT\CoreBundle\Generator;

use AT\CoreBundle\Interfaces\GeneratorInterface;
use AT\CoreBundle\Model\Node;
use AT\CoreBundle\Model\NodeList;

/**
 * AT\CoreBundle\Generator\PHPGenerator
 *
 * @author Andrey Tkachenko <andrey.tkachenko@opensoftdev.ru>
 */
class PHPGenerator implements GeneratorInterface
{
    public function generate(NodeList $statements, array $options = array()){
        $p = trim($this->prettyPrint($statements));

        $p = preg_replace('/^\?>\n?/', '', $p, -1, $count);
        $p = preg_replace('/<\?php$/', '', $p);

        if (!$count) {
            $p = "<?php\n\n" . $p;
        }

        return $p;
    }

    protected $precedenceMap = array(
        // [precedence, associativity] where for the latter -1 is %left, 0 is %nonassoc and 1 is %right
        'Expr_BitwiseNot'       => array( 1,  1),
        'Expr_PreInc'           => array( 1,  1),
        'Expr_PreDec'           => array( 1,  1),
        'Expr_PostInc'          => array( 1, -1),
        'Expr_PostDec'          => array( 1, -1),
        'Expr_UnaryPlus'        => array( 1,  1),
        'Expr_UnaryMinus'       => array( 1,  1),
        'Expr_Cast_Int'         => array( 1,  1),
        'Expr_Cast_Double'      => array( 1,  1),
        'Expr_Cast_String'      => array( 1,  1),
        'Expr_Cast_Array'       => array( 1,  1),
        'Expr_Cast_Object'      => array( 1,  1),
        'Expr_Cast_Bool'        => array( 1,  1),
        'Expr_Cast_Unset'       => array( 1,  1),
        'Expr_ErrorSuppress'    => array( 1,  1),
        'Expr_Instanceof'       => array( 2,  0),
        'Expr_BooleanNot'       => array( 3,  1),
        'Expr_Mul'              => array( 4, -1),
        'Expr_Div'              => array( 4, -1),
        'Expr_Mod'              => array( 4, -1),
        'Expr_Plus'             => array( 5, -1),
        'Expr_Minus'            => array( 5, -1),
        'Expr_Concat'           => array( 5, -1),
        'Expr_ShiftLeft'        => array( 6, -1),
        'Expr_ShiftRight'       => array( 6, -1),
        'Expr_Smaller'          => array( 7,  0),
        'Expr_SmallerOrEqual'   => array( 7,  0),
        'Expr_Greater'          => array( 7,  0),
        'Expr_GreaterOrEqual'   => array( 7,  0),
        'Expr_Equal'            => array( 8,  0),
        'Expr_NotEqual'         => array( 8,  0),
        'Expr_Identical'        => array( 8,  0),
        'Expr_NotIdentical'     => array( 8,  0),
        'Expr_BitwiseAnd'       => array( 9, -1),
        'Expr_BitwiseXor'       => array(10, -1),
        'Expr_BitwiseOr'        => array(11, -1),
        'Expr_BooleanAnd'       => array(12, -1),
        'Expr_BooleanOr'        => array(13, -1),
        'Expr_Ternary'          => array(14, -1),
        // parser uses %left for assignments, but they really behave as %right
        'Expr_Assign'           => array(15,  1),
        'Expr_AssignRef'        => array(15,  1),
        'Expr_AssignPlus'       => array(15,  1),
        'Expr_AssignMinus'      => array(15,  1),
        'Expr_AssignMul'        => array(15,  1),
        'Expr_AssignDiv'        => array(15,  1),
        'Expr_AssignConcat'     => array(15,  1),
        'Expr_AssignMod'        => array(15,  1),
        'Expr_AssignBitwiseAnd' => array(15,  1),
        'Expr_AssignBitwiseOr'  => array(15,  1),
        'Expr_AssignBitwiseXor' => array(15,  1),
        'Expr_AssignShiftLeft'  => array(15,  1),
        'Expr_AssignShiftRight' => array(15,  1),
        'Expr_LogicalAnd'       => array(16, -1),
        'Expr_LogicalXor'       => array(17, -1),
        'Expr_LogicalOr'        => array(18, -1),
    );

    protected $noIndentToken;
    protected $canUseSemicolonNamespaces;

    public function __construct() {
        $this->noIndentToken = '_NO_INDENT_' . mt_rand();
    }

    /**
     * Pretty prints an array of statements.
     *
     * @param NodeList $stmts Array of statements
     *
     * @return string Pretty printed statements
     */
    public function prettyPrint(NodeList $stmts) {
        $this->preprocessNodes($stmts);

        return str_replace("\n" . $this->noIndentToken, "\n", $this->pStmts($stmts, false));
    }

    /**
     * Pretty prints an expression.
     *
     * @param Node $node Expression node
     *
     * @return string Pretty printed node
     */
    public function prettyPrintExpr(Node $node) {
        return str_replace("\n" . $this->noIndentToken, "\n", $this->p($node));
    }

    /**
     * Preprocesses the top-level nodes to initialize pretty printer state.
     *
     * @param NodeList|Node[] $nodes Array of nodes
     */
    protected function preprocessNodes(NodeList $nodes) {
        /* We can use semicolon-namespaces unless there is a global namespace declaration */
        $this->canUseSemicolonNamespaces = true;
        foreach ($nodes as $node) {
            if ($node->getName() == 'Stmt_Namespace' && null === $node['name']) {
                $this->canUseSemicolonNamespaces = false;
            }
        }
    }

    /**
     * Pretty prints an array of nodes (statements) and indents them optionally.
     *
     * @param NodeList|Node[] $nodes  Array of nodes
     * @param bool             $indent Whether to indent the printed nodes
     *
     * @return string Pretty printed statements
     */
    protected function pStmts(NodeList $nodes, $indent = true) {
        $pNodes = array();
        foreach ($nodes as $node) {
            $pNodes[] = $this->pComments($node->getMeta('comments', array()))
                . $this->p($node)
                . (substr($node->getName(), 0, 5) == 'Expr_' ? ';' : '');
        }

        if ($indent) {
            return '    ' . preg_replace(
                '~\n(?!$|' . $this->noIndentToken . ')~',
                "\n" . '    ',
                implode("\n", $pNodes)
            );
        } else {
            return implode("\n", $pNodes);
        }
    }

    /**
     * Pretty prints a node.
     *
     * @param Node $node Node to be pretty printed
     *
     * @return string Pretty printed node
     */
    protected function p($node) {
        if($node instanceof Node){
            return $this->{'p' . $node->getName()}($node);
        } else if($node instanceof NodeList){
            // impossible situation
        } else {
            return $node;
        }
    }



    protected function pInfixOp($type, Node $leftNode, $operatorString, Node $rightNode) {
        list($precedence, $associativity) = $this->precedenceMap[$type];

        return $this->pPrec($leftNode, $precedence, $associativity, -1)
        . $operatorString
        . $this->pPrec($rightNode, $precedence, $associativity, 1);
    }

    protected function pPrefixOp($type, $operatorString, Node $node) {
        list($precedence, $associativity) = $this->precedenceMap[$type];
        return $operatorString . $this->pPrec($node, $precedence, $associativity, 1);
    }

    protected function pPostfixOp($type, Node $node, $operatorString) {
        list($precedence, $associativity) = $this->precedenceMap[$type];
        return $this->pPrec($node, $precedence, $associativity, -1) . $operatorString;
    }

    /**
     * Prints an expression node with the least amount of parentheses necessary to preserve the meaning.
     *
     * @param Node $node                Node to pretty print
     * @param int            $parentPrecedence    Precedence of the parent operator
     * @param int            $parentAssociativity Associativity of parent operator
     *                                            (-1 is left, 0 is nonassoc, 1 is right)
     * @param int            $childPosition       Position of the node relative to the operator
     *                                            (-1 is left, 1 is right)
     *
     * @return string The pretty printed node
     */
    protected function pPrec(Node $node, $parentPrecedence, $parentAssociativity, $childPosition) {
        $name = $node->getName();

        if (isset($this->precedenceMap[$name])) {
            $childPrecedence = $this->precedenceMap[$name][0];
            if ($childPrecedence > $parentPrecedence
                || ($parentPrecedence == $childPrecedence && $parentAssociativity != $childPosition)
            ) {
                return '(' . $this->{'p' . $name}($node) . ')';
            }
        }

        return $this->{'p' . $name}($node);
    }

    /**
     * Pretty prints an array of nodes and implodes the printed values.
     *
     * @param NodeList|Node[] $nodes Array of Nodes to be printed
     * @param string           $glue  Character to implode with
     *
     * @return string Imploded pretty printed nodes
     */
    protected function pImplode(NodeList $nodes, $glue = '') {
        $pNodes = array();
        foreach ($nodes as $node) {
            $pNodes[] = $this->p($node);
        }

        return implode($glue, $pNodes);
    }

    /**
     * Pretty prints an array of nodes and implodes the printed values with commas.
     *
     * @param NodeList|Node[] $nodes Array of Nodes to be printed
     *
     * @return string Comma separated pretty printed nodes
     */
    protected function pCommaSeparated(NodeList $nodes) {
        return $this->pImplode($nodes, ', ');
    }

    /**
     * Signals the pretty printer that a string shall not be indented.
     *
     * @param string $string Not to be indented string
     *
     * @return mixed String marked with $this->noIndentToken's.
     */
    protected function pNoIndent($string) {
        return str_replace("\n", "\n" . $this->noIndentToken, $string);
    }

    protected function pComments(array $comments) {
        $result = '';

        foreach ($comments as $comment) {
            $result .= $comment . "\n";
        }

        return $result;
    }
    

    // Special nodes

    public function pParam(Node $node) {
        return ($node['type'] ? (is_string($node['type']) ? $node['type'] : $this->p($node['type'])) . ' ' : '')
        . ($node['byRef'] ? '&' : '')
        . '$' . $node['name']
        . ($node['default'] ? ' = ' . $this->p($node['default']) : '');
    }

    public function pArg(Node $node) {
        return ($node['byRef'] ? '&' : '') . $this->p($node['value']);
    }

    public function pConst(Node $node) {
        return $node['name'] . ' = ' . $this->p($node['value']);
    }

    // Names

    public function pName(Node $node) {
        return implode('\\', $node['parts']->toArray());
    }

    public function pName_FullyQualified(Node $node) {
        return '\\' . implode('\\', $node['parts']->toArray());
    }

    public function pName_Relative(Node $node) {
        return 'namespace\\' . implode('\\', $node['parts']->toArray());
    }

    // Magic Constants

    public function pScalar_ClassConst(Node $node) {
        return '__CLASS__';
    }

    public function pScalar_TraitConst(Node $node) {
        return '__TRAIT__';
    }

    public function pScalar_DirConst(Node $node) {
        return '__DIR__';
    }

    public function pScalar_FileConst(Node $node) {
        return '__FILE__';
    }

    public function pScalar_FuncConst(Node $node) {
        return '__FUNCTION__';
    }

    public function pScalar_LineConst(Node $node) {
        return '__LINE__';
    }

    public function pScalar_MethodConst(Node $node) {
        return '__METHOD__';
    }

    public function pScalar_NSConst(Node $node) {
        return '__NAMESPACE__';
    }

    // Scalars

    public function pScalar_String(Node $node) {
        if(strstr($node['value'], "\n\t")){
            return '"' . $this->pNoIndent(str_replace("\n", '\n', addslashes($node['value']))) . '"';
        } else {
            return '\'' . $this->pNoIndent(addslashes($node['value'])) . '\'';
        }
    }

    public function pScalar_Encapsed(Node $node) {
        return '"' . $this->pEncapsList($node['parts'], '"') . '"';
    }

    public function pScalar_LNumber(Node $node) {
        return (string) $node['value'];
    }

    public function pScalar_DNumber(Node $node) {
        $stringValue = (string) $node['value'];

        // ensure that number is really printed as float
        return ctype_digit($stringValue) ? $stringValue . '.0' : $stringValue;
    }

    // Assignments

    public function pExpr_Assign(Node $node) {
        return $this->pInfixOp('Expr_Assign', $node['var'], ' = ', $node['expr']);
    }

    public function pExpr_AssignRef(Node $node) {
        return $this->pInfixOp('Expr_AssignRef', $node['var'], ' =& ', $node['expr']);
    }

    public function pExpr_AssignPlus(Node $node) {
        return $this->pInfixOp('Expr_AssignPlus', $node['var'], ' += ', $node['expr']);
    }

    public function pExpr_AssignMinus(Node $node) {
        return $this->pInfixOp('Expr_AssignMinus', $node['var'], ' -= ', $node['expr']);
    }

    public function pExpr_AssignMul(Node $node) {
        return $this->pInfixOp('Expr_AssignMul', $node['var'], ' *= ', $node['expr']);
    }

    public function pExpr_AssignDiv(Node $node) {
        return $this->pInfixOp('Expr_AssignDiv', $node['var'], ' /= ', $node['expr']);
    }

    public function pExpr_AssignConcat(Node $node) {
        return $this->pInfixOp('Expr_AssignConcat', $node['var'], ' .= ', $node['expr']);
    }

    public function pExpr_AssignMod(Node $node) {
        return $this->pInfixOp('Expr_AssignMod', $node['var'], ' %= ', $node['expr']);
    }

    public function pExpr_AssignBitwiseAnd(Node $node) {
        return $this->pInfixOp('Expr_AssignBitwiseAnd', $node['var'], ' &= ', $node['expr']);
    }

    public function pExpr_AssignBitwiseOr(Node $node) {
        return $this->pInfixOp('Expr_AssignBitwiseOr', $node['var'], ' |= ', $node['expr']);
    }

    public function pExpr_AssignBitwiseXor(Node $node) {
        return $this->pInfixOp('Expr_AssignBitwiseXor', $node['var'], ' ^= ', $node['expr']);
    }

    public function pExpr_AssignShiftLeft(Node $node) {
        return $this->pInfixOp('Expr_AssignShiftLeft', $node['var'], ' <<= ', $node['expr']);
    }

    public function pExpr_AssignShiftRight(Node $node) {
        return $this->pInfixOp('Expr_AssignShiftRight', $node['var'], ' >>= ', $node['expr']);
    }

    // Binary expressions

    public function pExpr_Plus(Node $node) {
        return $this->pInfixOp('Expr_Plus', $node['left'], ' + ', $node['right']);
    }

    public function pExpr_Minus(Node $node) {
        return $this->pInfixOp('Expr_Minus', $node['left'], ' - ', $node['right']);
    }

    public function pExpr_Mul(Node $node) {
        return $this->pInfixOp('Expr_Mul', $node['left'], ' * ', $node['right']);
    }

    public function pExpr_Div(Node $node) {
        return $this->pInfixOp('Expr_Div', $node['left'], ' / ', $node['right']);
    }

    public function pExpr_Concat(Node $node) {
        return $this->pInfixOp('Expr_Concat', $node['left'], ' . ', $node['right']);
    }

    public function pExpr_Mod(Node $node) {
        return $this->pInfixOp('Expr_Mod', $node['left'], ' % ', $node['right']);
    }

    public function pExpr_BooleanAnd(Node $node) {
        return $this->pInfixOp('Expr_BooleanAnd', $node['left'], ' && ', $node['right']);
    }

    public function pExpr_BooleanOr(Node $node) {
        return $this->pInfixOp('Expr_BooleanOr', $node['left'], ' || ', $node['right']);
    }

    public function pExpr_BitwiseAnd(Node $node) {
        return $this->pInfixOp('Expr_BitwiseAnd', $node['left'], ' & ', $node['right']);
    }

    public function pExpr_BitwiseOr(Node $node) {
        return $this->pInfixOp('Expr_BitwiseOr', $node['left'], ' | ', $node['right']);
    }

    public function pExpr_BitwiseXor(Node $node) {
        return $this->pInfixOp('Expr_BitwiseXor', $node['left'], ' ^ ', $node['right']);
    }

    public function pExpr_ShiftLeft(Node $node) {
        return $this->pInfixOp('Expr_ShiftLeft', $node['left'], ' << ', $node['right']);
    }

    public function pExpr_ShiftRight(Node $node) {
        return $this->pInfixOp('Expr_ShiftRight', $node['left'], ' >> ', $node['right']);
    }

    public function pExpr_LogicalAnd(Node $node) {
        return $this->pInfixOp('Expr_LogicalAnd', $node['left'], ' and ', $node['right']);
    }

    public function pExpr_LogicalOr(Node $node) {
        return $this->pInfixOp('Expr_LogicalOr', $node['left'], ' or ', $node['right']);
    }

    public function pExpr_LogicalXor(Node $node) {
        return $this->pInfixOp('Expr_LogicalXor', $node['left'], ' xor ', $node['right']);
    }

    public function pExpr_Equal(Node $node) {
        return $this->pInfixOp('Expr_Equal', $node['left'], ' == ', $node['right']);
    }

    public function pExpr_NotEqual(Node $node) {
        return $this->pInfixOp('Expr_NotEqual', $node['left'], ' != ', $node['right']);
    }

    public function pExpr_Identical(Node $node) {
        return $this->pInfixOp('Expr_Identical', $node['left'], ' === ', $node['right']);
    }

    public function pExpr_NotIdentical(Node $node) {
        return $this->pInfixOp('Expr_NotIdentical', $node['left'], ' !== ', $node['right']);
    }

    public function pExpr_Greater(Node $node) {
        return $this->pInfixOp('Expr_Greater', $node['left'], ' > ', $node['right']);
    }

    public function pExpr_GreaterOrEqual(Node $node) {
        return $this->pInfixOp('Expr_GreaterOrEqual', $node['left'], ' >= ', $node['right']);
    }

    public function pExpr_Smaller(Node $node) {
        return $this->pInfixOp('Expr_Smaller', $node['left'], ' < ', $node['right']);
    }

    public function pExpr_SmallerOrEqual(Node $node) {
        return $this->pInfixOp('Expr_SmallerOrEqual', $node['left'], ' <= ', $node['right']);
    }

    public function pExpr_Instanceof(Node $node) {
        return $this->pInfixOp('Expr_Instanceof', $node['expr'], ' instanceof ', $node['class']);
    }

    // Unary expressions

    public function pExpr_BooleanNot(Node $node) {
        return $this->pPrefixOp('Expr_BooleanNot', '!', $node['expr']);
    }

    public function pExpr_BitwiseNot(Node $node) {
        return $this->pPrefixOp('Expr_BitwiseNot', '~', $node['expr']);
    }

    public function pExpr_UnaryMinus(Node $node) {
        return $this->pPrefixOp('Expr_UnaryMinus', '-', $node['expr']);
    }

    public function pExpr_UnaryPlus(Node $node) {
        return $this->pPrefixOp('Expr_UnaryPlus', '+', $node['expr']);
    }

    public function pExpr_PreInc(Node $node) {
        return $this->pPrefixOp('Expr_PreInc', '++', $node['var']);
    }

    public function pExpr_PreDec(Node $node) {
        return $this->pPrefixOp('Expr_PreDec', '--', $node['var']);
    }

    public function pExpr_PostInc(Node $node) {
        return $this->pPostfixOp('Expr_PostInc', $node['var'], '++');
    }

    public function pExpr_PostDec(Node $node) {
        return $this->pPostfixOp('Expr_PostDec', $node['var'], '--');
    }

    public function pExpr_ErrorSuppress(Node $node) {
        return $this->pPrefixOp('Expr_ErrorSuppress', '@', $node['expr']);
    }

    // Casts

    public function pExpr_Cast_Int(Node $node) {
        return $this->pPrefixOp('Expr_Cast_Int', '(int) ', $node['expr']);
    }

    public function pExpr_Cast_Double(Node $node) {
        return $this->pPrefixOp('Expr_Cast_Double', '(double) ', $node['expr']);
    }

    public function pExpr_Cast_String(Node $node) {
        return $this->pPrefixOp('Expr_Cast_String', '(string) ', $node['expr']);
    }

    public function pExpr_Cast_Array(Node $node) {
        return $this->pPrefixOp('Expr_Cast_Array', '(array) ', $node['expr']);
    }

    public function pExpr_Cast_Object(Node $node) {
        return $this->pPrefixOp('Expr_Cast_Object', '(object) ', $node['expr']);
    }

    public function pExpr_Cast_Bool(Node $node) {
        return $this->pPrefixOp('Expr_Cast_Bool', '(bool) ', $node['expr']);
    }

    public function pExpr_Cast_Unset(Node $node) {
        return $this->pPrefixOp('Expr_Cast_Unset', '(unset) ', $node['expr']);
    }

    // Function calls and similar constructs

    public function pExpr_FuncCall(Node $node) {
        return $this->p($node['name']) . '(' . $this->pCommaSeparated($node['args']) . ')';
    }

    public function pExpr_MethodCall(Node $node) {
        return $this->pVarOrNewExpr($node['var']) . '->' . $this->pObjectProperty($node['name'])
        . '(' . $this->pCommaSeparated($node['args']) . ')';
    }

    public function pExpr_StaticCall(Node $node) {
        return $this->p($node['class']) . '::'
        . ($node['name'] instanceof Node
            ? ($node['name'] instanceof Node
            || $node['name'] instanceof Node
                ? $this->p($node['name'])
                : '{' . $this->p($node['name']) . '}')
            : $node['name'])
        . '(' . $this->pCommaSeparated($node['args']) . ')';
    }

    public function pExpr_Empty(Node $node) {
        return 'empty(' . $this->p($node['expr']) . ')';
    }

    public function pExpr_Isset(Node $node) {
        return 'isset(' . $this->pCommaSeparated($node['vars']) . ')';
    }

    public function pExpr_Print(Node $node) {
        return 'print ' . $this->p($node['expr']);
    }

    public function pExpr_Eval(Node $node) {
        return 'eval(' . $this->p($node['expr']) . ')';
    }

    public function pExpr_Include(Node $node) {
        static $map = array(
            Node::TYPE_INCLUDE      => 'include',
            Node::TYPE_INCLUDE_ONCE => 'include_once',
            Node::TYPE_REQUIRE      => 'require',
            Node::TYPE_REQUIRE_ONCE => 'require_once',
        );

        return $map[$node->getName()] . ' ' . $this->p($node['expr']);
    }

    public function pExpr_List(Node $node) {
        $pList = array();
        foreach ($node['vars'] as $var) {
            if (null === $var) {
                $pList[] = '';
            } else {
                $pList[] = $this->p($var);
            }
        }

        return 'list(' . implode(', ', $pList) . ')';
    }

    // Other

    public function pExpr_Variable(Node $node) {
        if ($node['name'] instanceof Node) {
            return '${' . $this->p($node['name']) . '}';
        } else {
            return '$' . $node['name'];
        }
    }

    public function pExpr_Array(Node $node) {
        return 'array(' . $this->pCommaSeparated($node['items']) . ')';
    }

    public function pExpr_ArrayItem(Node $node) {
        return (null !== $node['key'] ? $this->p($node['key']) . ' => ' : '')
        . ($node['byRef'] ? '&' : '') . $this->p($node['value']);
    }

    public function pExpr_ArrayDimFetch(Node $node) {
        return $this->pVarOrNewExpr($node['var'])
        . '[' . (null !== $node['dim'] ? $this->p($node['dim']) : '') . ']';
    }

    public function pExpr_ConstFetch(Node $node) {
        return $this->p($node['name']);
    }

    public function pExpr_ClassConstFetch(Node $node) {
        return $this->p($node['class']) . '::' . $node['name'];
    }

    public function pExpr_PropertyFetch(Node $node) {
        return $this->pVarOrNewExpr($node['var']) . '->' . $this->pObjectProperty($node['name']);
    }

    public function pExpr_StaticPropertyFetch(Node $node) {
        return $this->p($node['class']) . '::$' . $this->pObjectProperty($node['name']);
    }

    public function pExpr_ShellExec(Node $node) {
        return '`' . $this->pEncapsList($node['parts'], '`') . '`';
    }

    public function pExpr_Closure(Node $node) {
        return ($node['static'] ? 'static ' : '')
        . 'function ' . ($node['byRef'] ? '&' : '')
        . '(' . $this->pCommaSeparated($node['params']) . ')'
        . (!empty($node['uses']) ? ' use(' . $this->pCommaSeparated($node['uses']) . ')': '')
        . ' {' . "\n" . $this->pStmts($node['stmts']) . "\n" . '}';
    }

    public function pExpr_ClosureUse(Node $node) {
        return ($node['byRef'] ? '&' : '') . '$' . $node['var'];
    }

    public function pExpr_New(Node $node) {
        return 'new ' . $this->p($node['class']) . '(' . $this->pCommaSeparated($node['args']) . ')';
    }

    public function pExpr_Clone(Node $node) {
        return 'clone ' . $this->p($node['expr']);
    }

    public function pExpr_Ternary(Node $node) {
        // a bit of cheating: we treat the ternary as a binary op where the ?...: part is the operator.
        // this is okay because the part between ? and : never needs parentheses.
        return $this->pInfixOp('Expr_Ternary',
            $node['cond'], ' ?' . (null !== $node['if'] ? ' ' . $this->p($node['if']) . ' ' : '') . ': ', $node['else']
        );
    }

    public function pExpr_Exit(Node $node) {
        return 'die' . (null !== $node['expr'] ? '(' . $this->p($node['expr']) . ')' : '');
    }

    public function pExpr_Yield(Node $node) {
        if ($node['value'] === null) {
            return 'yield';
        } else {
            // this is a bit ugly, but currently there is no way to detect whether the parentheses are necessary
            return '(yield '
            . ($node['key'] !== null ? $this->p($node['key']) . ' => ' : '')
            . $this->p($node['value'])
            . ')';
        }
    }

    // Declarations

    public function pStmt_Namespace(Node $node) {
        if ($this->canUseSemicolonNamespaces) {
            return 'namespace ' . $this->p($node['name']) . ';' . "\n\n" . $this->pStmts($node['stmts'], false);
        } else {
            return 'namespace' . (null !== $node['name'] ? ' ' . $this->p($node['name']) : '')
            . ' {' . "\n" . $this->pStmts($node['stmts']) . "\n" . '}';
        }
    }

    public function pStmt_Use(Node $node) {
        return 'use ' . $this->pCommaSeparated($node['uses']) . ';';
    }

    public function pStmt_UseUse(Node $node) {
        $nameParts = $node['name'];
        return $this->p($node['name'])
        . (end($nameParts) !== $node['alias'] ? ' as ' . $node['alias'] : '');
    }

    public function pStmt_Interface(Node $node) {
        return 'interface ' . $node['name']
        . (!empty($node['extends']) ? ' extends ' . $this->pCommaSeparated($node['extends']) : '')
        . "\n" . '{' . "\n" . $this->pStmts($node['stmts']) . "\n" . '}';
    }

    public function pStmt_Class(Node $node) {
        return $this->pModifiers($node->getName())
        . 'class ' . $node['name']
        . (null !== $node['extends'] ? ' extends ' . $this->p($node['extends']) : '')
        . (!empty($node['implements']) ? ' implements ' . $this->pCommaSeparated($node['implements']) : '')
        . "\n" . '{' . "\n" . $this->pStmts($node['stmts']) . "\n" . '}';
    }

    public function pStmt_Trait(Node $node) {
        return 'trait ' . $node['name']
        . "\n" . '{' . "\n" . $this->pStmts($node['stmts']) . "\n" . '}';
    }

    public function pStmt_TraitUse(Node $node) {
        return 'use ' . $this->pCommaSeparated($node['traits'])
        . (empty($node['adaptations'])
            ? ';'
            : ' {' . "\n" . $this->pStmts($node['adaptations']) . "\n" . '}');
    }

    public function pStmt_TraitUseAdaptation_Precedence(Node $node) {
        return $this->p($node['trait']) . '::' . $node['method']
        . ' insteadof ' . $this->pCommaSeparated($node['insteadof']) . ';';
    }

    public function pStmt_TraitUseAdaptation_Alias(Node $node) {
        return (null !== $node['trait'] ? $this->p($node['trait']) . '::' : '')
        . $node['method'] . ' as'
        . (null !== $node['newModifier'] ? ' ' . $this->pModifiers($node['newModifier']) : '')
        . (null !== $node['newName']     ? ' ' . $node['newName']                        : '')
        . ';';
    }

    public function pStmt_Property(Node $node) {
        return $this->pModifiers($node->getName()) . $this->pCommaSeparated($node['props']) . ';';
    }

    public function pStmt_PropertyProperty(Node $node) {
        return '$' . $node['name']
        . (null !== $node['default'] ? ' = ' . $this->p($node['default']) : '');
    }

    public function pStmt_ClassMethod(Node $node) {
        return $this->pModifiers($node['type'])
        . 'function ' . ($node['byRef'] ? '&' : '') . $node['name']
        . '(' . $this->pCommaSeparated($node['params']) . ')'
        . (null !== $node['stmts']
            ? "\n" . '{' . "\n" . $this->pStmts($node['stmts']) . "\n" . '}'
            : ';')
        . "\n";
    }

    public function pStmt_ClassConst(Node $node) {
        return 'const ' . $this->pCommaSeparated($node['consts']) . ';';
    }

    public function pStmt_Function(Node $node) {
        return 'function ' . ($node['byRef'] ? '&' : '') . $node['name']
        . '(' . $this->pCommaSeparated($node['params']) . ')'
        . "\n" . '{' . "\n" . $this->pStmts($node['stmts']) . "\n" . '}';
    }

    public function pStmt_Const(Node $node) {
        return 'const ' . $this->pCommaSeparated($node['consts']) . ';';
    }

    public function pStmt_Declare(Node $node) {
        return 'declare (' . $this->pCommaSeparated($node['declares']) . ') {'
        . "\n" . $this->pStmts($node['stmts']) . "\n" . '}';
    }

    public function pStmt_DeclareDeclare(Node $node) {
        return $node['key'] . ' = ' . $this->p($node['value']);
    }

    // Control flow

    public function pStmt_If(Node $node) {
        return 'if (' . $this->p($node['cond']) . ') {'
        . "\n" . $this->pStmts($node['stmts']) . "\n" . '}'
        . $this->pImplode($node['elseifs'])
        . (null !== $node['else'] ? $this->p($node['else']) : '');
    }

    public function pStmt_Elseif(Node $node) {
        return ' elseif (' . $this->p($node['cond']) . ') {'
        . "\n" . $this->pStmts($node['stmts']) . "\n" . '}';
    }

    public function pStmt_Else(Node $node) {
        return ' else {' . "\n" . $this->pStmts($node['stmts']) . "\n" . '}';
    }

    public function pStmt_For(Node $node) {
        return 'for ('
        . $this->pCommaSeparated($node['init']) . ';' . (!empty($node['cond']) ? ' ' : '')
        . $this->pCommaSeparated($node['cond']) . ';' . (!empty($node['loop']) ? ' ' : '')
        . $this->pCommaSeparated($node['loop'])
        . ') {' . "\n" . $this->pStmts($node['stmts']) . "\n" . '}';
    }

    public function pStmt_Foreach(Node $node) {
        return 'foreach (' . $this->p($node['expr']) . ' as '
        . (null !== $node['keyVar'] ? $this->p($node['keyVar']) . ' => ' : '')
        . ($node['byRef'] ? '&' : '') . $this->p($node['valueVar']) . ') {'
        . "\n" . $this->pStmts($node['stmts']) . "\n" . '}';
    }

    public function pStmt_While(Node $node) {
        return 'while (' . $this->p($node['cond']) . ') {'
        . "\n" . $this->pStmts($node['stmts']) . "\n" . '}';
    }

    public function pStmt_Do(Node $node) {
        return 'do {' . "\n" . $this->pStmts($node['stmts']) . "\n"
        . '} while (' . $this->p($node['cond']) . ');';
    }

    public function pStmt_Switch(Node $node) {
        return 'switch (' . $this->p($node['cond']) . ') {'
        . "\n" . $this->pStmts($node['cases']) . "\n" . '}';
    }

    public function pStmt_Case(Node $node) {
        return (null !== $node['cond'] ? 'case ' . $this->p($node['cond']) : 'default') . ':'
        . ($node['stmts'] ? "\n" . $this->pStmts($node['stmts']) : '');
    }

    public function pStmt_TryCatch(Node $node) {
        return 'try {' . "\n" . $this->pStmts($node['stmts']) . "\n" . '}'
        . $this->pImplode($node['catches'])
        . ($node['finallyStmts'] !== null
            ? ' finally {' . "\n" . $this->pStmts($node['finallyStmts']) . "\n" . '}'
            : '');
    }

    public function pStmt_Catch(Node $node) {
        return ' catch (' . $this->p($node->getName()) . ' $' . $node['var'] . ') {'
        . "\n" . $this->pStmts($node['stmts']) . "\n" . '}';
    }

    public function pStmt_Break(Node $node) {
        return 'break' . ($node['num'] !== null ? ' ' . $this->p($node['num']) : '') . ';';
    }

    public function pStmt_Continue(Node $node) {
        return 'continue' . ($node['num'] !== null ? ' ' . $this->p($node['num']) : '') . ';';
    }

    public function pStmt_Return(Node $node) {
        return 'return' . (null !== $node['expr'] ? ' ' . $this->p($node['expr']) : '') . ';';
    }

    public function pStmt_Throw(Node $node) {
        return 'throw ' . $this->p($node['expr']) . ';';
    }

    public function pStmt_Label(Node $node) {
        return $node['name'] . ':';
    }

    public function pStmt_Goto(Node $node) {
        return 'goto ' . $node['name'] . ';';
    }

    // Other

    public function pStmt_Echo(Node $node) {
        return 'echo ' . $this->pCommaSeparated($node['exprs']) . ';';
    }

    public function pStmt_Static(Node $node) {
        return 'static ' . $this->pCommaSeparated($node['vars']) . ';';
    }

    public function pStmt_Global(Node $node) {
        return 'global ' . $this->pCommaSeparated($node['vars']) . ';';
    }

    public function pStmt_StaticVar(Node $node) {
        return '$' . $node['name']
        . (null !== $node['default'] ? ' = ' . $this->p($node['default']) : '');
    }

    public function pStmt_Unset(Node $node) {
        return 'unset(' . $this->pCommaSeparated($node['vars']) . ');';
    }

    public function pStmt_InlineHTML(Node $node) {
        return '?>' . $this->pNoIndent("\n" . $node['value']) . '<?php ';
    }

    public function pStmt_HaltCompiler(Node $node) {
        return '__halt_compiler();' . $node['remaining'];
    }

    // Helpers

    public function pObjectProperty($node) {
        if ($node instanceof Node) {
            return '{' . $this->p($node) . '}';
        } else {
            return $node;
        }
    }

    public function pModifiers($modifiers) {
        return ($modifiers & Node::MODIFIER_PUBLIC    ? 'public '    : '')
        . ($modifiers & Node::MODIFIER_PROTECTED ? 'protected ' : '')
        . ($modifiers & Node::MODIFIER_PRIVATE   ? 'private '   : '')
        . ($modifiers & Node::MODIFIER_STATIC    ? 'static '    : '')
        . ($modifiers & Node::MODIFIER_ABSTRACT  ? 'abstract '  : '')
        . ($modifiers & Node::MODIFIER_FINAL     ? 'final '     : '');
    }

    public function pEncapsList(array $encapsList, $quote) {
        $return = '';
        foreach ($encapsList as $element) {
            if (is_string($element)) {
                $return .= addcslashes($element, "\n\r\t\f\v$" . $quote . "\\");
            } else {
                $return .= '{' . $this->p($element) . '}';
            }
        }

        return $return;
    }

    public function pVarOrNewExpr(Node $node) {
        if (!$node instanceof Node) {
            return '(' . $this->p($node) . ')';
        } else {
            return $this->p($node);
        }
    }
}