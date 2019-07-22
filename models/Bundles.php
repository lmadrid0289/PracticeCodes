<?php

namespace app\models;

use Yii;
use yii\log\Logger;

/**
 * This is the model class for table "bundles".
 *
 * @property string $shopify_id
 * @property string $title
 * @property string $description
 * @property string $image
 * @property string $price
 * @property int $discrepancies
 * @property int $deleted
 * @property string $shop_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Shops $shop
 * @property Products[] $products
 */
class Bundles extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bundles';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['shopify_id', 'title', 'description', 'price', 'shop_id'], 'required'],
            [['image'], 'required','on' => 'create', 'message' => '{attribute} is required' ],
            [['image'], 'file', 'skipOnEmpty' => true, 'extensions' => 'gif, png, jpg', 'maxSize' => 5242880],//   5MB          
            [['shopify_id', 'discrepancies', 'deleted', 'shop_id'], 'integer'],
            [['price'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 200],          
            [['shopify_id'], 'unique'],
            [['shop_id'], 'exist', 'skipOnError' => true, 'targetClass' => Shops::className(), 'targetAttribute' => ['shop_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'shopify_id' => 'Shopify ID',
            'title' => 'Title',
            'description' => 'Description',
            'image' => 'Image',
            'price' => 'Price',
            'discrepancies' => 'Discrepancies',
            'deleted' => 'Deleted',
            'shop_id' => 'Shop ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShop()
    {
        return $this->hasOne(Shops::className(), ['id' => 'shop_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Products::className(), ['bundle_id' => 'shopify_id']);
    }





}
