<?php
$this->assign('title', __('Keyword Tasks'));
$this->assign('description', '');
$this->assign('content_title', __('Keyword Tasks'));

?>

<div class="box box-primary">
    <div class="box-body">
        <p><?= $this->Html->link(__('Add Keyword Task'), ['action' => 'add'], ['class' => 'btn btn-success']); ?></p>

        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id', __('Id')); ?></th>
                    <th><?= $this->Paginator->sort('keyword', __('Keyword')); ?></th>
                    <th><?= __('Target URL') ?></th>
                    <th><?= __('Campaign') ?></th>
                    <th><?= $this->Paginator->sort('status', __('Status')); ?></th>
                    <th><?= $this->Paginator->sort('sort_order', __('Order')); ?></th>
                    <th><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($keywordTasks as $task): ?>
                <tr>
                    <td><?= h($task->id) ?></td>
                    <td><?= h($task->keyword) ?></td>
                    <td><?= h($task->target_url) ?></td>
                    <td><?= $task->campaign ? h($task->campaign->name) : __('-- All --') ?></td>
                    <td><?= $task->status == 1 ? __('Active') : __('Inactive') ?></td>
                    <td><?= h($task->sort_order) ?></td>
                    <td>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $task->id], ['class' => 'btn btn-info btn-xs']); ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $task->id], ['confirm' => __('Are you sure?'), 'class' => 'btn btn-danger btn-xs']); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<ul class="pagination">
    <?php
    if ($this->Paginator->hasPrev()) {
        echo $this->Paginator->prev('«', ['tag' => 'li'], null, ['class' => 'disabled', 'tag' => 'li', 'disabledTag' => 'a']);
    }
    echo $this->Paginator->numbers([
        'modulus' => 4,
        'separator' => '',
        'ellipsis' => '<li><a>...</a></li>',
        'tag' => 'li',
        'currentTag' => 'a',
        'first' => 2,
        'last' => 2
    ]);
    if ($this->Paginator->hasNext()) {
        echo $this->Paginator->next('»', ['tag' => 'li'], null, ['class' => 'disabled', 'tag' => 'li', 'disabledTag' => 'a']);
    }
    ?>
</ul>
