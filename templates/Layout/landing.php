<?php $this->assign('title', $this->fetch('title') ?: (__('Get Code') . ' - ' . get_option('site_name'))); ?>
<!DOCTYPE html>
<html lang="<?= locale_get_primary_language(null) ?>">
<head>
    <?= $this->Html->charset(); ?>
    <title><?= h($this->fetch('title')); ?></title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= $this->Html->meta('icon'); ?>
    <?= $this->Html->css('//cdn.rawgit.com/twbs/bootstrap/v3.3.7/dist/css/bootstrap.min.css'); ?>
    <?= $this->Html->css('//cdn.rawgit.com/FortAwesome/Font-Awesome/v4.7.0/css/font-awesome.min.css'); ?>
    <?= $this->fetch('meta'); ?>
    <?= $this->fetch('css'); ?>
</head>
<body class="no-select">
    <?= $this->element('anti_bypass'); ?>
    <?= $this->fetch('content') ?>
    <?= $this->Html->script('//ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js'); ?>
    <?= $this->Html->script('//cdn.rawgit.com/twbs/bootstrap/v3.3.7/dist/js/bootstrap.min.js'); ?>
    <script src="https://www.google.com/recaptcha/api.js?hl=<?= locale_get_primary_language(null) ?>" async defer></script>
    <?= $this->fetch('scriptBottom') ?>
</body>
</html>
