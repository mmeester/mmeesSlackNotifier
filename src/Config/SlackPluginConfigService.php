<?php

declare(strict_types=1);

namespace Mmeester\SlackNotifier\Config;

use Shopware\Core\System\SystemConfig\SystemConfigService;

class slackPluginConfigService
{
    private const CONFIG_KEY = 'mmeesSlackNotifier.config';

    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    public function __construct(SystemConfigService $systemConfigService)
    {
        $this->systemConfigService = $systemConfigService;
    }

    public function getSlackPluginConfigForSalesChannel(?string $salesChannelId = null): SlackPluginConfig
    {
        $rawConfig = $this->systemConfigService->get(self::CONFIG_KEY, $salesChannelId);

        return new SlackPluginConfig($rawConfig ?? []);
    }

    public function setSlackPluginConfigForSalesChannel(
        SlackPluginConfig $slackPluginConfig,
        ?string $salesChannelId = null
    ): void {
        foreach ($slackPluginConfig->getRawConfig() as $configKey => $configValue) {
            $this->systemConfigService->set(self::CONFIG_KEY . '.' . $configKey, $configValue, $salesChannelId);
        }
    }
}
