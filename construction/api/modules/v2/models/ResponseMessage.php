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
    protected $data;

    /**
     * Set text message
     */
    public function setMessage($status = 1, $data = '') {
        switch ($status) {
            case 0:
                $this->message = [
                    'method' => strtolower(Yii::$app->getRequest()->getMethod()),
                    'status' => $status,
                    'type' => 'success',
                    'message' => $data
                ];
                break;
            case 1:
                $this->message = [
                    'method' => strtolower(Yii::$app->getRequest()->getMethod()),
                    'status' => $status,
                    'type' => 'error',
                    'message' => $data
                ];
        }
    }

    /**
     * Set text message with data
     */
    public function saveDataMessage($data = '') {
        $this->setMessage(0, $data);
    }

    /**
     * Set text error message
     */
    public function saveErrorMessage($data = '') {
        $this->setMessage(1, $data);
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
