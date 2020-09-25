<?php
/**
 * OrderRepository
 *
 * @copyright Copyright © 2020 e-mmer. All rights reserved.
 * @author    maurits@e-mmer.nl
 */

namespace Mmeester\SlackNotifier\Entity\Order;

use Shopware\Core\Checkout\Order\OrderCollection;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;

/**
 * Class OrderRepository
 *
 * @package Mmeester\SlackNotifier\Entity\Order
 */
class OrderRepository
{
    /**
     * @var EntityRepositoryInterface
     */
    private $baseRepository;

    /**
     * OrderRepository constructor.
     *
     * @param EntityRepositoryInterface $baseRepository
     */
    public function __construct(EntityRepositoryInterface $baseRepository)
    {
        $this->baseRepository = $baseRepository;
    }

    /**
     * Returns ids of the orders which are in the open status
     *
     * @return array
     * @throws InconsistentCriteriaIdsException
     */
    public function getOrderIds(): array
    {
        $criteria = new Criteria();
        $fromDate = (new \DateTime())->sub(new \DateInterval('P30D'));

        $criteria->addFilter(new RangeFilter('orderDateTime', ['gte' => $fromDate->format(DATE_ATOM)]));

        return $this->baseRepository->search($criteria, Context::createDefaultContext())->getIds();
    }

    /**
     * Returns collection of order entities for passed orderIds
     *
     * @param array $orderIds
     *
     * @return OrderCollection
     * @throws InconsistentCriteriaIdsException
     */
    public function getOrders(array $orderIds): OrderCollection
    {
        $criteria = new Criteria($orderIds);
        $criteria->addAssociations($this->getAssociationsForOrder());

        /** @var OrderCollection $collection */
        $collection = $this->baseRepository->search($criteria, Context::createDefaultContext())->getEntities();

        return  $collection;
    }

    /**
     * Returns order entity by order id
     *
     * @param string $orderId
     *
     * @return OrderEntity|null
     * @throws InconsistentCriteriaIdsException
     */
    public function getOrderById(string $orderId): ?OrderEntity
    {
        $criteria = new Criteria([$orderId]);
        $criteria->addAssociations($this->getAssociationsForOrder());

        return $this->baseRepository->search($criteria, Context::createDefaultContext())->first();
    }

    /**
     * Returns order entity by order number
     *
     * @param string $orderNumber
     *
     * @return OrderEntity|null
     * @throws InconsistentCriteriaIdsException
     */
    public function getOrderByNumber(string $orderNumber): ?OrderEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('orderNumber', $orderNumber));
        $criteria->addAssociations($this->getAssociationsForOrder());

        return $this->baseRepository->search($criteria, Context::createDefaultContext())->first();
    }

    /**
     * Returns associations for order search
     *
     * @return array
     */
    private function getAssociationsForOrder(): array
    {
        return [
            'addresses',
            'lineItems.product',
            'currency',
            'orderCustomer.customer',
            'deliveries.shippingMethod',
            'deliveries.shippingOrderAddress.country',
            'deliveries.shippingOrderAddress.state',
            'transactions.paymentMethod'
        ];
    }
}
