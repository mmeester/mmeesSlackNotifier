<?php
/**
 * LanguageHelper
 *
 * @copyright Copyright © 2020 e-mmer. All rights reserved.
 * @author    maurits@e-mmer.nl
 */

namespace Mmeester\SlackNotifier\Helper;


use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Routing\Exception\LanguageNotFoundException;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\Framework\Context;

class LanguageHelper
{
    /**
     * @var EntityRepositoryInterface
     */
    private $languageRepository;

    /**
     * @var Context
     */
    private $context;

    /**
     * LanguageHelper constructor.
     *
     * @param EntityRepositoryInterface $languageRepository
     */
    public function __construct(EntityRepositoryInterface $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }

    /**
     * @param $context
     *
     * @return string
     */
    public function getLocaleByLanguageId($context)
    {
        $criteria = ( new Criteria() )
            ->addAssociation('locale')
            ->addFilter(new EqualsFilter('language.id', $context->getLanguageId()));

        /** @var LanguageEntity|null $language */
        $language = $this->languageRepository->search($criteria, $this->context)->get($context->getLanguageId());

        if ($language === null) {
            throw new LanguageNotFoundException($context->getLanguageId());
        }

        return $language->getLocale()->getCode();
    }

}
