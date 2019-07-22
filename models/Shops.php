<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "shops".
 *
 * @property string $id
 * @property string $permanent_domain
 * @property string $access_token
 * @property string $scopes
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Bundles[] $bundles
 */
class Shops extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'shops';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['permanent_domain', 'access_token', 'scopes'], 'required'],
            [['scopes'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['permanent_domain', 'access_token'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'permanent_domain' => 'Permanent Domain',
            'access_token' => 'Access Token',
            'scopes' => 'Scopes',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBundles()
    {
        return $this->hasMany(Bundles::className(), ['shop_id' => 'id']);
    }
}
