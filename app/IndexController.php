<?php

namespace App;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        return $app['twig']->render('index.twig');
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
     * Json response
     *
     * @param Request $request
     * @param Application $app
     * @return Response
     */
    public function search(Request $request, Application $app)
    {
        $searchValue = $request->get('searchValue');

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

}