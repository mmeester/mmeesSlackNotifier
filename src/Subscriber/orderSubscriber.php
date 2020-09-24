<?php
/**
 * Subscriber to listen to order events
 *
 * @copyright Copyright © 2020 e-mmer. All rights reserved.
 * @author    maurits@e-mmer.nl
 */
declare(strict_types=1);

namespace Mmeester\SlackNotifier\Subscriber;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Core\Checkout\Order\OrderEvents;

use GuzzleHttp\Client;

class orderSubscriber extends AbstractController implements EventSubscriberInterface
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
     * orderSubscriber constructor.
     *
     * @param EntityRepositoryInterface $orderRepository
     */
    public function __construct(
        EntityRepositoryInterface $orderRepository
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
            OrderEvents::ORDER_WRITTEN_EVENT => 'onOrderWritten'
        ];
    }

    /**
     * @param EntityWrittenEvent $event
     */
    public function onOrderWritten(EntityWrittenEvent $event)
    {
        $orderId = $event->getWriteResults()[0]->getPayload()['id'];

        $criteria = new Criteria([$orderId]);
        $criteria->addAssociation('addresses.country');

        $order = $this->orderRepository->search($criteria, $orderId);
        var_dump($order);
        die();

        $this->client->post("https://hooks.slack.com/services/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",
            [
                'body' => '{"text":"Hello, World!"}',
                'headers' => [
                    'Content-Type' => 'application/json',
                ]
            ]
        );

    }

}
