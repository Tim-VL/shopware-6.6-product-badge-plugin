<?php declare(strict_types=1);

namespace Swag\ProductBadges\Controller\Api;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Log\Package;
use Swag\ProductBadges\Service\ProductBadgeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Package('core')]
#[Route(defaults: ['_routeScope' => ['api']])]
class ProductBadgeAssignmentController extends AbstractController
{
    public function __construct(
        private readonly EntityRepository $productBadgeProductRepository,
        private readonly EntityRepository $productRepository,
        private readonly ProductBadgeService $productBadgeService
    ) {
    }

    #[Route(path: '/api/product-badge/assign', name: 'api.product_badge.assign', methods: ['POST'])]
    public function assignBadgeToProduct(Request $request, Context $context): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $requiredFields = ['productId', 'productVersionId', 'swagProductBadgeId'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return new JsonResponse([
                    'success' => false,
                    'message' => "Missing required field: {$field}"
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        try {
            $assignmentData = [
                'productId' => $data['productId'],
                'productVersionId' => $data['productVersionId'],
                'swagProductBadgeId' => $data['swagProductBadgeId'],
                'mediaId' => $data['mediaId'] ?? null,
            ];

            $result = $this->productBadgeProductRepository->create([$assignmentData], $context);
            $id = $result->getPrimaryKeys('swag_product_badge_product')[0];

            return new JsonResponse([
                'success' => true,
                'data' => ['id' => $id]
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Failed to assign badge to product: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route(path: '/api/product-badge/unassign/{id}', name: 'api.product_badge.unassign', methods: ['DELETE'])]
    public function unassignBadgeFromProduct(string $id, Context $context): JsonResponse
    {
        try {
            $this->productBadgeProductRepository->delete([['id' => $id]], $context);

            return new JsonResponse([
                'success' => true,
                'message' => 'Badge unassigned successfully'
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Failed to unassign badge: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route(path: '/api/product/{productId}/badges', name: 'api.product.badges', methods: ['GET'])]
    public function getProductBadges(string $productId, Context $context): JsonResponse
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productId', $productId));
        $criteria->addAssociation('swagProductBadge');
        $criteria->addAssociation('swagProductBadge.translations');
        $criteria->addAssociation('media');

        $result = $this->productBadgeProductRepository->search($criteria, $context);

        return new JsonResponse([
            'success' => true,
            'data' => $result->getEntities(),
            'total' => $result->getTotal()
        ]);
    }

    #[Route(path: '/api/product-badge/{badgeId}/products', name: 'api.product_badge.products', methods: ['GET'])]
    public function getBadgeProducts(string $badgeId, Context $context): JsonResponse
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('swagProductBadgeId', $badgeId));
        $criteria->addAssociation('product');
        $criteria->addAssociation('media');

        $result = $this->productBadgeProductRepository->search($criteria, $context);

        return new JsonResponse([
            'success' => true,
            'data' => $result->getEntities(),
            'total' => $result->getTotal()
        ]);
    }

    #[Route(path: '/api/product-badge/bulk-assign', name: 'api.product_badge.bulk_assign', methods: ['POST'])]
    public function bulkAssignBadges(Request $request, Context $context): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['assignments']) || !is_array($data['assignments'])) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Missing or invalid assignments data'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $assignments = [];
            $requiredFields = ['productId', 'productVersionId', 'swagProductBadgeId'];
            
            foreach ($data['assignments'] as $index => $assignment) {
                foreach ($requiredFields as $field) {
                    if (!isset($assignment[$field])) {
                        return new JsonResponse([
                            'success' => false,
                            'message' => "Missing required field '{$field}' in assignment {$index}"
                        ], Response::HTTP_BAD_REQUEST);
                    }
                }
                
                $assignments[] = [
                    'productId' => $assignment['productId'],
                    'productVersionId' => $assignment['productVersionId'],
                    'swagProductBadgeId' => $assignment['swagProductBadgeId'],
                    'mediaId' => $assignment['mediaId'] ?? null,
                ];
            }

            $this->productBadgeProductRepository->create($assignments, $context);

            return new JsonResponse([
                'success' => true,
                'message' => 'Badges assigned successfully',
                'count' => count($assignments)
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Failed to bulk assign badges: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route(path: '/api/product-badge/remove-from-product', name: 'api.product_badge.remove_from_product', methods: ['DELETE'])]
    public function removeBadgeFromProduct(Request $request, Context $context): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['productId']) || !isset($data['badgeId'])) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Missing required fields: productId and badgeId'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter('productId', $data['productId']));
            $criteria->addFilter(new EqualsFilter('swagProductBadgeId', $data['badgeId']));
            
            if (isset($data['productImageId']) && $data['productImageId']) {
                $criteria->addFilter(new EqualsFilter('mediaId', $data['productImageId']));
            }

            $result = $this->productBadgeProductRepository->search($criteria, $context);
            
            if ($result->getTotal() === 0) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Badge assignment not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $assignmentIds = [];
            foreach ($result->getEntities() as $assignment) {
                $assignmentIds[] = ['id' => $assignment->getId()];
            }

            $this->productBadgeProductRepository->delete($assignmentIds, $context);

            return new JsonResponse([
                'success' => true,
                'message' => 'Badge removed successfully'
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Failed to remove badge: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


}
