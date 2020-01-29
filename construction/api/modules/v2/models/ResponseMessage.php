<?php
namespace api\modules\v2\models;

use Yii;
use yii\base\Model;
use yii\helpers\Json;

/**
 * Model return messages
 */
class ResponseMessage extends Model
{

    /**
     * properties
     */
    protected $message;

    /**
     * Set text message
     */
    public function setMessage($status = 1, $message = '') {
        switch ($status) {
            case 0:
                $this->message = [
                    'method' => strtolower(Yii::$app->getRequest()->getMethod()),
                    'status' => $status,
                    'type' => 'success',
                    'message' => $message
                ];
                break;
            case 1:
                $this->message = [
                    'method' => strtolower(Yii::$app->getRequest()->getMethod()),
                    'status' => $status,
                    'type' => 'error',
                    'message' => $message
                ];
        }
    }

    /**
     * Set text message with data
     */
    public function saveDataMessage($message = '') {
        $this->setMessage(0, $message);
    }

    /**
     * Set text error message
     */
    public static function saveErrorMessage($message = '') {
        $this->setMessage(1, $message);
    }

    /**
     * Get text message with data
     *
     */
    public static function getDataMessage()
    {
        return $this->message;
    }

    /**
     * Get text error message
     *
     */
    public function getErrorMessage()
    {
        return $this->message;
    }
}
