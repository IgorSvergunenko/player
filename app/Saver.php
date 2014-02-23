<?php
/**
 *
 * Date: 03.02.14
 * Time: 17:57
 */

namespace App;

require_once 'lib/classes/VkPhpSdk.php';

class Saver
{

    const PATH_TO_ALBUMS = '/../saved/';

    const USER_ALBUM_PATTERN = '/album';
    const GROUP_ALBUM_PATTERN = '/album-';
    const GROUP_ALBUM_PATTERN2 = 'z=album-';
    const USER_ALBUM_TYPE = 'uid';
    const GROUP_ALBUM_TYPE = 'gid';

    const ERROR_TYPE_EMPTY_URL = 'Ссылка не введена.';
    const ERROR_TYPE_WRONG_URL = 'Введена неправильная ссылка.';
    const ERROR_TYPE_PROTECTED = 'Альбом защищен настройками приватности.';

    /**
     * @var
     */
    private $vk;

    /**
     * @var
     */
    private $albumID;

    /**
     * @var
     */
    private $albumName;

    /**
     * @var
     */
    private $albumUrl;

    /**
     * @var
     */
    private $albumType;

    /**
     * @var
     */
    private $ownerID;

    public function __construct()
    {
        $this->vk = new \VkPhpSdk();
    }

    /**
     * @param $url
     * @return $this
     * @throws \Exception
     */
    public function process($url) {

        $this->albumUrl = $url;

        // check is valid url
        $validationData = $this->validateUrl($url);

        if (!$validationData['status']) {
            throw new \Exception($validationData['message']);
        }

        // get data from url
        list($this->albumType, $this->ownerID, $this->albumID) = $this->parseUrl($url);

        return $this;
    }

    /**
     * @return array
     */
    public function getPhotos() {

        $this->albumName = 'album_' . $this->ownerID . '-' . $this->albumID;

        $albumData = $this->vk->api('photos.get', array(
                $this->albumType => $this->ownerID,
                'aid' => $this->albumID
            )
        );

        if (empty($albumData) || empty($albumData['response']) || !is_array($albumData['response'])) {
            return array(
                'status' => false,
                'message' => 'Empty response',
                'albumUrl' => $this->albumUrl,
                'albumName' => $this->albumName,
                'photos' => array(),
                'photosCount' => 0
            );
        }

        $photos = array();
        foreach($albumData['response'] as $pictureData) {

            // Get type of the photo
            $photo = '';
            if(isset($pictureData['src_xxbig'])) {
                $photo = $pictureData['src_xxbig'];
            } elseif (isset($pictureData['src_xbig'])) {
                $photo = $pictureData['src_xbig'];
            } elseif (isset($pictureData['src_big'])) {
                $photo = $pictureData['src_big'];
            }

            if (!empty($photo)) {
                array_push($photos, $photo);
            }
        }

        if (empty($photos)) {
            return array(
                'status' => false,
                'message' => 'No photos in album',
                'albumUrl' => $this->albumUrl,
                'albumName' => $this->albumName,
                'photos' => array(),
                'photosCount' => 0
            );
        }

        return array(
            'status' => true,
            'message' => 'success',
            'albumUrl' => $this->albumUrl,
            'albumName' => $this->albumName,
            'photosCount' => count($photos),
            'photos' => array_chunk($photos, 10, true),
        );
    }

    /**
     * @param $photos
     * @param $albumName
     * @return string
     */
    public function savePhotos($photos, $albumName) {

        $dirPath = WEB_PATH . self::PATH_TO_ALBUMS . $albumName;

        if (!is_dir($dirPath)) {
            mkdir($dirPath);
        }

        foreach($photos as $key => $photo) {

            $picName = $key+1 . '.jpg';

            $file = file_get_contents($photo);
            $fp = fopen($dirPath . DIRECTORY_SEPARATOR . $picName, "a+");
            fwrite($fp, $file);
            fclose($fp);
        }

        return $dirPath;
    }

    /**
     * @param $dirPath
     * @param $fileName
     * @return mixed
     */
    public function zipAlbum($dirPath, $fileName) {

        $zip = new \ZipArchive;
        $link = $dirPath . '/../' . $fileName . '.zip';

        $zip->open($link, \ZipArchive::CREATE);

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dirPath),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {

            $filePath = $file->getRealPath();

            if (is_dir($filePath)) {
                continue;
            }

            // Add current file to archive
            $zip->addFromString(basename($filePath),  file_get_contents($filePath));
        }

        // Zip archive will be created only after closing object
        $zip->close();

        // Remove saved photos
        $objects = scandir($dirPath);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dirPath."/".$object) == "dir") rmdir($dirPath."/".$object); else unlink($dirPath."/".$object);
            }
        }
        reset($objects);
        rmdir($dirPath);

        return $link;
    }

    /**
     * Validate url string
     *
     * @param $url
     * @return array
     */
    public function validateUrl($url) {

        if (empty($url)) {
            return array('status' => false, 'message' => self::ERROR_TYPE_EMPTY_URL);
        }

        $data = parse_url($url);

        if (!isset($data['path'])) {
            return array('status' => false, 'message' => self::ERROR_TYPE_WRONG_URL);
        }

        $pos = strpos($url, 'album');

        if (!$pos) {
            return array('status' => false, 'message' => self::ERROR_TYPE_WRONG_URL);
        }

        $pos = strpos($url, 'z=album');
        $pos2 = strpos($url, '/album');

        if (!$pos && !$pos2) {
            return array('status' => false, 'message' => self::ERROR_TYPE_WRONG_URL);
        }

        $pos = strpos($url, '_');
        if (!$pos) {
            return array('status' => false, 'message' => self::ERROR_TYPE_WRONG_URL);
        }

        list($str, $albumID) = explode('_', $url);

        if (empty($str) || empty($albumID)) {
            return array('status' => false, 'message' => self::ERROR_TYPE_WRONG_URL);
        }

        return array('status' => true);
    }

    /**
     * Parse url and get albumId, ownerId and type
     *
     * @param $url
     * @return array
     */
    public function parseUrl($url) {

        // Check is album pattern in url
        $pos = strpos($url, self::GROUP_ALBUM_PATTERN);
        $pos2 = strpos($url, self::GROUP_ALBUM_PATTERN2);

        if ($pos || $pos2) {

            // Group album
            $type = self::GROUP_ALBUM_TYPE;
            $dataStr = explode('-', $url);
            list($userID, $albumID)  = explode('_', $dataStr[1]);

        } else {

            // User album
            $type = self::USER_ALBUM_TYPE;
            list($urlData, $albumData) = explode('album', $url);
            list($userID, $albumID) = explode('_', $albumData);
        }

        return array($type, $userID, $albumID);
    }

    /**
     * @return string
     */
    public function getAlbumName() {
        return $this->albumName;
    }
}