<?php
/**
 *
 * Date: 03.02.14
 * Time: 17:57
 */

namespace App;

require_once 'lib/classes/Oauth2Proxy.php';
require_once 'lib/classes/VkPhpSdk.php';

class Audio
{
    /**
     * @var
     */
    private $vk;

    private $api_id;

    private $secret_key;

    private $access_token;

    public function __construct($app)
    {
        $this->api_id = $app['vk']['id'];
        $this->secret_key = $app['vk']['key'];
        $this->access_token = $app['vk']['accessToken'];

        $this->vk = new \VkPhpSdk();
        $this->vk->setAccessToken($this->access_token);
    }

    /**
     * @param $searchText
     * @return mixed
     */
    public function search($searchText) {

        $resp = $this->vk->api(
            'audio.search',
            array(
                'q'=>$searchText,
                'auto_complete'=>'1',
                'sort'=>'2',
                'count'=>'25'
            )
        );

        $songs = array();

        if (!empty($resp['response'])) {
            unset($resp['response'][0]);
            foreach ($resp['response'] as $song) {
                array_push(
                    $songs,
                    array(
                        'artist' => $song['artist'],
                        'title' => $song['title'],
                        'url' => $song['url']
                    )
                );
            }
        }

        return $songs;
    }

}
