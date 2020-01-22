<?php
namespace api\modules\v2\models;

use Yii;
use yii\base\Model;

/**
 * Passport
 */
class ProfilePassport extends Model
{
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
    public function rules()
    {

        return [
            [['imageFiles'], 'required', 'message' => 'Поле должно быть заполнено'],
            [['imageFiles'], 'file', 'maxFiles' => 2,'mimeTypes' => ['image/jpeg', 'image/pjpeg', 'image/png'], 'extensions' => ['jpg', 'jpeg', 'png'], 'maxSize' => 1.5*1024*1024, 'message' => 'Файл не соответствует требованиям'],
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
                    Yii::$app->params['uploadPath'] = Yii::$app->basePath . '/web/uploads/passport/';
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
}
