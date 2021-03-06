<?php

namespace Psecio\Parse\Rule;

use Psecio\Parse\RuleInterface;
use PhpParser\Node;

/**
 * By default 'extract' overwrites variables in the local scope
 *
 * For the extract function, if either:
 *  - the second param is not set (overwrite by default)
 *  = the second param is set but is EXTR_OVERWRITE
 * fail...
 *
 * @todo Add long description to docblock
 */
class Extract implements RuleInterface
{
    use Helper\NameTrait, Helper\DocblockDescriptionTrait, Helper\IsFunctionTrait;

    public function isValid(Node $node)
    {
        if ($this->isFunction($node, 'extract') === true) {
            // Check to be sure it has two arguments
            if (count($node->args) < 2) {
                return false;
            }

            $name = (!isset($node->args[1]->value->name->parts[0]))
                ? $node->args[1]->value->name : $node->args[1]->value->name->parts[0];

            // So we have two parameters...see if #2 is not equal to EXTR_OVERWRITE
            if ($name === 'EXTR_OVERWRITE') {
                return false;
            }
        }
        return true;
    }
}
