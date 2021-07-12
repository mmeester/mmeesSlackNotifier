<?php
/**
 * CurrencyHelper
 *
 * @copyright Copyright © 2020 e-mmer. All rights reserved.
 * @author    maurits@e-mmer.nl
 */

namespace Mmeester\SlackNotifier\Helper;

class CurrencyHelper
{
    public function formatForSlack($price, $currency = 'EUR') {
        $locale = 'NL_nl';
        $formatter =  new \NumberFormatter($locale, \NumberFormatter::CURRENCY);

        return $formatter->formatCurrency($price, $currency);
    }


}
