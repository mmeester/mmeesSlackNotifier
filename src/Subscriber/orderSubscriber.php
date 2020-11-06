<?php
/**
 * Subscriber to listen to order events
 *
 * @copyright Copyright © 2020 e-mmer. All rights reserved.
 * @author    maurits@e-mmer.nl
 */
declare(strict_types=1);

namespace Mmeester\SlackNotifier\Subscriber;

use Mmeester\SlackNotifier\Entity\Order\OrderRepository;
use Mmeester\SlackNotifier\Helper\CurrencyHelper;
use Mmeester\SlackNotifier\Helper\SettingsHelper;
use Mmeester\SlackNotifier\Helper\SlackHelper;

use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class orderSubscriber implements EventSubscriberInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var EntityRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CurrencyHelper
     */
    private $currency;

    /**
     * @var Slack
     */
    private $slack;

    /**
     * @var SettingsHelper
     */
    private $settings;

    /**
     * orderSubscriber constructor.
     *
     * @param OrderRepository $orderRepository
     * @param CurrencyHelper  $currency
     * @param SlackHelper           $slack
     */
    public function __construct(
        OrderRepository $orderRepository,
        CurrencyHelper $currency,
        SlackHelper $slack,
        SettingsHelper $settings
    )
    {
        $this->orderRepository = $orderRepository;
        $this->currency = $currency;
        $this->slack = $slack;
        $this->settings = $settings;
    }

    /**
     * @return array|string[]
     */
    public static function getSubscribedEvents()
    {
        return [
            CheckoutOrderPlacedEvent::class => 'onOrderPlaced'
        ];
    }

    /**
     * @param CheckoutOrderPlacedEvent $event
     */
    public function onOrderPlaced(CheckoutOrderPlacedEvent $event)
    {
        $salesChannelId =  $event->getSalesChannelId();

        if($this->settings->isOrderNotification("orderPlaced", $salesChannelId) === true) {
            $order = $event->getOrder();
            if ($order) {
                $shipment = null;

                $customer = $order->getOrderCustomer();
                $shipments = $order->getDeliveries()->getShippingMethods()->getElements();

                foreach($shipments as $ship) {
                    $shipment = $ship;
                    break;
                }

                $slackItems = "";
                $items = $order->getLineItems();
                foreach($items as $item) {
                    $slackItems .= $item->getQuantity(). "x ".$item->getLabel()."\n";
                }

                if($shipment !== null) {
                    $slackMsg = [
                        "blocks" => [
                            [
                                "type" => "section",
                                "text" => [
                                    "type" => "mrkdwn",
                                    "text" => "*New order (" . $order->getOrderNumber() . ") placed*\nCustomer *" . $customer->getFirstName() . " " . $customer->getLastName() . "* <" . $customer->getEmail() . ">"
                                ]
                            ],
                            [
                                "type" => "section",
                                "fields" => [
                                    [
                                        "type" => "mrkdwn",
                                        "text" => "*Order:*\n" . $slackItems
                                    ],
                                ]
                            ],
                            [
                                "type" => "section",
                                "fields" => [
                                    [
                                        "type" => "mrkdwn",
                                        "text" => "*Total:*\n" . $this->currency->formatCurrency($order->getAmountTotal(),
                                                'NL_nl', 'EUR')
                                    ],
                                    [
                                        "type" => "mrkdwn",
                                        "text" => "*Shipment:*\n" . $shipment->getName()
                                    ]
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
                    ];
                    $this->slack->sendMessage($slackMsg, $salesChannelId);
                }
            }
        }
    }

}
