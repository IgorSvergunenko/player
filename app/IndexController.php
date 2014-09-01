<?php

namespace App;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\PlayList;
use App\Models\StatPageViews;

class IndexController
{
    /**
     * Main page
     *
     * @param Request $request
     * @param Application $app
     * @return mixed
     */
    public function index(Request $request, Application $app)
    {
        $playListModel = new PlayList($app['db']);
        $playLists = $playListModel->getAllPlayLists();

        return $app['twig']->render('index.twig', array(
            'playLists' => $playLists,
            'isAdmin' => isset($_COOKIE[$app['auth']['adminCookieName']]) ? true : false
        ));
    }

    /**
     * About page
     *
     * @param Request $request
     * @param Application $app
     * @return mixed
     */
    public function about(Request $request, Application $app)
    {
        return $app['twig']->render('about.twig');
    }

    /**
     * Contacts page
     *
     * @param Request $request
     * @param Application $app
     * @return mixed
     */
    public function contact(Request $request, Application $app)
    {
        return $app['twig']->render('contact.twig');
    }

    /**
     * Search songs in VK
     *
     * @param Request $request
     * @param Application $app
     * @return Response
     */
    public function search(Request $request, Application $app)
    {
        $searchValue = $request->get('searchValue');

        $stat = new StatPageViews($app['db']);
        $stat->saveSearchValue($app, $searchValue);

        try {
            $audio = new Audio($app);
            $searchResult = $audio->search($searchValue);
        } catch(\Exception $e) {
            $searchResult = $e->getMessage();
        }

        return new Response(
            json_encode($searchResult),
            200
        );
    }

    /**
     * Save play list to DB
     *
     * @param Request $request
     * @param Application $app
     * @return Response
     */
    public function saveList(Request $request, Application $app)
    {
        $playListName = $request->get('playListName');
        $userId = $request->get('userId', 1);
        $songs = $request->get('songs');

        $playListModel = new PlayList($app['db']);
        $playListModel->savePlayList($playListName, $songs);

        return new Response(
            json_encode(['success']),
            200
        );
    }

    /**
     * Get songs from play list
     *
     * @param Request $request
     * @param Application $app
     * @return Response
     */
    public function getList(Request $request, Application $app)
    {
        $playListId = $request->get('playListId');

        $playListModel = new PlayList($app['db']);
        $songs = $playListModel->getPlayListSongs($playListId);

        return new Response(
            json_encode($songs),
            200
        );
    }

    /**
     * @param Request $request
     * @param Application $app
     * @return Response
     */
    public function loadSong(Request $request, Application $app)
    {
        $url = $request->get('url');
        $name = $request->get('name');

        header("Content-type: application/x-file-to-save");
        header("Content-Disposition: attachment; filename=$name.mp3");
        readfile($url);

        return new Response(
            json_encode(''),
            200
        );
    }
}