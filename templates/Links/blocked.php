<?php
$this->assign('title', __('Access Restricted'));
$this->assign('description', __('Your IP appears to be using a VPN or proxy.'));
$message = isset($message) ? $message : __('Your IP appears to be using a VPN or proxy. Please disable it to continue.');
?>
<div class="container" style="padding: 80px 20px 40px;">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-danger">
                <div class="panel-heading">
                    <h3 class="panel-title"><?= __('Access Restricted') ?></h3>
                </div>
                <div class="panel-body">
                    <p class="lead"><?= h($message) ?></p>
                    <p><?= __('To continue, please disable any VPN or proxy service and try again.') ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
