<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\Bundles;
use app\models\Products;
use app\models\Shops;
use app\models\Installer;
use app\models\BundlesForm;
use yii\web\UploadedFile;
use app\models\shopify\Store as ShopifyStore; 

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    { 
        $urlParams = Yii::$app->getRequest()->get();

        //http://bundles-apps.bayardcanada.ca/?hmac=84fc8a480fc7a18fe3fde52c5acba5c14e89d25216a07a0b9972bdd8d2ebda93&shop=bayard-dev.myshopify.com&timestamp=1543596300


        if(count($urlParams) == 1 && array_key_exists('shop', $urlParams)){

            $intallerobj = new Installer();
            $redirectURL = $intallerobj->installationURL($urlParams['shop']);

           return $this->redirect($redirectURL) ;
           

        }   
        else if(count($urlParams) > 1){
            
            //if(Installer::validateShopifyRequest($urlParams)){
				
                $shopObj = Shops::findOne(['permanent_domain' => $urlParams['shop']]);
				
				
                if($shopObj){
                   $_SESSION['shop']= $shopObj->permanent_domain;
                   $_SESSION['access_token']= $shopObj->access_token;
   
                }else{
				
				die('shop not found');
			}

           /* }
			else{
				
				die('not shopify');
			}*/
           
        }else{
            if(!isset($_SESSION['access_token'])){
                die('please login through the shop');

            }

        }

       
    //     //here get access token 
    //     //installation of the app set up scopes of user
        
    //     $shopifyStoreObj = new ShopifyStore('bayard-dev.myshopify.com', '02805de023133a387aab3041653e7df5');

    //     $UrlParams->getQueryParam('companyAdd_id');

        
    //     $allProducts = $shopifyStoreObj->getAllProducts(2, ['limit' => 5]);

    //     foreach($allProducts as $product){
    //         $shopifyId[] = $product['id'];
    //     }

    //     echo '<pre>';
    //    print_r($shopifyId);
    //     echo '</pre>';
    //     die();

        return $this->render('index');
    }


    public function actionAuth()
    {
        $urlParams = Yii::$app->getRequest()->get();

        $isValidRequest = Installer::validateShopifyRequest($urlParams);

		if($isValidRequest && $urlParams['state'] ==  $_SESSION['nonce']){
            $result = Installer::exchangeAuthCodeForToken($urlParams['code'], $urlParams['shop']);
            print_r($result);die();
        }
        else{
            // sorry shit happened
        }
    }

    
    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionCreate()
    { 
        
    }

    public function actionAddProducts(array $id)
    {
        
        $searchModel = new ProductsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        



        $model = new Product();
        if($model->load(yii::$app->request->post())){
            
            Yii::$app->session->setFlash('success','Product added');               
                  
        }

        return $this->render('productsForm', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
        
    }

    public function actionManage()
    {
        return $this->render('manageBundles');
    }

    
}
