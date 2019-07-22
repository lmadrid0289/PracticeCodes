<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Bundles */

$this->title = 'Create Bundles';
$this->params['breadcrumbs'][] = ['label' => 'Bundles', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bundles-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'modelProducts' => $modelProducts,
    ]) ?>

</div>
