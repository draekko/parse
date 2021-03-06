<?php

namespace Psecio\Parse\Rule;

use Psecio\Parse\RuleInterface;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;

/**
 * Remove any use of ereg functions, deprecated as of PHP 5.3.0. Use preg_*
 *
 * Long description missing...
 *
 * @todo Add long description to docblock
*/
class EregFunctions implements RuleInterface
{
    use Helper\NameTrait, Helper\DocblockDescriptionTrait;

    private $functions = ['ereg', 'eregi', 'ereg_replace', 'eregi_replace'];

    public function isValid(Node $node)
    {
        $nodeName = (is_object($node->name)) ? $node->name->parts[0] : $node->name;

        if ($node instanceof FuncCall && in_array(strtolower($nodeName), $this->functions)) {
            return false;
        }

        return true;
    }
}
