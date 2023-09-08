<?php
declare(strict_types = 1);
namespace Cylancer\CyNewsletter\Upgrades;

use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

final class UserToolsMigrationWizard implements UpgradeWizardInterface
{

    /**
     * Return the speaking name of this wizard
     */
    public function getTitle(): string
    {
        return '[cylancer.net] newsletter usertools migration wizard';
    }

    /**
     * Return the description for this wizard
     */
    public function getDescription(): string
    {
        return '[cylancer.net] newsletter usertools migration wizard';
    }

    /**
     * Execute the update
     *
     * Called when a wizard reports that an update is necessary
     */
    public function executeUpdate(): bool
    {
        /** @var QueryBuilder $source */
        $source = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_usertools_domain_model_newsletterlog');
        $source->select('pid','news')->from('tx_usertools_domain_model_newsletterlog');
        
        $sourceStatement = $source->execute();
        
        /** @var QueryBuilder $target */
        
        $target = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_usertools_domain_model_newsletterlog');
        $target->insert('tx_cynewsletter_domain_model_newsletterlog');
        
        while ($row = $sourceStatement->fetch()) {
            $target->values([
                'pid' => $row['pid'],
                'news' => $row['news']
            ])->executeStatement();
        }
        return true;
    }
    /**
     * Is an update necessary?
     *
     * Is used to determine whether a wizard needs to be run.
     * Check if data for migration exists.
     *
     * @return bool Whether an update is required (TRUE) or not (FALSE)
     */
    public function updateNecessary(): bool
    {
        if (isset($GLOBALS['TCA']['tx_usertools_domain_model_newsletterlog'])) {

            /** @var QueryBuilder $source */
            $source = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_usertools_domain_model_newsletterlog');
            $source->select('news')
                ->from('tx_usertools_domain_model_newsletterlog')
                ->setMaxResults(1);

            $sourceStatement = $source->execute();
            if (! ($row = $sourceStatement->fetchAllNumeric() !== false)) {
                return false;
            }

            /** @var QueryBuilder $target */
            $target = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_cynewsletter_domain_model_newsletterlog');
            $target->select('news')
                ->from('tx_usertools_domain_model_newsletterlog')
                ->setMaxResults(1);

            $targetStatement = $target->execute();
            if ($row = $targetStatement->fetchAllNumeric() !== false) {
                return true;
            }

            return false;
        }
        return true;
    }

    /**
     * Returns an array of class names of prerequisite classes
     *
     * This way a wizard can define dependencies like "database up-to-date" or
     * "reference index updated"
     *
     * @return string[]
     */
    public function getPrerequisites(): array
    {
        return [];
    }

    public function getIdentifier(): string
    {
        return 'cynewsletter_newsletterUsertoolsMigrationWizard';
    }
}
