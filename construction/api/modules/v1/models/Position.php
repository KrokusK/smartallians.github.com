<?php
namespace frontend\modules\v1\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "position".
 *
 */
class Position extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%position}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {

        return [
            [['portfolio_id', 'description'], 'required', 'message' => 'Поле должно быть заполнено'],
            [['id'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле id  должно быть типа integer', 'skipOnEmpty' => true],
            [['portfolio_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $statusesPortfolioId = Portfolio::find()->select(['id'])->asArray()->all();
                    $statusesPortfolioIdStr = [];
                    foreach ($statusesPortfolioId as $item) {
                        array_push($statusesPortfolioIdStr, "{$item['id']}");
                    }
                    return $statusesPortfolioIdStr;
                },
                'message' => 'Портфолио не выбрано из списка'],
            [['description'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
        ];

    }

    /**
     *
     * Link to table portfolio
     */
    public function getPortfolios()
    {
        return $this->hasOne(Portfolio::className(), ['id' => 'portfolio_id']);
    }

    /**
     *
     * Link to table photo
     */
    public function getPhotos()
    {
        return $this->hasMany(Photo::className(), ['position_id' => 'id']);
    }
}
