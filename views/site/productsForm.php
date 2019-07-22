<?php



use yii\helpers\Html;
use yii\widgets\ActiveForm;



/* @var $this yii\web\View */


?>
<div class="site-create">

    <div class="jumbotron">
        <h1>Add products to the bundle</h1>
        


      <!-- info about the products-->
       
      <?php $form = ActiveForm::begin();?>
       
      <?= $form->field($model,'title')->textInput(['maxlength'=>true]);?>
      <?= $form->field($model,'sku')->textInput();?>
      <?= $form->field($model,'price')->textInput();?>
      <?= $form->field($model,'vendor')->textInput();?>  
      
      <?= Html::submitButton('Create',['class'=>'btn btn-success']) ;?>
      <?php ActiveForm::end(); ?>

    </div>

    <div class="body-content">
    <!-- list of products in the bundle-->

    <?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        'title',
        'sku',
        'price',
        'bundle_id',
        'created_at',
        
        ],
     ]) ?>


    </div>
</div>