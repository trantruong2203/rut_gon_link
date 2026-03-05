<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.3.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App;

use ADmad\SocialAuth\Middleware\SocialAuthMiddleware;
use Cake\Core\Configure;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;

/**
 * Application setup class.
 *
 * This defines the bootstrapping logic and middleware layers you
 * want to use in your application.
 */
class Application extends \Cake\Http\BaseApplication
{
    /**
     * Load all the application configuration and bootstrap logic.
     *
     * @return void
     */
    public function bootstrap(): void
    {
        parent::bootstrap();

        $this->addPlugin('Migrations');
        $this->addPlugin('ADmad/SocialAuth');

        $theme = function_exists('get_option') ? get_option('theme', 'ClassicTheme') : 'ClassicTheme';
        $theme = str_replace(' ', '', $theme);
        try {
            $this->addPlugin($theme, ['bootstrap' => true, 'routes' => true]);
        } catch (\Cake\Core\Exception\MissingPluginException $e) {
            // Theme plugin may not exist
        }
    }

    /**
     * Setup the middleware queue your application will use.
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to setup.
     * @return \Cake\Http\MiddlewareQueue The updated middleware queue.
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        $middlewareQueue
            ->add(new ErrorHandlerMiddleware(Configure::read('Error', []), $this))
            ->add(new AssetMiddleware([
                'cacheTime' => Configure::read('Asset.cacheTime'),
            ]))
            ->add(new RoutingMiddleware($this))
            ->add(new SocialAuthMiddleware($this->getSocialAuthConfig()))
            ->add(new BodyParserMiddleware())
            ->add((new CsrfProtectionMiddleware([
                'httponly' => true,
            ]))->skipCheckCallback(function ($request) {
                $path = $request->getPath();

                // Bỏ qua CSRF cho trình cài đặt
                if (strpos($path, '/install') === 0) {
                    return true;
                }

                // IPN thanh toán
                if ($path === '/member/campaigns/ipn') {
                    return true;
                }

                // Các endpoint Links sử dụng AJAX / redirect token,
                // không cần CSRF để tránh lỗi khi dùng từ domain/subdomain khác.
                if (preg_match('#^/links/(go|popad|shorten|code|landing|r|finalAd)$#i', $path)) {
                    return true;
                }

                return false;
            }));

        return $middlewareQueue;
    }

    /**
     * Get SocialAuth middleware configuration.
     *
     * @return array
     */
    protected function getSocialAuthConfig(): array
    {
        $serviceConfig = ['provider' => []];

        if (function_exists('get_option')) {
            if ((bool) get_option('social_login_facebook', false)) {
                $serviceConfig['provider']['facebook'] = [
                    'applicationId' => get_option('social_login_facebook_app_id'),
                    'applicationSecret' => get_option('social_login_facebook_app_secret'),
                    'scope' => ['email'],
                ];
            }
            if ((bool) get_option('social_login_google', false)) {
                $serviceConfig['provider']['google'] = [
                    'applicationId' => get_option('social_login_google_client_id'),
                    'applicationSecret' => get_option('social_login_google_client_secret'),
                    'scope' => [
                        'https://www.googleapis.com/auth/userinfo.email',
                        'https://www.googleapis.com/auth/userinfo.profile',
                    ],
                ];
            }
        }

        return [
            'requestMethod' => 'GET',
            'loginUrl' => '/auth/users/signin',
            'loginRedirect' => '/member/dashboard',
            'userModel' => 'Users',
            'profileModel' => 'ADmad/SocialAuth.SocialProfiles',
            'finder' => 'auth',
            'sessionKey' => 'Auth',
            'getUserCallback' => 'getUser',
            'serviceConfig' => $serviceConfig,
        ];
    }
}
