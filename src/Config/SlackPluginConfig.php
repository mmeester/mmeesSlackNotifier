<?php
declare(strict_types=1);

/**
 * SlackPluginConfig
 *
 * @copyright Copyright © 2020 e-mmer. All rights reserved.
 * @author    maurits@e-mmer.nl
 */

namespace Mmeester\SlackNotifier\Config;


class SlackPluginConfig
{

    /**
     * @var array
     */
    private $rawConfig;

    public function __construct(array $rawConfig)
    {
        $this->rawConfig = $rawConfig;
    }

    /**
     * @return string|null
     */
    public function getSlackEndpoint(): ?string
    {
        return $this->getConfigValueOrNull('endpoint');
    }

    /**
     * @return array|null
     */
    public function getOrderNotificationsSettings(): ?array
    {
        return $this->getConfigValueOrNull('orderNotifications');
    }

    /**
     * @param string $configKey
     *
     * @return mixed|null
     */
    private function getConfigValueOrNull(string $configKey)
    {
        return $this->rawConfig[$configKey] ?? null;
    }
}
