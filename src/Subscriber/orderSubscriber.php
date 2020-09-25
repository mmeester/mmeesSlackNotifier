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
     * @var ShipmentEntityRepository
     */
    private $shipmentRepository;

    /**
     * orderSubscriber constructor.
     *
     * @param OrderRepository          $orderRepository
     * @param ShipmentEntityRepository $shipmentRepository
     */
    public function __construct(
        OrderRepository $orderRepository
    )
    {
        $this->orderRepository = $orderRepository;
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

            $customer = $order->getOrderCustomer();
            $shipments = $order->getDeliveries()->getShippingMethods()->getElements();

            foreach($shipments as $shipment) {
                break;
            }

            if($shipment) {
                $slackMsg = [
                    "blocks" => [
                        [
                            "type" => "section",
                            "text" => [
                                "type" => "mrkdwn",
                                "text" => "A new order has been placed by: *" . $customer->getFirstName() . " " . $customer->getLastName() . "*"
                            ]
                        ],
                        [
                            "type" => "section",
                            "fields" => [
                                [
                                    "type" => "mrkdwn",
                                    "text" => "*Total*\n" . $order->getAmountTotal()
                                ],
                                [
                                    "type" => "mrkdwn",
                                    "text" => "*Shipment*\n" . $shipment->getName()
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

                $this->client->post("https://hooks.slack.com/services/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",
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
