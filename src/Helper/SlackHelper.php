<?php
/**
 * SlackHelper
 *
 * @copyright Copyright © 2020 e-mmer. All rights reserved.
 * @author    maurits@e-mmer.nl
 */
declare(strict_types=1);

namespace Mmeester\SlackNotifier\Helper;

use Mmeester\SlackNotifier\Config\SlackPluginConfigService;
use GuzzleHttp\Client;

class SlackHelper
{
    /**
     * @var SlackPluginConfigService
     */
    private $slackPluginConfigService;

    /**
     * SlackHelper constructor.
     *
     * @param SlackPluginConfigService $slackPluginConfig
     */
    public function __construct(
        SlackPluginConfigService $slackPluginConfig
    )
    {
        $this->slackPluginConfigService = $slackPluginConfig;
        $this->client = new Client();
    }

    /**
     * @param $channelId
     *
     * @return string|null
     */
    private function getSlackEndpoint($channelId): ?string {
        $slackPluginConfig = $this->slackPluginConfigService->getSlackPluginConfigForSalesChannel(
            $channelId
        );

        return $slackPluginConfig->getSlackEndpoint();
    }

    /**
     * @param $msg
     * @param $channelId
     *
     * @return \Psr\Http\Message\ResponseInterface|null
     */
    public function sendMessage($msg, $channelId): ?\Psr\Http\Message\ResponseInterface {
        if(!empty($this->getSlackEndpoint($channelId))) {
            return $this->client->post($this->getSlackEndpoint($channelId),
                [
                    'body' => json_encode($msg),
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ]
                ]
            );
        }
        return null;
    }

}
