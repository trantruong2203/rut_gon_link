<?php

namespace App\Command;

use App\Service\CampaignVerificationService;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\I18n\FrozenTime;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;

class CampaignVerificationRecheckCommand extends Command
{
    public static function defaultName(): string
    {
        return 'campaign_verification_recheck';
    }

    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser->setDescription('Re-check campaign website verification status for existing campaigns.');
        $parser->addOption('limit', [
            'short' => 'l',
            'default' => 200,
            'help' => 'Max number of campaigns to process.',
        ]);
        $parser->addOption('days', [
            'short' => 'd',
            'default' => 1,
            'help' => 'Only re-check campaigns not checked in the last N days.',
        ]);
        $parser->addOption('dry-run', [
            'boolean' => true,
            'default' => false,
            'help' => 'Show result without saving to DB.',
        ]);

        return $parser;
    }

    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $limit = max(1, (int)$args->getOption('limit'));
        $days = max(0, (int)$args->getOption('days'));
        if ($limit === 200) {
            $limit = max(1, (int)get_option('campaign_verify_recheck_limit', 200));
        }
        if ($days === 1) {
            $days = max(0, (int)get_option('campaign_verify_recheck_days', 1));
        }
        $dryRun = (bool)$args->getOption('dry-run');
        $cutoff = FrozenTime::now()->subDays($days);

        $campaignsTable = TableRegistry::getTableLocator()->get('Campaigns');

        $query = $campaignsTable->find()
            ->where(function ($exp) use ($cutoff) {
                return $exp->or_([
                    'verification_checked_at IS' => null,
                    ['verification_checked_at <' => $cutoff],
                ]);
            })
            ->where(['verification_token IS NOT' => null])
            ->where(['website_url IS NOT' => null])
            ->where(['website_url !=' => ''])
            ->where(['status IN' => [1, 2, 5, 6]])
            ->orderAsc('id')
            ->limit($limit);

        $campaigns = $query->all();
        $total = $campaigns->count();

        $io->out(sprintf(
            'Campaign verification re-check started. total=%d limit=%d days=%d dry_run=%s',
            $total,
            $limit,
            $days,
            $dryRun ? 'yes' : 'no'
        ));

        $success = 0;
        $failed = 0;

        foreach ($campaigns as $campaign) {
            $campaign->verification_status = CampaignVerificationService::STATUS_PENDING;
            $result = CampaignVerificationService::verifyAndApply($campaign, 'cron_daily');

            if (!$dryRun) {
                $saved = (bool)$campaignsTable->save($campaign);
            } else {
                $saved = true;
            }

            if ($saved && $result['verified']) {
                $success++;
            } else {
                $failed++;
            }

            $io->out(sprintf(
                '#%d status=%s saved=%s note=%s',
                (int)$campaign->id,
                $result['verified'] ? 'verified' : 'failed',
                $saved ? 'yes' : 'no',
                $result['note']
            ));
        }

        $summary = sprintf(
            'Campaign verification re-check completed. processed=%d verified=%d failed=%d dry_run=%s',
            $total,
            $success,
            $failed,
            $dryRun ? 'yes' : 'no'
        );
        $io->success($summary);
        Log::info($summary);

        return static::CODE_SUCCESS;
    }
}
