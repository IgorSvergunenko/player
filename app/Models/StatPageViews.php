<?php
/**
 *
 * Date: 03.02.14
 * Time: 17:57
 */

namespace App\Models;

require_once '/../lib/geo.php';
require_once '/../lib/browser.php';

class StatPageViews
{
    /**
     * @var
     */
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Track music search
     *
     * @param $app
     * @param $searchValue
     * @return string
     */
    public function saveSearchValue($app, $searchValue)
    {
        $isTrackStat = $this->isTrackStat($app);

        if (!$isTrackStat) {
            return '';
        }

        $userInfo = $this->getUserInfo();

        $this->db->insert('stat_search', [
            'date' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'userInfo' => json_encode($userInfo),
            'searchValue' => $searchValue,
        ]);

        $searchId = $this->db->lastInsertId();

        return $searchId;
    }

    /**
     * Track page views
     *
     * @param $app
     * @return string
     */
    public function savePageViews($app)
    {
        $page = ltrim($_SERVER['REQUEST_URI'], '/') == '' ? 'index' : ltrim($_SERVER['REQUEST_URI'], '/');
        $isTrackStat = $this->isTrackStat($app, $page);

        if (!$isTrackStat) {
            return '';
        }

        $userInfo = $this->getUserInfo();

        $this->db->insert('stat_pageviews', [
            'date' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'userInfo' => json_encode($userInfo),
            'page' => $page,
        ]);

        $pageViewsId = $this->db->lastInsertId();

        return $pageViewsId;
    }

    /**
     * @param $date
     * @return array
     */
    public function getSearched($date)
    {
        $sql = "SELECT * FROM stat_search WHERE DATE(`date`) = ? ORDER BY id DESC";
        $visitors = $this->db->fetchAll($sql, array($date));

        $response = [];

        if (!empty($visitors)) {
            foreach ($visitors as $key => $visitor) {
                $userInfo = json_decode($visitor['userInfo'], true);
                $country = !empty($userInfo['country']) ? $userInfo['country'] : 'unknown';
                $city = !empty($userInfo['city']) ? $userInfo['city'] : 'unknown';

                $record = array(
                    "search" => ($visitor['searchValue'] == '') ? '' : $visitor['searchValue'],
                    "date" => $visitor['date'],
                    "ip" => $visitor['ip'],
                    "city" => $country . " (" . $city . ")"
                );
                array_push($response, $record);
            }
        }

        return $response;
    }

    /**
     * @param $date
     * @return array
     */
    public function getPageView($date)
    {
        $sql = "SELECT * FROM stat_pageviews WHERE DATE(`date`) = ? ORDER BY id DESC";
        $visitors = $this->db->fetchAll($sql, array($date));

        $response = [];

        if (!empty($visitors)) {
            foreach ($visitors as $key => $visitor) {
                $userInfo = json_decode($visitor['userInfo'], true);
                $country = !empty($userInfo['country']) ? $userInfo['country'] : 'unknown';
                $city = !empty($userInfo['city']) ? $userInfo['city'] : 'unknown';

                $record = array(
                    "page" => ($visitor['page'] == '') ? 'index' : $visitor['page'],
                    "date" => $visitor['date'],
                    "ip" => $visitor['ip'],
                    "city" => $country . " (" . $city . ")"
                );
                array_push($response, $record);
            }
        }

        return $response;
    }

    /**
     * @param $app
     * @param $selectedPage
     * @return bool
     */
    public function isTrackStat($app, $selectedPage = '')
    {
        $trackStat = true;

        $botMatches = preg_match('/bot|crawl|slurp|spider/i', strtolower($_SERVER['HTTP_USER_AGENT']));

        if ($botMatches !== 0) {
            $trackStat = false;
        }

        $excludeIps = $app['excludeIpStat'];
        foreach($excludeIps as $ip) {
            if ($_SERVER['REMOTE_ADDR'] == $ip) {
                $trackStat = false;
                break;
            }
        }

        if (!empty($selectedPage)) {
            $excludePages = $app['excludePagesStat'];
            foreach($excludePages as $page) {
                if ($selectedPage == $page) {
                    $trackStat = false;
                    break;
                }
            }
        }

        return $trackStat;
    }

    /**
     * @return array
     */
    public function getUserInfo()
    {
        $data = [];

        /*IP staff*/
        $o = array();
        $o['charset'] = 'utf-8';
        $geo = new \Geo($o);
        $geoData = $geo->get_value();

        if (!empty($geoData['country'])) {
            $data['country'] = $geoData['country'];
        }
        if (!empty($geoData['city'])) {
            $data['city'] = $geoData['city'];
        }

        /*Browser staff*/
        $ua = getBrowser();
        if (!empty($ua['name'])) {
            $data['browser'] = $ua['name'];
        }
        if (!empty($ua['version'])) {
            $data['browser_version'] = $ua['version'];
        }
        if (!empty($ua['platform'])) {
            $data['user_platform'] = $ua['platform'];
        }

        return $data;
    }
}