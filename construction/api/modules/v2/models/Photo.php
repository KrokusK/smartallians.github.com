<?php
namespace api\modules\v2\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "photo".
 *
 */
class Photo extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 1;
    const STATUS_ACTIVE = 2;

    /**
     * @var UploadedFile
     */
    public $imageFiles;
    public $image_src_filename;
    public $image_web_filename;
    public $arrayWebFilename;
    public $msg;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%photo}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {

        return [
            [['description', 'caption', 'path', 'imageFiles', 'created_by'], 'required', 'message' => 'Поле должно быть заполнено'],
            [['id'], 'match', 'pattern' => '/^[0-9]*$/', 'message' => 'поле должно быть типа integer', 'skipOnEmpty' => true],
            [['response_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $ResponseId = Response::find()->select(['id'])->asArray()->all();
                    $ResponseIdStr = [];
                    foreach ($ResponseId as $item) {
                        array_push($ResponseIdStr, "{$item['id']}");
                    }
                    return $ResponseIdStr;
                },
                'message' => 'Отклик не выбран из списка', 'skipOnEmpty' => true],
            [['request_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $statusesRequestId = Request::find()->select(['id'])->asArray()->all();
                    $statusesRequestIdStr = [];
                    foreach ($statusesRequestId as $item) {
                        array_push($statusesRequestIdStr, "{$item['id']}");
                    }
                    return $statusesRequestIdStr;
                },
                'message' => 'Заявка не выбрана из списка', 'skipOnEmpty' => true],
            [['position_id'], 'in', 'range' =>
                function ( $attribute, $params ) {
                    $statusesPositionId = Request::find()->select(['id'])->asArray()->all();
                    $statusesPositionIdStr = [];
                    foreach ($statusesPositionId as $item) {
                        array_push($statusesPositionIdStr, "{$item['id']}");
                    }
                    return $statusesPositionIdStr;
                },
                'message' => 'Позиция не выбрана из списка', 'skipOnEmpty' => true],
            [['description'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
            [['caption'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
            [['path'], 'string', 'max' => 255, 'message' => 'Число знаков не должно превышать 255'],
            [['imageFiles'], 'file', 'maxFiles' => 5,'mimeTypes' => ['image/gif', 'image/jpeg', 'image/pjpeg', 'image/png'], 'extensions' => ['gif', 'jpg', 'jpeg', 'png'], 'maxSize' => 30*1024*1024, 'message' => 'Файл не соответствует требованиям'],
        ];

    }

    /**
     * upload ad photos to the server
     */
    public function upload()
    {
        if ($this->validate()) {
            $this->arrayWebFilename = array();

            // get images for each photo and save to the server as random filenames
            foreach ($this->imageFiles as $image) {
                if (!empty($image) && $image->size !== 0) {
                    $this->image_src_filename = $image->name;
                    $tmp = explode(".", $image->name);
                    $ext = end($tmp);
                    // generate a unique file name to prevent duplicate filenames
                    $this->image_web_filename = Yii::$app->security->generateRandomString() . ".{$ext}";
                    array_push($this->arrayWebFilename, "{$this->image_web_filename}");
                    // the path to save file, you can set an uploadPath
                    // in Yii::$app->params (as used in example below)
                    Yii::$app->params['uploadPath'] = Yii::$app->basePath . '/web/uploads/photo/';
                    $path = Yii::$app->params['uploadPath'] . $this->image_web_filename;
                    $image->saveAs($path);
                } else {
                    $this->msg = 'Файл не был загружен';
                    return false;
                }
            }
            return true;
        } else {
            $this->msg = 'Файл не прошел валидацию';
            return false;
        }
    }

    /**
     *
     * Link to table position
     */
    public function getPositions()
    {
        return $this->hasOne(Position::className(), ['id' => 'position_id']);
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
     * Link to table request
     */
    public function getRequests()
    {
        return $this->hasOne(Request::className(), ['id' => 'request_id']);
    }
}
