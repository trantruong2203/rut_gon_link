<?php
$this->assign('title', __('Fraud Report by IP'));
$this->assign('description', '');
$this->assign('content_title', __('Fraud Report by IP'));

?>

<div class="box box-primary">
    <div class="box-body">
        <p class="text-muted"><?= __('Report shows IPs with paid views. Referer ratio indicates traffic source: Google Search vs Direct vs other.') ?></p>

        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th><?= __('IP') ?></th>
                    <th><?= __('Views') ?></th>
                    <th><?= __('Earnings') ?></th>
                    <th><?= __('Referer Ratio') ?></th>
                    <th><?= __('Top Referers') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (isset($ipDetails) ? $ipDetails : [] as $detail): ?>
                <tr>
                    <td><?= h($detail['ip']) ?></td>
                    <td><?= (int) $detail['views'] ?></td>
                    <td><?= display_price_currency($detail['earnings']) ?></td>
                    <td>
                        <small>
                            <?= __('Google') ?>: <?= $detail['google_ratio'] ?>% |
                            <?= __('Direct') ?>: <?= $detail['direct_ratio'] ?>% |
                            <?= __('Other') ?>: <?= $detail['other_ratio'] ?>%
                        </small>
                    </td>
                    <td>
                        <?php
                        $refs = array_slice($detail['referer_breakdown'], 0, 3);
                        $parts = [];
                        foreach ($refs as $r) {
                            $parts[] = h($r->referer_domain ?: 'Direct') . ' (' . $r->count . ')';
                        }
                        echo implode(', ', $parts);
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (empty($ipDetails)): ?>
        <p><?= __('No data yet.') ?></p>
        <?php endif; ?>
    </div>
</div>
