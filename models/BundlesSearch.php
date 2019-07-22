<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Bundles;

/**
 * BundlesSearch represents the model behind the search form of `app\models\Bundles`.
 */
class BundlesSearch extends Bundles
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['shopify_id', 'discrepancies', 'deleted', 'shop_id'], 'integer'],
            [['title', 'description', 'image', 'created_at', 'updated_at'], 'safe'],
            [['price'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Bundles::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'shopify_id' => $this->shopify_id,
            'price' => $this->price,
            'discrepancies' => $this->discrepancies,
            'deleted' => $this->deleted,
            'shop_id' => $this->shop_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'image', $this->image]);

        return $dataProvider;
    }
}
