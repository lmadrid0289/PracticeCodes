<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Bundles */

$this->title = 'Update Bundles: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Bundles', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->shopify_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="bundles-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
