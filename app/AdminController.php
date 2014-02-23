<?php

namespace App;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminController
{
    /**
     * Perform user login
     *
     * @param Request $request
     * @param Application $app
     * @return mixed
     */
    public function login(Request $request, Application $app)
    {
        $username = $request->get('username');
        $password = $request->get('password');

        if ($app['auth']['user'] == $username && $app['auth']['pass'] == md5($password)) {
            $app['session']->set('user', array('username' => $username));
            return $app->redirect('/dashboard');
        }

        return $app->redirect('/admin');
    }

    /**
     * Login page
     *
     * @param Request $request
     * @param Application $app
     * @return mixed
     */
    public function admin(Request $request, Application $app)
    {
        return $app['twig']->render('login.twig');
    }

    /**
     * Logout action
     *
     * @param Request $request
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function logout(Request $request, Application $app)
    {
        $app['session']->set('user', null);

        return $app->redirect('/');
    }

    /**
     * Main admin page
     *
     * @param Request $request
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function dashboard(Request $request, Application $app)
    {
        $user = $app['session']->get('user');
        if (is_null($user)) {
            return $app->redirect('/admin');
        }

        return $app['twig']->render('dashboard.twig');
    }

}