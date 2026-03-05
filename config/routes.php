<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
use Cake\Routing\RouteBuilder;
use Cake\Routing\Route\DashedRoute;

/**
 * The default class to use for all routes
 *
 * The following route classes are supplied with CakePHP and are appropriate
 * to set as the default:
 *
 * - Route
 * - InflectedRoute
 * - DashedRoute
 *
 * If no call is made to `Router::defaultRouteClass()`, the class used is
 * `Route` (`Cake\Routing\Route\Route`)
 *
 * Note that `Route` does not do any inflections on URLs which will result in
 * inconsistently cased URLs when used with `:plugin`, `:controller` and
 * `:action` markers.
 *
 */
return function (RouteBuilder $routes): void {
    $routes->setRouteClass(DashedRoute::class);

    if (!is_app_installed()) {
        $routes->connect('/install/:action', ['controller' => 'Install']);
        if (strpos(env('REQUEST_URI') ?: '', 'install') === false) {
            $routes->redirect('/**', ['controller' => 'Install', 'action' => 'index'], ['status' => 307]);
            return;
        }
    }

    $routes->scope('/', function (RouteBuilder $routes): void {
    /*
    if (!is_app_installed()) {
        $request = Router::getRequest();

        if (strpos($request->url, 'install') === false) {
            $routes->connect('/install/:action', ['controller' => 'Install']);
            return $routes->redirect('/**', ['controller' => 'Install', 'action' => 'index'], ['status' => 307]);
        }
    }
    */
    
    /**
     * Here, we are connecting '/' (base path) to a controller called 'Pages',
     * its action called 'display', and we pass a param to select the view file
     * to use (in this case, src/Template/Pages/home.ctp)...
     */
    $routes->connect('/', ['controller' => 'Pages', 'action' => 'home']);

    $routes->connect('/st/', ['controller' => 'Links', 'action' => 'st']);

    $routes->connect('/api/', ['controller' => 'Links', 'action' => 'api']);

    $routes->connect('/advertising-rates', ['controller' => 'Pages', 'action' => 'view', 'advertising-rates']);

    $routes->connect('/payout-rates', ['controller' => 'Pages', 'action' => 'view', 'payout-rates']);
    
    $routes->connect('/pages/*', ['controller' => 'Pages', 'action' => 'view']);
    
    $routes->connect('/blog', ['controller' => 'Posts', 'action' => 'index']);
    
    $routes->connect('/blog/:id-:slug', ['controller' => 'Posts', 'action' => 'view'],
        ['pass' => ['id', 'slug'], 'id' => '[0-9]+']);
    
    $routes->connect('/ref/*', ['controller' => 'Users', 'action' => 'ref']);

    $routes->connect('/code/:alias', ['controller' => 'Links', 'action' => 'code'], ['pass' => ['alias']]);
    $routes->connect('/landing/:alias', ['controller' => 'Links', 'action' => 'landing'], ['pass' => ['alias']]);
    $routes->connect('/final-ad', ['controller' => 'Links', 'action' => 'finalAd']);

    $routes->connect('/:alias/info', [ 'controller' => 'Statistics', 'action' => 'view'], [ 'pass' => ['alias']]);
    $routes->connect('/:alias', [ 'controller' => 'Links', 'action' => 'view'], [ 'pass' => ['alias'], 'routeClass' => \App\Routing\Route\ShortLinkRoute::class]);

    /**
     * Connect catchall routes for all controllers.
     *
     * Using the argument `DashedRoute`, the `fallbacks` method is a shortcut for
     *    `$routes->connect('/:controller', ['action' => 'index'], ['routeClass' => 'DashedRoute']);`
     *    `$routes->connect('/:controller/:action/*', [], ['routeClass' => 'DashedRoute']);`
     *
     * Any route class can be used with this method, such as:
     * - DashedRoute
     * - InflectedRoute
     * - Route
     * - Or your own route class
     *
     * You can remove these routes once you've connected the
     * routes you want in your application.
     */
    $routes->fallbacks();
    });

    /**
     * Auth routes
     */
    $routes->prefix('auth', function (RouteBuilder $routes): void {
    // All routes here will be prefixed with ‘/auth‘
    // And have the prefix => auth route element added.
    $routes->connect('/signin', ['controller' => 'Users', 'action' => 'signin']);

    $routes->connect('/signup', ['controller' => 'Users', 'action' => 'signup']);

    $routes->connect('/logout', ['controller' => 'Users', 'action' => 'logout']);

    $routes->connect('/forgot-password', ['controller' => 'Users', 'action' => 'forgotPassword']);

    $routes->fallbacks();
    });

    /**
     * Member routes
     */
    $routes->prefix('member', function (RouteBuilder $routes): void {
    // All routes here will be prefixed with ‘/member‘
    // And have the prefix => member route element added.
    $routes->connect('/dashboard', ['controller' => 'Users', 'action' => 'dashboard']);

    $routes->fallbacks();
    });

    /**
     * Admin routes
     */
    $routes->prefix('admin', function (RouteBuilder $routes): void {
    // All routes here will be prefixed with ‘/admin‘
    // And have the prefix => admin route element added.
    $routes->connect('/dashboard', ['controller' => 'Users', 'action' => 'dashboard']);

    $routes->fallbacks();
    });
};
