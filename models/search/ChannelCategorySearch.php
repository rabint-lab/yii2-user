<?php

namespace rabint\user\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use rabint\user\models\ChannelCategory;

/**
 * ChannelCategorySearch represents the model behind the search form about `rabint\user\models\ChannelCategory`.
 */
class ChannelCategorySearch extends ChannelCategory {

    public $user = null;

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
                [['id', 'user_id', 'parent_id', 'thumbnail_id', 'created_at'], 'integer'],
                [['title', 'slug', 'config', 'description', 'meta'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios() {
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
    public function search($params, $returnActiveQuery = FALSE) {
        $query = ChannelCategory::find()->alias('channelcategory');

        // add conditions that should always apply here
        $query->select(new \yii\db\Expression('*, IF(parent_id IS NULL,CONCAT(\'-\',id),CONCAT(\'-\',parent_id,\'-\',id)) as parent_order'));

        $query->orderBy(new \yii\db\Expression('parent_order asc ,id asc'));
//
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
//            'sort' => ['defaultOrder' => ['id' => SORT_ASC]]
        ]);

        $this->load($params);

        $this->user_id = $this->user;
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'user_id' => $this->user_id,
            'parent_id' => $this->parent_id,
            'thumbnail_id' => $this->thumbnail_id,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
                ->andFilterWhere(['like', 'slug', $this->slug])
                ->andFilterWhere(['like', 'config', $this->config])
                ->andFilterWhere(['like', 'description', $this->description])
                ->andFilterWhere(['like', 'meta', $this->meta]);

        if ($returnActiveQuery) {
            return $query;
        }
        return $dataProvider;
    }

}
