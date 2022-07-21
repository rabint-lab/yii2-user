<?php

namespace rabint\user\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use rabint\user\models\UserFavorite;

/**
 * UserFavoriteSearch represents the model behind the search form about `rabint\user\models\UserFavorite`.
 */
class UserFavoriteSearch extends UserFavorite
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'created_at', 'object_id', 'priority'], 'integer'],
            [['object', 'thumbnail', 'title', 'link', 'meta'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
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
     * @param boolean $returnActiveQuery
     *
     * @return ActiveDataProvider OR ActiveQuery
     */
    public function search($params,$returnActiveQuery = FALSE)
    {
        $query = UserFavorite::find()->alias('userfavorite');

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
            'id' => $this->id,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            'object_id' => $this->object_id,
            'priority' => $this->priority,
        ]);

        $query->andFilterWhere(['like', 'object', $this->object])
            ->andFilterWhere(['like', 'thumbnail', $this->thumbnail])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'link', $this->link])
            ->andFilterWhere(['like', 'meta', $this->meta]);

        if ($returnActiveQuery) {
            return $query;
        }
        return $dataProvider;
    }
}
