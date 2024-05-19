<?php

namespace App\Services\Notification;

use App\Helpers\Constants;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class FcmService implements NotificationInterface
{
    //protected $firebase_api_key = 'AIzaSyC_BrZOEPLi_SKLaln30Y-nKNPec_Ob1AU';
    protected $firebase_api_key = 'AAAAxqBmyrw:APA91bFkoU2cY45ugV5GtYKfJ7CJYEzbwsoplOtiKa-0YAnuYmAxiuyU8toOfxZ8WPqZusDe_YZDgAsi9pq3gQtnXgne7PAVqNY5TB1AoFpp2bruZphMYDpDHUbnuekFL8KLjVDq6fHa';
    protected $firebase_api_key_admin = '';
    protected $fcm_send_url = 'https://fcm.googleapis.com/fcm/send';

    /**
     * @param $deviceTokens
     * @param $data
     * @throws GuzzleException
     */
    public function sendBatchNotification($deviceTokens, $data)
    {
        // TODO: Implement sendBatchNotification() method.

        self::subscribeTopic($deviceTokens, $data['topicName']);
        self::sendNotification($data, $data['topicName']);
        self::unsubscribeTopic($deviceTokens, $data['topicName']);
    }

    /**
     * @param $data
     * @param $topicName
     * @throws GuzzleException
     */
    public function sendNotification($data, $topicName = null)
    {
        // TODO: Implement sendNotification() method.

        $url = 'https://fcm.googleapis.com/fcm/send';
        $data_push = [
            'to' => $topicName,
            'notification' => [
                'body' => isset($data['body']) ? $data['body'] : 'Something',
                'title' => isset($data['title']) ? $data['title'] . time() : 'Something',
                'image' => isset($data['image']) ? $data['image'] : null,
            ],
            'data' => [
                'url' => isset($data['url']) ? $data['url'] : null,
                'redirect_to' => isset($data['redirect_to']) ? $data['redirect_to'] : null,
            ],
            'apns' => [
                'payload' => [
                    'aps' => [
                        'mutable-content' => 1,
                    ],
                ],
                'fcm_options' => [
                    'image' => isset($data['image']) ? $data['image'] : null,
                ],
            ],
        ];

        $this->execute($url, $data_push);
    }

    /**
     * @param $deviceTokens
     * @param $topicName
     * @throws GuzzleException
     */
    public function subscribeTopic($deviceTokens, $topicName)
    {
        // TODO: Implement subscribeTopic() method.

        $url = 'https://iid.googleapis.com/iid/v1:batchAdd';
        $data = [
            'to' => '/topics/' . $topicName,
            'registration_tokens' => $deviceTokens,
        ];

        $this->execute($url, $data);
    }

    /**
     * @param $deviceTokens
     * @param $topicName
     * @throws GuzzleException
     */
    public function unsubscribeTopic($deviceTokens, $topicName)
    {
        // TODO: Implement unsubscribeTopic() method.

        $url = 'https://iid.googleapis.com/iid/v1:batchRemove';
        $data = [
            'to' => '/topics/' . $topicName,
            'registration_tokens' => $deviceTokens,
        ];

        $this->execute($url, $data);
    }

    /**
     * @param $url
     * @param array $dataPost
     * @param string $method
     * @return bool
     * @throws GuzzleException
     */
    private function execute($url, $dataPost = [], $method = 'POST')
    {
        $result = false;
        try {
            $client = new Client();
            $result = $client->request($method, $url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'key=' . $this->firebase_api_key,
                ],
                'json' => $dataPost,
                'timeout' => 300,
            ]);

            $result = $result->getStatusCode() == Response::HTTP_OK;
        } catch (\Exception $e) {
            Log::debug($e);
        }

        return $result;
    }

    /**
     * Hàm bắn noti cho nhiều người với cùng nội dung
     *
     * @param $data
     * @param $registration_ids
     * @param string $platform
     *
     * @return bool|string
     */
    public function multiplePusher($receiver_type, $data, $registration_ids, $platform = 'Android')
    {
        if ($receiver_type && is_array($registration_ids) && count($registration_ids) > 0) {
            $data_push = [
                'registration_ids' => $registration_ids,
                'notification' => [
                    'title' => isset($data['title']) ? $data['title'] : 'SSC-EDU',
                    'body' => isset($data['body']) ? $data['body'] : 'SSC-EDU thân chào quý khách',
                    'image' => isset($data['image']) ? $data['image'] : null
                ],
                'data' => [
                    'title' => isset($data['title']) ? $data['title'] : 'SSC-EDU',
                    'body' => isset($data['body']) ? $data['body'] : 'SSC-EDU thân chào quý khách',
                    'image' => isset($data['image']) ? $data['image'] : null,
                    'action_type' => isset($data['action_type']) ? $data['action_type'] : null,
                    'record_id' => isset($data['record_id']) ? $data['record_id'] : null,
                    'redirect_to' => isset($data['redirect_to']) ? $data['redirect_to'] : null
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'mutable-content' => 1,
                        ],
                    ],
                    'fcm_options' => [
                        'image' => isset($data['image']) ? $data['image'] : null,
                    ],
                ],
            ];

            return $this->executePush($receiver_type, $data_push);

        } else {
            return  false;
        }
    }

    /**
     * Hàm CURL đến firebase để gửi tin nhắn
     *
     * @param $data_push
     * @return bool|string
     */
    private function executePush($receiver_type=Constants::ACCOUNT_TYPE_CUSTOMER, $data_push)
    {
        $key = ($receiver_type == Constants::ACCOUNT_TYPE_CUSTOMER) ? $this->firebase_api_key : $this->firebase_api_key_admin;

        $headers = array(
            'Authorization: key=' . $key,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->fcm_send_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_push));
        $result = curl_exec($ch);
        curl_close($ch);
        //
        return $result;
    }
}
