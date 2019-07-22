<?php

namespace app\controllers;

use Yii;
use app\models\Bundles;
use app\models\BundlesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use app\models\Products;
use app\models\Shops;
use app\models\shopify\Product;
use app\models\shopify\Store as ShopifyStore; 

/**
 * BundlesController implements the CRUD actions for Bundles model.
 */
class BundlesController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Bundles models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BundlesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Bundles model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Bundles model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Bundles();
		$modelProducts = [new Products];
   
   $model->shopify_id = 1;

   $storeModel = Shops::findOne(['permanent_domain' =>  $_SESSION['shop']]);

   $shop = $storeModel->permanent_domain;
   $tokenStore = $storeModel->access_token;
   $model->shop_id = $storeModel->id;

   $shopifyStoreObj = new ShopifyStore($shop,  $tokenStore);
if(Yii::$app->request->isPost){
   $model->load(Yii::$app->request->post());
    
		if ($model->validate()){

        if($model->image){
           $fileName = $model->image->baseName. '.'. $model->image->extension;
           $filePath = Yii::$app->params['bundleImages'].$fileName;

           if ($model->image->saveAs($filePath)){
               $model->image = $fileName;
           }
       }

	   $bundleData = $model->toArray();
       $bundleData['vendor'] = 'Bundle';
       $bundleData['keywords'] = 'Bundle';
       $bundleData['type'] = 'Bundle';
       $bundleData['variants'][] = ['price'=>$model->price,'title'=>$model->title,'isbn_13'=>'N/A','record_reference'=>'N/A','requires_shipping'=> true];
       $bundleData['metafields'][] = [];
       $bundleData['front_cover'] = [Yii::$app->params['app_URL'].Yii::$app->params['bundleImages'].$model->image];
	 

       $rowData = new Product($bundleData,[]);
       
      
      $result = $shopifyStoreObj->createProduct($rowData->get());
	 
   }else
   {
	   print_r($model->getErrors());
	   die();
   }		
		
	}
   

   return $this->render('create', [
       'model' => $model,
       'modelProducts' => (empty($modelProducts)) ? [new Products] : $modelProducts
   ]);
    }

    /**
     * Updates an existing Bundles model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->shopify_id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Bundles model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Bundles model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Bundles the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Bundles::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
