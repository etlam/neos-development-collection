<?php
declare(strict_types=1);
namespace Neos\EventSourcedNeosAdjustments\Fluid\ViewHelpers\ContentElement;

/*
 * This file is part of the Neos.Neos package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\ContentRepository\Intermediary\Domain\NodeBasedReadModelInterface;
use Neos\EventSourcedNeosAdjustments\ContentElementWrapping\ContentElementWrappingService;
use Neos\Flow\Annotations as Flow;
use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;
use Neos\FluidAdaptor\Core\ViewHelper\Exception as ViewHelperException;
use Neos\Fusion\FusionObjects\Helpers\FusionAwareViewInterface;

/**
 * A view helper for manually wrapping content editables.
 *
 * Note that using this view helper is usually not necessary as Neos will automatically wrap editables of content
 * elements.
 *
 * By explicitly wrapping template parts with node meta data that is required for the backend to show properties in the
 * inspector, this ViewHelper enables usage of the ``contentElement.editable`` ViewHelper outside of content element
 * templates. This is useful if you want to make properties of a custom document node inline-editable.
 *
 * = Examples =
 *
 * <code title="Defaults">
 * <neos:contentElement.wrap>
 *  <div>{neos:contentElement.editable(property: 'someProperty')}</div>
 * </neos:contentElement.wrap>
 * </code>
 */
class WrapViewHelper extends AbstractViewHelper
{
    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @Flow\Inject
     * @var ContentElementWrappingService
     */
    protected $contentElementWrappingService;

    /**
     * In live workspace this just renders a the content.
     * For logged in users with access to the Backend this also adds the attributes for the RTE to work.
     *
     * @param NodeBasedReadModelInterface $node The node of the content element. Optional, will be resolved from the Fusion context by default.
     * @return string The rendered property with a wrapping tag. In the user workspace this adds some required attributes for the RTE to work
     * @throws ViewHelperException
     * @throws \Neos\EventSourcedContentRepository\Domain\Context\NodeAddress\Exception\NodeAddressCannotBeSerializedException
     */
    public function render(NodeBasedReadModelInterface $node = null)
    {
        $view = $this->viewHelperVariableContainer->getView();
        if (!$view instanceof FusionAwareViewInterface) {
            throw new ViewHelperException('This ViewHelper can only be used in a Fusion content element. You have to specify the "node" argument if it cannot be resolved from the Fusion context.', 1385737102);
        }
        $fusionObject = $view->getFusionObject();
        $currentContext = $fusionObject->getRuntime()->getCurrentContext();

        if ($node === null) {
            $node = $currentContext['node'];
        }
        return $this->contentElementWrappingService->wrapContentObject($node, $this->renderChildren(), $fusionObject->getPath());
    }
}
