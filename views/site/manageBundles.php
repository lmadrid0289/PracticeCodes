<?php

/* @var $this yii\web\View */

$this->title = 'My Yii Application';
?>
<div class="site-create">

    <div class="jumbotron">
        <h1>Manage Bundles</h1>

       <?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        'title',
        'shopify_id',
        'price',
        [
            'label' => 'Products',
            'attribute' => 'shopify_id',
            'value' => function ($model) { return $model->getProducts()->count();}
        ],
       
        'created_at',
        
        ],
     ]) ?>

      
    </div>

    <div class="body-content">


    </div>
</div>