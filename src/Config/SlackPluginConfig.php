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

    public function getSlackEndpoint(): ?string
    {
        return $this->getConfigValueOrNull('endpoint');
    }

    private function getConfigValueOrNull(string $configKey)
    {
        return $this->rawConfig[$configKey] ?? null;
    }
}
