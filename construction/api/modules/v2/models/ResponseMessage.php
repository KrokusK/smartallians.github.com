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
                    'status' => 0,
                    'type' => 'success',
                    'data' => $message
                ];
                break;
            case 1:
                $this->message = [
                    'method' => strtolower(Yii::$app->getRequest()->getMethod()),
                    'status' => 0,
                    'type' => 'success',
                    '0' => $message
                ];
                break;
            case 2:
                $this->message = [
                    'method' => strtolower(Yii::$app->getRequest()->getMethod()),
                    'status' => 0,
                    'type' => 'success',
                    'message' => $message
                ];
                break;
            case 3:
                $this->message = [
                    'method' => strtolower(Yii::$app->getRequest()->getMethod()),
                    'status' => 1,
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
     * Set text message in array format
     */
    public function saveArrayMessage($message = '') {
        $this->setMessage(1, $message);
    }

    /**
     * Set text message
     */
    public function saveSuccessMessage($message = '') {
        $this->setMessage(2, $message);
    }

    /**
     * Set text error message
     */
    public function saveErrorMessage($message = '') {
        $this->setMessage(3, $message);
    }

    /**
     * Get text message with data
     *
     */
    public function getDataMessage()
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
