<?php
declare(strict_types=1);
namespace Neos\EventSourcedNeosAdjustments\Fluid\ViewHelpers\Uri;

/*
 * This file is part of the Neos.Neos package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Domain\ContentSubgraph\NodePath;
use Neos\ContentRepository\Domain\NodeAggregate\NodeAggregateIdentifier;
use Neos\ContentRepository\Intermediary\Domain\NodeBasedReadModelInterface;
use Neos\EventSourcedContentRepository\Domain\Context\NodeAddress\Exception\NodeAddressCannotBeSerializedException;
use Neos\EventSourcedContentRepository\Domain\Context\NodeAddress\NodeAddress;
use Neos\EventSourcedContentRepository\Domain\Context\NodeAddress\NodeAddressFactory;
use Neos\EventSourcedContentRepository\Domain\Context\Parameters\VisibilityConstraints;
use Neos\EventSourcedContentRepository\Domain\Projection\Content\ContentGraphInterface;
use Neos\EventSourcedContentRepository\Domain\Projection\Content\ContentSubgraphInterface;
use Neos\EventSourcedNeosAdjustments\Domain\Context\Content\NodeSiteResolvingService;
use Neos\EventSourcedNeosAdjustments\EventSourcedRouting\NodeUriBuilder;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Exception as HttpException;
use Neos\Flow\Mvc\Exception\NoMatchingRouteException;
use Neos\Flow\Mvc\Routing\Exception\MissingActionNameException;
use Neos\Flow\Mvc\Routing\UriBuilder;
use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;
use Neos\FluidAdaptor\Core\ViewHelper\Exception as ViewHelperException;
use Neos\Fusion\ViewHelpers\FusionContextTrait;

/**
 * A view helper for creating URIs pointing to nodes.
 *
 * The target node can be provided as string or as a Node object; if not specified
 * at all, the generated URI will refer to the current document node inside the Fusion context.
 *
 * When specifying the ``node`` argument as string, the following conventions apply:
 *
 * *``node`` starts with ``/``:*
 * The given path is an absolute node path and is treated as such.
 * Example: ``/sites/acmecom/home/about/us``
 *
 * *``node`` does not start with ``/``:*
 * The given path is treated as a path relative to the current node.
 * Examples: given that the current node is ``/sites/acmecom/products/``,
 * ``stapler`` results in ``/sites/acmecom/products/stapler``,
 * ``../about`` results in ``/sites/acmecom/about/``,
 * ``./neos/info`` results in ``/sites/acmecom/products/neos/info``.
 *
 * *``node`` starts with a tilde character (``~``):*
 * The given path is treated as a path relative to the current site node.
 * Example: given that the current node is ``/sites/acmecom/products/``,
 * ``~/about/us`` results in ``/sites/acmecom/about/us``,
 * ``~`` results in ``/sites/acmecom``.
 *
 * = Examples =
 *
 * <code title="Default">
 * <neos:uri.node />
 * </code>
 * <output>
 * homepage/about.html
 * (depending on current workspace, current node, format etc.)
 * </output>
 *
 * <code title="Generating an absolute URI">
 * <neos:uri.node absolute="{true"} />
 * </code>
 * <output>
 * http://www.example.org/homepage/about.html
 * (depending on current workspace, current node, format, host etc.)
 * </output>
 *
 * <code title="Target node given as absolute node path">
 * <neos:uri.node node="/sites/acmecom/about/us" />
 * </code>
 * <output>
 * about/us.html
 * (depending on current workspace, current node, format etc.)
 * </output>
 *
 * <code title="Target node given as relative node path">
 * <neos:uri.node node="~/about/us" />
 * </code>
 * <output>
 * about/us.html
 * (depending on current workspace, current node, format etc.)
 * </output>
 *
 * <code title="Target node given as node://-uri">
 * <neos:uri.node node="node://30e893c1-caef-0ca5-b53d-e5699bb8e506" />
 * </code>
 * <output>
 * about/us.html
 * (depending on current workspace, current node, format etc.)
 * </output>
 * @api
 */
class NodeViewHelper extends AbstractViewHelper
{
    use FusionContextTrait;

    /**
     * @Flow\Inject
     * @var NodeAddressFactory
     */
    protected $nodeAddressFactory;

    /**
     * @Flow\Inject
     * @var NodeSiteResolvingService
     */
    protected $nodeSiteResolvingService;

    /**
     * @Flow\Inject
     * @var ContentGraphInterface
     */
    protected $contentGraph;

    /**
     * Initialize arguments
     *
     * @return void
     * @throws ViewHelperException
     */
    public function initializeArguments()
    {
        $this->registerArgument('subgraph', 'mixed', 'The subgraph');

        $this->registerArgument('node', 'mixed', 'A node object, a string node path (absolute or relative), a string node://-uri or NULL');
        $this->registerArgument('format', 'string', 'Format to use for the URL, for example "html" or "json"');
        $this->registerArgument('absolute', 'boolean', 'If set, an absolute URI is rendered', false, false);
        $this->registerArgument('arguments', 'array', 'Additional arguments to be passed to the UriBuilder (for example pagination parameters)', false, []);
        $this->registerArgument('section', 'string', 'The anchor to be added to the URI', false, '');
        $this->registerArgument('addQueryString', 'boolean', 'If set, the current query parameters will be kept in the URI', false, false);
        $this->registerArgument('argumentsToBeExcludedFromQueryString', 'array', 'arguments to be removed from the URI. Only active if $addQueryString = true', false, []);
        $this->registerArgument('baseNodeName', 'string', 'The name of the base node inside the Fusion context to use for the ContentContext or resolving relative paths', false, 'documentNode');
        $this->registerArgument('nodeVariableName', 'string', 'The variable the node will be assigned to for the rendered child content', false, 'linkedNode');
        $this->registerArgument('resolveShortcuts', 'boolean', 'INTERNAL Parameter - if false, shortcuts are not redirected to their target. Only needed on rare backend occasions when we want to link to the shortcut itself', false, true);
    }

    /**
     * Renders the URI.
     *
     */
    public function render()
    {
        $node = $this->arguments['node'];
        $nodeAddress = null;

        if ($node instanceof NodeBasedReadModelInterface) {
            $nodeAddress = $node->getAddress();
        } elseif (is_string($node)) {
            $nodeAddress = $this->resolveNodeAddressFromString($node);
        } else {
            throw new ViewHelperException(sprintf('The "node" argument can only be a string or an instance of %s. Given: %s', NodeBasedReadModelInterface::class, is_object($node) ? get_class($node) : gettype($node)), 1601372376);
        }

        $uriBuilder = new UriBuilder();
        $uriBuilder->setRequest($this->controllerContext->getRequest());
        $uriBuilder->setFormat($this->arguments['format'])
            ->setCreateAbsoluteUri($this->arguments['absolute'])
            ->setArguments($this->arguments['arguments'])
            ->setSection($this->arguments['section'])
            ->setAddQueryString($this->arguments['addQueryString'])
            ->setArgumentsToBeExcludedFromQueryString($this->arguments['argumentsToBeExcludedFromQueryString']);

        try {
            $uri = (string)NodeUriBuilder::fromUriBuilder($uriBuilder)->uriFor($nodeAddress);
        } catch (NodeAddressCannotBeSerializedException | HttpException | NoMatchingRouteException | MissingActionNameException $e) {
            throw new ViewHelperException(sprintf('Failed to build URI for node: %s: %s', $nodeAddress, $e->getMessage()), 1601372594, $e);
        }
        return $uri;
    }

    /**
     * Converts strings like "relative/path", "/absolute/path", "~/site-relative/path" and "~" to the corresponding NodeAddress
     *
     * @param string $path
     * @return NodeAddress
     * @throws ViewHelperException
     */
    private function resolveNodeAddressFromString(string $path): NodeAddress
    {
        /* @var NodeBasedReadModelInterface $documentNode */
        $documentNode = $this->getContextVariable('documentNode');
        $documentNodeAddress = $documentNode->getAddress();
        if (strncmp($path, 'node://', 7) === 0) {
            return $this->nodeAddressFactory->adjustWithNodeAggregateIdentifier($documentNodeAddress, NodeAggregateIdentifier::fromString(\mb_substr($path, 7)));
        }
        $subgraph = $this->getContentSubgraphForNodeAddress($documentNodeAddress);
        if (strncmp($path, '~', 1) === 0) {
            // TODO: This can be simplified once https://github.com/neos/contentrepository-development-collection/issues/164 is resolved
            $siteNode = $this->nodeSiteResolvingService->findSiteNodeForNodeAddress($documentNodeAddress);
            if ($siteNode === null) {
                throw new ViewHelperException(sprintf('Failed to determine site node for aggregate node "%s" and subgraph "%s"', $documentNodeAddress->getNodeAggregateIdentifier(), json_encode($subgraph, JSON_PARTIAL_OUTPUT_ON_ERROR)), 1601366598);
            }
            if ($path === '~') {
                $targetNode = $siteNode;
            } else {
                $targetNode = $subgraph->findNodeByPath(NodePath::fromString(substr($path, 1)), $siteNode->getNodeAggregateIdentifier());
            }
        } else {
            $targetNode = $subgraph->findNodeByPath(NodePath::fromString($path), $documentNodeAddress->getNodeAggregateIdentifier());
        }
        if ($targetNode === null) {
            throw new ViewHelperException(sprintf('Node on path "%s" could not be found for aggregate node "%s" and subgraph "%s"', $path, $documentNodeAddress->getNodeAggregateIdentifier(), json_encode($subgraph, JSON_PARTIAL_OUTPUT_ON_ERROR)), 1601311789);
        }
        return $documentNodeAddress->withNodeAggregateIdentifier($targetNode->getNodeAggregateIdentifier());
    }

    /**
     * Returns the ContentSubgraph that is specified via "subgraph" argument, if present. Otherwise the Subgraph of the given $nodeAddress
     *
     * @param NodeAddress $nodeAddress
     * @return ContentSubgraphInterface
     */
    private function getContentSubgraphForNodeAddress(NodeAddress $nodeAddress): ContentSubgraphInterface
    {
        if ($this->arguments['subgraph']) {
            return $this->arguments['subgraph'];
        }
        return $this->contentGraph->getSubgraphByIdentifier($nodeAddress->getContentStreamIdentifier(), $nodeAddress->getDimensionSpacePoint(), VisibilityConstraints::withoutRestrictions());
    }
}
