<?php
/**
 * SettingsHelper
 *
 * @copyright Copyright © 2020 e-mmer. All rights reserved.
 * @author    maurits@e-mmer.nl
 */
declare(strict_types=1);

namespace Mmeester\SlackNotifier\Helper;

use Mmeester\SlackNotifier\Config\SlackPluginConfigService;

class SettingsHelper
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
    }

    /**
     * @param string $notificationType
     * @param null   $channelId
     *
     * @return bool
     */
    public function isOrderNotification($notificationType = "orderPlaced", $channelId = null): bool {
        $slackPluginConfig = $this->slackPluginConfigService->getSlackPluginConfigForSalesChannel(
            $channelId
        );

        $settings = $slackPluginConfig->getOrderNotificationsSettings();

        if(empty($settings))
            return true;

        return in_array($notificationType, $settings);
    }
}
