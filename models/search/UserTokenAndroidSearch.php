<?php

namespace rabint\user\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use rabint\user\models\UserTokenAndroid;

/**
 * UserTokenAndroidSearch represents the model behind the search form about `\rabint\user\models\UserTokenAndroid`.
 */
class UserTokenAndroidSearch extends UserTokenAndroid
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'active_code', 'expire_at', 'created_at', 'updated_at'], 'integer'],
            [['token', 'android_id'], 'safe'],
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
        $query = UserTokenAndroid::find()->alias('usertokenandroid');

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
            'active_code' => $this->active_code,
            'expire_at' => $this->expire_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'token', $this->token])
            ->andFilterWhere(['like', 'android_id', $this->android_id]);

        if ($returnActiveQuery) {
            return $query;
        }
        return $dataProvider;
    }
}
