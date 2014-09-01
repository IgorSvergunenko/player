<?php

namespace App;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\StatPageViews;

class AdminController
{
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
            return $app->redirect('/pageviews');
        }

        return $app->redirect('/admin');
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
     * Searched artists and songs stat page
     *
     * @param Request $request
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function searched(Request $request, Application $app)
    {
        $user = $app['session']->get('user');
        if (is_null($user)) {
            return $app->redirect('/admin');
        }

        $date = $request->get('date', date('Y-m-d'));

        $stat = new StatPageViews($app['db']);
        $visitors = $stat->getSearched($date);
        $result = json_encode($visitors);

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            return $result;
        }

        return $app['twig']->render(
            'searched.twig',
            array(
                'visitors' => $result,
                'date' => $date,
            )
        );
    }

    /**
     * Pageviews stat page
     *
     * @param Request $request
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function pageviews(Request $request, Application $app)
    {
        $user = $app['session']->get('user');
        if (is_null($user)) {
            return $app->redirect('/admin');
        }

        $date = $request->get('date', date('Y-m-d'));

        $stat = new StatPageViews($app['db']);
        $visitors = $stat->getPageView($date);
        $result = json_encode($visitors);

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            return $result;
        }

        return $app['twig']->render(
            'pageviews.twig',
            array(
                'visitors' => $result,
                'date' => $date,
            )
        );
    }

    /**
     * Ban for users
     *
     * @param Request $request
     * @param Application $app
     * @return Response
     */
    public function ban(Request $request, Application $app)
    {
        $ip = $request->get('ip');

        $fileName = '.htaccess';

        $htaccessDir = WEB_PATH . "/../" . $fileName;
        $fp = fopen($htaccessDir, 'a');

        $write = fwrite($fp, ' ' . $ip);
        if ($write)
            $response = 'Data ' . $ip . ' successfully written to a file.';
        else
            $response = 'Error!!!';

        fclose($fp);

        return new Response(json_encode($response), 200);
    }
}