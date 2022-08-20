<?php
namespace Neos\ContentRepository\Security\Authorization\Privilege\Node;

/*
 * This file is part of the Neos.ContentRepository package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Projection\ContentGraph\Node;

/**
 * A privilege to restrict reading of node properties.
 *
 * This is needed, as the technical implementation is not based on the entity privilege type, that
 * the read node privilege (retrieving the node at all) ist based on.
 */
class ReadNodePropertyPrivilege extends AbstractNodePropertyPrivilege
{
    /**
     * @var array<string,string>
     */
    protected array $methodNameToPropertyMapping = [
        'getName' => 'name',
        'isHidden' => 'hidden',
        'isHiddenInIndex' => 'hiddenInIndex',
        'getHiddenBeforeDateTime' => 'hiddenBeforeDateTime',
        'getHiddenAfterDateTime' => 'hiddenAfterDateTime',
        'getAccessRoles' => 'accessRoles',
    ];

    protected function buildMethodPrivilegeMatcher(): string
    {
        return  'within(' . Node::class . ') && method(.*->(getProperty|getProperties)())';
    }
}