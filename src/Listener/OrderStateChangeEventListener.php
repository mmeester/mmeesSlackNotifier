<?php
/**
 * Order state change event listeners
 *
 * @copyright Copyright © 2020 e-mmer. All rights reserved.
 * @author    maurits@e-mmer.nl
 */
declare(strict_types=1);

namespace Mmeester\SlackNotifier\Listener;

use Mmeester\SlackNotifier\Helper\SlackHelper;
use Mmeester\SlackNotifier\Helper\SettingsHelper;
use Shopware\Core\Checkout\Cart\Exception\OrderDeliveryNotFoundException;
use Shopware\Core\Checkout\Cart\Exception\OrderNotFoundException;
use Shopware\Core\Checkout\Cart\Exception\OrderTransactionNotFoundException;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\StateMachine\Event\StateMachineStateChangeEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class OrderStateChangeEventListener
{
    /**
     * @var EntityRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $orderTransactionRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $orderDeliveryRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var Slack
     */
    private $slack;

    /**
     * @var SettingsHelper
     */
    private $settings;

    /**
     * Count number of times state change is called to only show message once
     *
     * @var int
     */
    private $count = 0;

    public function __construct(
        EntityRepositoryInterface $orderRepository,
        EntityRepositoryInterface $orderTransactionRepository,
        EntityRepositoryInterface $orderDeliveryRepository,
        EventDispatcherInterface $eventDispatcher,
        SlackHelper $slack,
        SettingsHelper $settings
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderTransactionRepository = $orderTransactionRepository;
        $this->orderDeliveryRepository = $orderDeliveryRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->slack = $slack;
        $this->settings = $settings;
    }

    /**
     * @throws OrderNotFoundException
     * @throws OrderTransactionNotFoundException
     */
    public function onOrderTransactionStateChange(StateMachineStateChangeEvent $event): void
    {
        if($this->count === 0) {
            $salesChannelId = $event->getSalesChannelId();

            if($this->settings->isOrderNotification("transactionStatus", $salesChannelId) === true) {
                $orderTransactionId = $event->getTransition()->getEntityId();

                /** @var OrderTransactionEntity|null $orderTransaction */
                $orderTransaction = $this->orderTransactionRepository->search(
                    new Criteria([$orderTransactionId]),
                    $event->getContext()
                )->first();

                if ($orderTransaction === null) {
                    throw new OrderTransactionNotFoundException($orderTransactionId);
                }

                $orderId = $orderTransaction->getOrderId();

                $order = $this->getOrder($orderId, $event->getContext());
                $customer = $order->getOrderCustomer();

                $this->slack->sendMessage([
                    "blocks" => [
                        [
                            "type" => "section",
                            "text" => [
                                "type" => "mrkdwn",
                                "text" => "_Payment status_ for order (" . $order->getOrderNumber() . ") changed: *" . $event->getNextState()->getName() . "* \nCustomer: *" . $customer->getFirstName() . " " . $customer->getLastName() . "* <" . $customer->getEmail() . ">"
                            ]
                        ],
                        [
                            "type" => "actions",
                            "elements" => [
                                [
                                    "type" => "button",
                                    "style" => "primary",
                                    "text" => [
                                        "type" => "plain_text",
                                        "text" => "View order"
                                    ],
                                    "url" => "https://" . $_SERVER['SERVER_NAME'] . "/admin#/sw/order/detail/" . $order->getId()
                                ]
                            ]
                        ]
                    ]
                ], $salesChannelId);
            }
        }

        $this->count++;
    }

    /**
     * @throws OrderDeliveryNotFoundException
     * @throws OrderNotFoundException
     */
    public function onOrderDeliveryStateChange(StateMachineStateChangeEvent $event): void
    {
        if($this->count === 0) {
            $salesChannelId = $event->getSalesChannelId();

            if($this->settings->isOrderNotification("deliveryStatus", $salesChannelId) === true) {
                $orderDeliveryId = $event->getTransition()->getEntityId();

                /** @var OrderDeliveryEntity|null $orderDelivery */
                $orderDelivery = $this->orderDeliveryRepository->search(
                    new Criteria([$orderDeliveryId]),
                    $event->getContext()
                )->first();

                if ($orderDelivery === null) {
                    throw new OrderDeliveryNotFoundException($orderDeliveryId);
                }

                $orderId = $orderDelivery->getOrderId();

                $order = $this->getOrder($orderId, $event->getContext());
                $customer = $order->getOrderCustomer();

                $this->slack->sendMessage([
                    "blocks" => [
                        [
                            "type" => "section",
                            "text" => [
                                "type" => "mrkdwn",
                                "text" => "_Delivery status_ for order (" . $order->getOrderNumber() . ") changed: *" . $event->getNextState()->getName() . "* \nCustomer: *" . $customer->getFirstName() . " " . $customer->getLastName() . "* <" . $customer->getEmail() . ">"
                            ]
                        ],
                        [
                            "type" => "actions",
                            "elements" => [
                                [
                                    "type" => "button",
                                    "style" => "primary",
                                    "text" => [
                                        "type" => "plain_text",
                                        "text" => "View order"
                                    ],
                                    "url" => "https://" . $_SERVER['SERVER_NAME'] . "/admin#/sw/order/detail/" . $order->getId()
                                ]
                            ]
                        ]
                    ]
                ], $salesChannelId);
            }
        }

        $this->count++;
    }

    /**
     * @throws OrderNotFoundException
     */
    public function onOrderStateChange(StateMachineStateChangeEvent $event): void
    {
        if($this->count === 0) {
            $salesChannelId = $event->getSalesChannelId();

            if ($this->settings->isOrderNotification("orderStatus", $salesChannelId) === true) {
                $orderId = $event->getTransition()->getEntityId();

                $order = $this->getOrder($orderId, $event->getContext());
                $customer = $order->getOrderCustomer();

                $this->slack->sendMessage([
                    "blocks" => [
                        [
                            "type" => "section",
                            "text" => [
                                "type" => "mrkdwn",
                                "text" => "_Order status_ for order (" . $order->getOrderNumber() . ") changed: *" . $event->getNextState()->getName() . "* \nCustomer: *" . $customer->getFirstName() . " " . $customer->getLastName() . "* <" . $customer->getEmail() . ">"
                            ]
                        ],
                        [
                            "type" => "actions",
                            "elements" => [
                                [
                                    "type" => "button",
                                    "style" => "primary",
                                    "text" => [
                                        "type" => "plain_text",
                                        "text" => "View order"
                                    ],
                                    "url" => "https://" . $_SERVER['SERVER_NAME'] . "/admin#/sw/order/detail/" . $order->getId()
                                ]
                            ]
                        ]
                    ]
                ], $salesChannelId);
            }
        }

        $this->count++;
    }

    /**
     * @throws OrderNotFoundException
     */
    private function getOrder(string $orderId, Context $context): OrderEntity
    {
        $orderCriteria = $this->getOrderCriteria($orderId);
        /** @var OrderEntity|null $order */
        $order = $this->orderRepository->search($orderCriteria, $context)->first();
        if ($order === null) {
            throw new OrderNotFoundException($orderId);
        }

        return $order;
    }

    private function getOrderCriteria(string $orderId): Criteria
    {
        $orderCriteria = new Criteria([$orderId]);
        $orderCriteria->addAssociation('orderCustomer.salutation');
        $orderCriteria->addAssociation('stateMachineState');
        $orderCriteria->addAssociation('transactions');
        $orderCriteria->addAssociation('deliveries.shippingMethod');
        $orderCriteria->addAssociation('salesChannel');

        return $orderCriteria;
    }

}
