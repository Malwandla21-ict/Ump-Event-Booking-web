<?php
/** @var string|null $pageTitle */
$appName = config('app.name');
$title = isset($pageTitle) ? "$pageTitle · $appName" : $appName;
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="Discover and book University of Mpumalanga campus events — talks, festivals, sports and more.">
<meta name="theme-color" content="#16285c">
<link rel="icon" type="image/png" href="<?= asset('assets/img/logo-crest.png') ?>">
<link rel="stylesheet" href="<?= asset('assets/css/app.css') ?>">
<script src="<?= asset('assets/js/theme.js') ?>"></script>
<script src="<?= asset('assets/js/app.js') ?>" defer></script>
