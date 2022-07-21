<?php

namespace rabint\user\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use rabint\user\models\User;

/**
 * UserSearch represents the model behind the search form about `rabint\user\models\User`.
 */
class UserSearch extends User {

    public $order = 'latest';
    public $has_post = 0;
    public $keyword = NULL;

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
                [['id', 'created_at', 'updated_at', 'has_post'], 'integer'],
                [['keyword', 'username', 'order'], 'safe'],
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
     * @return ActiveDataProvider
     */
    public function search($params, $returnActiveQuery = FALSE) {
        $query = User::find()->alias('t');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
//            'sort' => ['defaultOrder' => ['is_official' => SORT_DESC]]
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
        //todo(1): add where for show only channels 
        $query->andFilterWhere([
            't.id' => $this->id,
            't.created_at' => $this->created_at,
            't.updated_at' => $this->updated_at,
//            't.is_official' => $this->is_official,
        ]);
        if ($this->has_post) {
            $publishedStatus = \app\modules\post\models\Post::STATUS_PUBLISH;
            $textFormat = \app\modules\post\models\Post::FORMAT_TEXT;
            $condition = <<<EOL
EXISTS (
       SELECT 1 from pst_post t2 
       WHERE (t.id = t2.user_id) 
              AND (t2.status = :publishedStatus)
              AND (t2.format != :textFormat)
)
EOL;
            $query->andWhere(new \yii\db\Expression($condition, ['publishedStatus' => $publishedStatus, 'textFormat' => $textFormat]));
        }

        if ($this->keyword) {

            $condition = "id in (SELECT user_id from user_profile  WHERE "
//                    . "firstname like '%:keyword%' or  "
//                    . "lastname like '%:keyword%' or  "
                    . "nickname like '%:keyword%'  "
                    . " )";

            $query->andWhere(new \yii\db\Expression($condition, ['keyword' => $this->keyword]));
        }

        $query->andFilterWhere(['like', 't.username', $this->username]);
        //todo(1): fill chanel order
        switch ($this->order) {
                    
            case 'recentlyUpdatedOfficial':
                $query->joinWith('posts');
                $query->groupBy('t.id');
                $query->select(new \yii\db\Expression('t.*,max(pst_post.created_at) as pst_max_update'));
                $query->orderBy('official_order DESC ,pst_max_update DESC');
                break;
            case 'recentlyUpdated':
                $query->joinWith('posts');
                $query->groupBy('t.id');
                $query->select('t.*,max(pst_post.created_at) as pst_max_update');
                $query->orderBy('pst_max_update DESC');
                break;
            case 'updated':
                $query->orderBy('t.updated_at DESC');
                break;
            case 'mostuser':
                $query->orderBy('t.updated_at DESC');
                break;
            case 'mostvisit':
                $query->orderBy('t.updated_at DESC');
                break;
            case 'earliest':
                $query->orderBy('t.updated_at DESC');
                break;
            case 'latest':
                $query->orderBy('t.created_at DESC');
            default:
                break;
        }
//        var_dump($query->createCommand()->rawSql);
//        die('-=---');
        if ($returnActiveQuery) {
            return $query;
        }
        return $dataProvider;
    }
    
    public static function searchFactory($params, $returnActiveQuery = FALSE, $shortParams = true)
    {
        $new = new self();
        if ($shortParams) {
            $modelName = basename(str_replace('\\', '/', self::class));
            $newParams = [$modelName => $params];
        }
        return $new->search($newParams, $returnActiveQuery);
    }
}
