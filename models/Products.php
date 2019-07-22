<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "products".
 *
 * @property int $id
 * @property string $title
 * @property string $sku
 * @property string $vendor
 * @property string $price
 * @property int $deleted
 * @property string $bundle_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Bundles $bundle
 */
class Products extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'products';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'sku', 'vendor', 'price', 'bundle_id'], 'required'],
            [['price'], 'number'],
            [['deleted', 'bundle_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['title', 'sku'], 'string', 'max' => 255],
            [['vendor'], 'string', 'max' => 100],
            [['bundle_id'], 'exist', 'skipOnError' => true, 'targetClass' => Bundles::className(), 'targetAttribute' => ['bundle_id' => 'shopify_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'sku' => 'Sku',
            'vendor' => 'Vendor',
            'price' => 'Price',
            'deleted' => 'Deleted',
            'bundle_id' => 'Bundle ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBundle()
    {
        return $this->hasOne(Bundles::className(), ['shopify_id' => 'bundle_id']);
    }
}
