<?php

namespace App;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController
{
    /**
     * Register page
     *
     * @param Request $request
     * @param Application $app
     * @return mixed
     */
    public function signUp(Request $request, Application $app)
    {
        return $app['twig']->render('registration.twig', array());
    }

    /**
     * @param Request $request
     * @param Application $app
     * @return mixed
     */
    public function signIn(Request $request, Application $app)
    {
        return $app['twig']->render('registration.twig', array());
    }
}