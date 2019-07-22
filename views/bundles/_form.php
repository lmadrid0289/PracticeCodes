<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use wbraganca\dynamicform\DynamicFormWidget;

/* @var $this yii\web\View */
/* @var $model app\models\Bundles */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="bundles-form">

    <?php $form = ActiveForm::begin(['id'=>'dynamic-form',
          'options'=> ['enctype' => 'multipart/form-data']
      ]); ?>

    
    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'tags')->textInput(['maxlength' => true]) ?>
    
    


    <?= $form->field($model, 'description')->textarea(['rows' => '6']) ?>

    <?= $form->field($model, 'image')->fileInput(); ?>

    <?= $form->field($model, 'price')->textInput(['maxlength' => true]) ?>

    
    <div class="panel panel-default">
        <div class="panel-heading"><h4><i class="glyphicon glyphicon-shopping-cart"></i> Products</h4></div>
        <div class="panel-body">
             <?php DynamicFormWidget::begin([
                'widgetContainer' => 'dynamicform_wrapper', // required: only alphanumeric characters plus "_" [A-Za-z0-9_]
                'widgetBody' => '.container-items', // required: css class selector
                'widgetItem' => '.item', // required: css class
                'limit' => 10, // the maximum times, an element can be cloned (default 999)
                'min' => 1, // 0 or 1 (default 1)
                'insertButton' => '.add-item', // css class
                'deleteButton' => '.remove-item', // css class
                'model' => $modelProducts[0],
                'formId' => 'dynamic-form',
                'formFields' => [
                    'title',
                    'sku',
                    'vendor',
                    'price',                                
                ],
            ]); ?>

            <div class="container-items"><!-- widgetContainer -->
            <?php foreach ($modelProducts as $i => $modelOneProduct): ?>
                <div class="item panel panel-default"><!-- widgetBody -->
                    <div class="panel-heading">
                        <h3 class="panel-title pull-left">Product</h3>
                        <div class="pull-right">
                            <button type="button" class="add-item btn btn-success btn-xs"><i class="glyphicon glyphicon-plus"></i></button>
                            <button type="button" class="remove-item btn btn-danger btn-xs"><i class="glyphicon glyphicon-minus"></i></button>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="panel-body">
                        <?php
                            // necessary for update action.
                            if (! $modelOneProduct->isNewRecord) {
                                echo Html::activeHiddenInput($modelOneProduct, "[{$i}]id");
                            }
                        ?>
                        <?= $form->field($modelOneProduct, "[{$i}]title")->textInput(['maxlength' => true]) ?>
                        <div class="row">
                            <div class="col-sm-6">
                                <?= $form->field($modelOneProduct, "[{$i}]sku")->textInput(['maxlength' => true]) ?>
                            </div>
                            <div class="col-sm-6">
                                <?= $form->field($modelOneProduct, "[{$i}]vendor")->textInput(['maxlength' => true]) ?>
                            </div>
                        </div><!-- .row -->
                        <div class="row">
                            <div class="col-sm-4">
                                <?= $form->field($modelOneProduct, "[{$i}]price")->textInput(['maxlength' => true]) ?>
                            </div>
                            
                        </div><!-- .row -->
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
            <?php DynamicFormWidget::end(); ?>
        </div>
    </div>











    

    <div class="form-group">
        <?= Html::submitButton('Create Bundle', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
