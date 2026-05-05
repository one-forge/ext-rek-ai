<?php
declare(strict_types=1);

namespace OneForge\RekAi\DataProcessing;

use Doctrine\DBAL\ArrayParameterType;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

class RekaiSettingsProcessor implements DataProcessorInterface
{
    public function __construct(
        private readonly \OneForge\RekAi\Service\RekAiConfigurationService $configurationService
    ) {}

    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ): array {
        $site = $cObj->getRequest()?->getAttribute('site');
        if ($site instanceof Site) {
            $processedData['rekaiSettings'] = $this->configurationService->getConfigurationForSite($site);
        } else {
            $processedData['rekaiSettings'] = [];
        }

        $data = $processedData['data'];

        if (
            ($data['tx_rekai_rootpath_mode'] ?? '') === 'level'
            && !empty($data['tx_rekai_subtree_pages'])
        ) {
            $pageUids = GeneralUtility::intExplode(',', $data['tx_rekai_subtree_pages'], true);
            $slugs = $this->resolvePageSlugs($pageUids);
            $processedData['data']['tx_rekai_subtree'] = implode(',', $slugs);
        }

        if (
            ($data['tx_rekai_qna_branch_mode'] ?? '') === 'subtree'
            && !empty($data['tx_rekai_qna_subtree_pages'])
        ) {
            $pageUids = GeneralUtility::intExplode(',', $data['tx_rekai_qna_subtree_pages'], true);
            $slugs = $this->resolvePageSlugs($pageUids);
            $processedData['data']['tx_rekai_qna_subtree'] = implode(',', $slugs);
        }

        return $processedData;
    }

    private function resolvePageSlugs(array $pageUids): array
    {
        if (empty($pageUids)) {
            return [];
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('pages');

        $rows = $queryBuilder
            ->select('slug')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->in(
                    'uid',
                    $queryBuilder->createNamedParameter($pageUids, ArrayParameterType::INTEGER)
                )
            )
            ->executeQuery()
            ->fetchAllAssociative();

        return array_column($rows, 'slug');
    }
}
