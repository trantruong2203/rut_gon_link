<?php

namespace App\Routing\Route;

use Cake\ORM\TableRegistry;
use Cake\Routing\Route\Route as CakeRoute;

class ShortLinkRoute extends CakeRoute
{
    /**
     * @inheritDoc
     */
    public function parse(string $url, string $method = ''): ?array
    {
        $route = parent::parse($url, $method);
        if (empty($route)) {
            return null;
        }

        if (!database_connect()) {
            return null;
        }

        try {
            $alias = $route['pass'][0] ?? null;
            if ($alias === null) {
                return null;
            }

            $Links = TableRegistry::getTableLocator()->get('Links');
            $count = $Links->find('all')
                ->where(['alias' => $alias])
                ->count();
            if ($count) {
                return $route;
            }
        } catch (\Exception $ex) {
        }

        return null;
    }
}
