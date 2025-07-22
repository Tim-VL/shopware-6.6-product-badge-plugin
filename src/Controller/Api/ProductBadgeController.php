<?php declare(strict_types=1);

namespace Swag\ProductBadges\Controller\Api;

use Shopware\Core\Framework\Api\Context\AdminApiSource;
use Shopware\Core\Framework\Api\Response\ResponseFactoryRegistry;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\Log\Package;
use Swag\ProductBadges\Service\ProductBadgeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;


#[Package('core')]
#[Route(defaults: ['_routeScope' => ['api']])]
class ProductBadgeController extends AbstractController
{
    public function __construct(
        private readonly EntityRepository $productBadgeRepository,
        private readonly ProductBadgeService $productBadgeService,
        private readonly ResponseFactoryRegistry $responseFactory
    ) {
    }

    #[Route(path: '/api/product-badge', name: 'api.product_badge.search', methods: ['GET', 'POST'])]
    public function search(Request $request, Context $context): Response
    {
        $criteria = new Criteria();
        
        // pagination
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 25);
        $sortBy = $request->query->get('sortBy', 'name');
        $sortDirection = $request->query->get('sortDirection', 'ASC');
        
        // sorting
        $criteria->addSorting(new FieldSorting($sortBy, $sortDirection === 'ASC' ? FieldSorting::ASCENDING : FieldSorting::DESCENDING));
        
        // filters BEFORE calculating total
        if ($request->query->get('active')) {
            $criteria->addFilter(new EqualsFilter('active', true));
        }

        if ($request->query->get('name')) {
            $criteria->addFilter(new ContainsFilter('name', $request->query->get('name')));
        }

        $criteria->addAssociation('image');

        $criteria->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_EXACT);
        
        $offset = ($page - 1) * $limit;
        $criteria->setLimit($limit);
        $criteria->setOffset($offset);

        $result = $this->productBadgeRepository->search($criteria, $context);

        return $this->responseFactory->getType($request)->createListingResponse(
            $criteria,
            $result,
            $this->productBadgeRepository->getDefinition(),
            $request,
            $context
        );
    }

    #[Route(path: '/api/product-badge/{id}', name: 'api.product_badge.detail', methods: ['GET'])]
    public function detail(string $id, Request $request, Context $context): Response
    {
        $criteria = new Criteria([$id]);
        $criteria->addAssociation('image');
        $criteria->addAssociation('productBadgeProducts.product');

        $result = $this->productBadgeRepository->search($criteria, $context);
        $entity = $result->first();

        if (!$entity) {
            return new JsonResponse([
                'errors' => [
                    [
                        'code' => 'PRODUCT_BADGE_NOT_FOUND',
                        'status' => '404',
                        'title' => 'Product badge not found'
                    ]
                ]
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->responseFactory->getType($request)->createDetailResponse(
            $criteria,
            $entity,
            $this->productBadgeRepository->getDefinition(),
            $request,
            $context
        );
    }

    #[Route(path: '/api/product-badge', name: 'api.product_badge.create', methods: ['POST'])]
    public function create(Request $request, Context $context): Response
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return new JsonResponse([
                'errors' => [
                    [
                        'code' => 'INVALID_JSON',
                        'status' => '400',
                        'title' => 'Invalid JSON data'
                    ]
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $result = $this->productBadgeRepository->create([$data], $context);
            $id = $result->getPrimaryKeys('swag_product_badge')[0];

            return new JsonResponse([
                'success' => true,
                'data' => ['id' => $id]
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse([
                'errors' => [
                    [
                        'code' => 'CREATE_ERROR',
                        'status' => '500',
                        'title' => 'Failed to create product badge',
                        'detail' => $e->getMessage()
                    ]
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route(path: '/api/product-badge/{id}', name: 'api.product_badge.update', methods: ['PATCH'])]
    public function update(string $id, Request $request, Context $context): Response
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return new JsonResponse([
                'errors' => [
                    [
                        'code' => 'INVALID_JSON',
                        'status' => '400',
                        'title' => 'Invalid JSON data'
                    ]
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        $data['id'] = $id;

        try {
            $this->productBadgeRepository->update([$data], $context);

            return new JsonResponse([
                'success' => true
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'errors' => [
                    [
                        'code' => 'UPDATE_ERROR',
                        'status' => '500',
                        'title' => 'Failed to update product badge',
                        'detail' => $e->getMessage()
                    ]
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route(path: '/api/product-badge/{id}', name: 'api.product_badge.delete', methods: ['DELETE'])]
    public function delete(string $id, Context $context): Response
    {
        try {
            $this->productBadgeRepository->delete([['id' => $id]], $context);

            return new JsonResponse([
                'success' => true
            ], Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return new JsonResponse([
                'errors' => [
                    [
                        'code' => 'DELETE_ERROR',
                        'status' => '500',
                        'title' => 'Failed to delete product badge',
                        'detail' => $e->getMessage()
                    ]
                ]
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route(path: '/api/product-badge/product/{productId}', name: 'api.product_badge.product_badges', methods: ['GET'], defaults: ['_routeScope' => ['api']])]
    public function getBadgesForProduct(string $productId, Request $request, Context $context): JsonResponse
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productBadgeProducts.productId', $productId));
        $criteria->addAssociation('image'); // Badge image
        $criteria->addAssociation('translations');
        $criteria->addAssociation('productBadgeProducts.product'); // Product association
        $criteria->addAssociation('productBadgeProducts.media'); // Media assigned to the badge-product relation
        $criteria->addAssociation('productBadgeProducts.productBadge');

        $result = $this->productBadgeRepository->search($criteria, $context);

        $badges = [];
        foreach ($result->getEntities() as $badge) {
            $translations = $badge->getTranslations();
            $firstTranslation = $translations ? $translations->first() : null;
            $badgeLabel = $firstTranslation ? $firstTranslation->getLabel() : $badge->getName();
            
            $productBadgeProducts = $badge->getProductBadgeProducts();
            
            if ($productBadgeProducts) {
                $filteredProductBadgeProducts = $productBadgeProducts->filter(function($productBadgeProduct) use ($productId) {
                    return $productBadgeProduct->getProductId() === $productId;
                });
                
                foreach ($filteredProductBadgeProducts as $productBadgeProduct) {
                    $badgeData = $badge->jsonSerialize();
                    $badgeData['label'] = $badgeLabel;
                    
                    $badgeData['productImage'] = null;
                    
                    if ($productBadgeProduct->getMedia()) {
                        $badgeData['productImage'] = $productBadgeProduct->getMedia()->jsonSerialize();
                    }
                    elseif ($productBadgeProduct->getProduct() && $productBadgeProduct->getProduct()->getCover()) {
                        $badgeData['productImage'] = $productBadgeProduct->getProduct()->getCover()->jsonSerialize();
                    }
                    
                    $badgeData['productBadgeProductId'] = $productBadgeProduct->getId();
                    
                    $badges[] = $badgeData;
                }
            }
        }

        return new JsonResponse([
            'data' => $badges,
            'total' => count($badges)
        ]);
    }
}
