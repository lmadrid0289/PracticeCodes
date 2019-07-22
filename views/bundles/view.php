<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Bundles */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Bundles', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bundles-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->shopify_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->shopify_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'shopify_id',
            'title',
            'description',
            'image',
            'price',
            'discrepancies',
            'deleted',
            'shop_id',
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>
