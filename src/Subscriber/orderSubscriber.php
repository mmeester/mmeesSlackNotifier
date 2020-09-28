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
use Mmeester\SlackNotifier\Config\SlackPluginConfigService;
use Mmeester\SlackNotifier\Helper\CurrencyHelper;

use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use GuzzleHttp\Client;

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
     * @var SlackPluginConfig
     */
    private $slackPluginConfigService;

    /**
     * @var CurrencyHelper
     */
    private $currency;

    /**
     * orderSubscriber constructor.
     *
     * @param OrderRepository          $orderRepository
     * @param SlackPluginConfigService $slackPluginConfig
     * @param CurrencyHelper           $currency
     */
    public function __construct(
        OrderRepository $orderRepository,
        SlackPluginConfigService $slackPluginConfig,
        CurrencyHelper $currency
    )
    {
        $this->orderRepository = $orderRepository;
        $this->slackPluginConfigService =  $slackPluginConfig;
        $this->currency = $currency;
        $this->client = new Client();
    }


    /**
     * @return array|string[]
     */
    public static function getSubscribedEvents()
    {
        return [
            CheckoutOrderPlacedEvent::class => 'onOrderPlaced',
        ];
    }

    /**
     * @param CheckoutOrderPlacedEvent $event
     */
    public function onOrderPlaced(CheckoutOrderPlacedEvent $event)
    {
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
                                "text" => "*New order placed*\nCustomer *" . $customer->getFirstName() . " " . $customer->getLastName() . "* <" . $customer->getEmail() . ">"
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
                                    "text" => "*Total:*\n" . $this->currency->formatCurrency($order->getAmountTotal(), 'NL_nl', 'EUR')
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
                                    "url" => "https://".$_SERVER['SERVER_NAME']."/admin#/sw/order/detail/" . $order->getId()
                                ]
                            ]
                        ]
                    ]
                ];



                $slackPluginConfig = $this->slackPluginConfigService->getSlackPluginConfigForSalesChannel(
                    $event->getSalesChannelId()
                );

                $this->client->post($slackPluginConfig->getSlackEndpoint(),
                    [
                        'body' => json_encode($slackMsg),
                        'headers' => [
                            'Content-Type' => 'application/json',
                        ]
                    ]
                );
            }
        }
    }

}
