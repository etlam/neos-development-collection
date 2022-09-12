<?php

/*
 * This file is part of the Neos.ContentRepository.BehavioralTests package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

declare(strict_types=1);

namespace Neos\ContentRepository\BehavioralTests\Command;

use Neos\ContentGraph\DoctrineDbalAdapter\Domain\Projection\GraphProjector;
use Neos\ContentGraph\PostgreSQLAdapter\Domain\Projection\HypergraphProjection;
use Neos\ContentRepository\Core\DimensionSpace\DimensionSpacePoint;
use Neos\ContentRepository\Core\DimensionSpace\DimensionSpacePointSet;
use Neos\ContentRepository\Core\SharedModel\Workspace\ContentStreamId;
use Neos\ContentRepository\Core\SharedModel\Node\NodeAggregateId;
use Neos\ContentRepository\Core\NodeType\NodeTypeName;
use Neos\ContentRepository\Core\Feature\ContentStreamForking\Event\ContentStreamWasForked;
use Neos\ContentRepository\Core\Feature\NodeCreation\Event\NodeAggregateWithNodeWasCreated;
use Neos\ContentRepository\Core\Feature\RootNodeCreation\Event\RootNodeAggregateWithNodeWasCreated;
use Neos\ContentRepository\Core\SharedModel\Node\NodeAggregateClassification;
use Neos\ContentRepository\Core\DimensionSpace\OriginDimensionSpacePoint;
use Neos\ContentRepository\Core\Feature\NodeModification\Dto\SerializedPropertyValues;
use Neos\ContentRepository\Core\SharedModel\User\UserId;
use Neos\Flow\Cli\CommandController;

final class ContentStreamCommandController extends CommandController
{
//    private GraphProjector $graphProjector;
//
//    private HypergraphProjector $hypergraphProjector;
//
//    private contentStreamId $contentStreamId;
//
//    private DimensionSpacePointSet $dimensionSpacePoints;
//
//    public function __construct(GraphProjector $graphProjector, HypergraphProjector $hypergraphProjector)
//    {
//        $this->graphProjector = $graphProjector;
//        $this->hypergraphProjector = $hypergraphProjector;
//        $this->contentStreamId = contentStreamId::fromString('cs-identifier');
//        $this->dimensionSpacePoints = new DimensionSpacePointSet([
//            DimensionSpacePoint::fromArray(['language' => 'mul']),
//            DimensionSpacePoint::fromArray(['language' => 'de']),
//            DimensionSpacePoint::fromArray(['language' => 'gsw']),
//            DimensionSpacePoint::fromArray(['language' => 'en']),
//            DimensionSpacePoint::fromArray(['language' => 'fr'])
//        ]);
//        parent::__construct();
//    }
//
//    /**
//     * @throws \Throwable
//     */
//    public function preparePerformanceTestCommand(int $nodesPerLevel, int $levels): void
//    {
//        $this->graphProjector->reset();
//        $this->hypergraphProjector->reset();
//        $rootnodeAggregateId = nodeAggregateId::fromString('lady-eleonode-rootford');
//        $rootNodeAggregateWasCreated = new RootNodeAggregateWithNodeWasCreated(
//            $this->contentStreamId,
//            $rootnodeAggregateId,
//            NodeTypeName::fromString('Neos.ContentRepository:Root'),
//            $this->dimensionSpacePoints,
//            NodeAggregateClassification::CLASSIFICATION_ROOT,
//            UserIdentifier::forSystemUser()
//        );
//        $this->graphProjector->whenRootNodeAggregateWithNodeWasCreated($rootNodeAggregateWasCreated);
//        $this->hypergraphProjector->whenRootNodeAggregateWithNodeWasCreated($rootNodeAggregateWasCreated);
//        #$time = microtime(true);
//        $this->createHierarchy($rootnodeAggregateId, 1, $levels, $nodesPerLevel);
//        #$this->outputLine(microtime(true) - $time . ' elapsed');
//    }
//
//    /**
//     * @throws \Throwable
//     */
//    private function createHierarchy(
//        nodeAggregateId $parentNodeAggregateId,
//        int $currentLevel,
//        int $maximumLevel,
//        int $numberOfNodes
//    ): void {
//        if ($currentLevel <= $maximumLevel) {
//            for ($i = 0; $i < $numberOfNodes; $i++) {
//                $nodeAggregateId = nodeAggregateId::create();
//                $nodeAggregateWasCreated = new NodeAggregateWithNodeWasCreated(
//                    $this->contentStreamId,
//                    $nodeAggregateId,
//                    NodeTypeName::fromString('Neos.ContentRepository:Testing'),
//                    OriginDimensionSpacePoint::fromArray(['language' => 'mul']),
//                    $this->dimensionSpacePoints,
//                    $parentNodeAggregateId,
//                    null,
//                    SerializedPropertyValues::fromArray([]),
//                    NodeAggregateClassification::CLASSIFICATION_REGULAR,
//                    UserIdentifier::forSystemUser()
//                );
//                $this->graphProjector->whenNodeAggregateWithNodeWasCreated($nodeAggregateWasCreated);
//                $this->hypergraphProjector->whenNodeAggregateWithNodeWasCreated($nodeAggregateWasCreated);
//                $this->createHierarchy($nodeAggregateId, $currentLevel + 1, $maximumLevel, $numberOfNodes);
//            }
//        }
//    }
//
//    /**
//     * @throws \Throwable
//     */
//    public function testPerformanceCommand(string $projectorName): void
//    {
//        $contentStreamWasForked = new ContentStreamWasForked(
//            contentStreamId::create(),
//            $this->contentStreamId,
//            1,
//            UserIdentifier::forSystemUser()
//        );
//        $time = microtime(true);
//        if ($projectorName === 'graph') {
//            $this->graphProjector->whenContentStreamWasForked($contentStreamWasForked);
//        } elseif ($projectorName === 'hypergraph') {
//            $this->hypergraphProjector->whenContentStreamWasForked($contentStreamWasForked);
//        }
//        $timeElapsed = microtime(true) - $time;
//        $this->outputLine($projectorName . ': ' . $timeElapsed);
//    }
}