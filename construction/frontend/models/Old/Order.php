<?php
namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "order".
 *
 */
class Order extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {

        return [
            [['request_id','response_id'], 'required', 'message' => 'Поле должно быть заполнено'],
            [['id'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле id  должно быть типа integer', 'skipOnEmpty' => true],
            [['request_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $requestId = Request::find()->select(['id'])->asArray()->all();
                    $requestIdStr = [];
                    foreach ($requestId as $item) {
                        array_push($requestIdStr, "{$item['id']}");
                    }
                    return $requestIdStr;
                },
                'message' => 'Заявка не выбрана из списка'],
            [['response_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $responseId = Response::find()->select(['id'])->asArray()->all();
                    $responseIdStr = [];
                    foreach ($responseId as $item) {
                        array_push($responseIdStr, "{$item['id']}");
                    }
                    return $responseIdStr;
                },
                'message' => 'Отклик не выбран из списка'],
            [['status_payment_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $statusPaymentId = StatusPayment::find()->select(['id'])->asArray()->all();
                    $statusPaymentIdStr = [];
                    foreach ($statusPaymentId as $item) {
                        array_push($statusPaymentIdStr, "{$item['id']}");
                    }
                    return $statusPaymentIdStr;
                },
                'message' => 'Статус оплаты не выбран из списка'],
            [['status_completion_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $statusCompletionId = StatusCompletion::find()->select(['id'])->asArray()->all();
                    $statusCompletionIdStr = [];
                    foreach ($statusCompletionId as $item) {
                        array_push($statusCompletionIdStr, "{$item['id']}");
                    }
                    return $statusCompletionIdStr;
                },
                'message' => 'Статус завершения не выбран из списка'],
            [['project_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $projectId = Project::find()->select(['id'])->asArray()->all();
                    $projectIdStr = [];
                    foreach ($projectId as $item) {
                        array_push($projectIdStr, "{$item['id']}");
                    }
                    return $projectIdStr;
                },
                'message' => 'Проект не выбрана из списка'],
            [['feedback_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $feedbackId = Feedback::find()->select(['id'])->asArray()->all();
                    $feedbackIdStr = [];
                    foreach ($feedbackId as $item) {
                        array_push($feedbackIdStr, "{$item['id']}");
                    }
                    return $feedbackIdStr;
                },
                'message' => 'Отзыв не выбрана из списка'],
        ];

    }

    /**
     *
     * Link to table request
     */
    public function getRequests()
    {
        return $this->hasOne(Request::className(), ['id' => 'request_id']);
    }

    /**
     *
     * Link to table response
     */
    public function getResponses()
    {
        return $this->hasOne(Response::className(), ['id' => 'response_id']);
    }

    /**
     *
     * Link to table status_payment
     */
    public function getStatusPayment()
    {
        return $this->hasOne(StatusPayment::className(), ['id' => 'status_payment_id']);
    }

    /**
     *
     * Link to table status_completion
     */
    public function getStatusCompletion()
    {
        return $this->hasOne(StatusCompletion::className(), ['id' => 'status_completion_id']);
    }

    /**
     *
     * Link to table project
     */
    public function getProjects()
    {
        return $this->hasOne(Project::className(), ['id' => 'project_id']);
    }

    /**
     *
     * Link to table feedback
     */
    public function getFeedbacks()
    {
        return $this->hasOne(Feedback::className(), ['id' => 'feedback_id']);
    }

    /**
     *
     * Link to table profile
     */
    public function getProfiles()
    {
        return $this->hasMany(Profile::className(), ['id' => 'profile_id'])
            ->viaTable('profile_rrod', ['order_id' => 'id']);
    }
}
