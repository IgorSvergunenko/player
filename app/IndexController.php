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
     * Get request with parameters
     *
     * @param Request $request
     * @param Application $app
     * @return mixed
     */
    public function parameters(Request $request, Application $app)
    {
        $link = $request->get('link');
        $statId = $request->get('statId');

        return $app['twig']->render(
            'parameters.twig',
            array(
                'link' => $link,
                'statId' => $statId
            )
        );

    }

    /**
     * Json response
     *
     * @param Request $request
     * @param Application $app
     * @return Response
     */
    public function ajaxTest(Request $request, Application $app)
    {
        $data = $request->get('data');

        return new Response(json_encode($data), 200);
    }
}