<?php

namespace App\Controller\Admin;

use App\Controller\Admin\AppAdminController;

class ReportsController extends AppAdminController
{
    public function campaigns()
    {
        $this->loadModel('Users');
        
        $plain_users = $this->Users->find('list', [
                'keyField' => 'id',
                'valueField' => 'username'
            ])
            ->toArray();
        
        $users = [];
        foreach ($plain_users as $key => $value) {
            $users[$key] = '#'.$key.' - '.$value;
        }
        
        $this->set('users', $users);
        
        $plain_campaigns = $this->Users->Campaigns->find('list', [
                'keyField' => 'id',
                'valueField' => 'name'
            ])
            ->toArray();
        
        $campaigns = [];
        foreach ($plain_campaigns as $key => $value) {
            $campaigns[$key] = '#'.$key.' - '.$value;
        }
        
        $this->set('campaigns', $campaigns);
        
        if ($this->request->getQuery('Filter') !== null) {
            
            $campaign_where = [];
            $filter = $this->request->getQuery('Filter');
            
            if( !empty($filter['campaign_id']) ) {
                $campaign_where['campaign_id'] = (int) $filter['campaign_id'];
            }
            
            if( !empty($filter['user_id']) ) {
                $campaign_where['user_id'] = (int) $filter['user_id'];
            }
            
            $campaign_earnings = $this->Users->Statistics->find()
                ->select([
                    'reason',
                    'count' => 'COUNT(reason)',
                    'earnings' => 'SUM(publisher_earn)',
                ])
                ->where($campaign_where)
                ->order(['earnings' => 'DESC'])
                ->group(['reason'])
                ->toArray();

            $this->set('campaign_earnings', $campaign_earnings);

            $campaign_countries = $this->Users->Statistics->find()
                ->select([
                    'country',
                    'count' => 'COUNT(country)',
                    'earnings' => 'SUM(publisher_earn)',
                ])
                ->where($campaign_where)
                ->order(['earnings' => 'DESC'])
                ->group(['country'])
                ->toArray();

            $this->set('campaign_countries', $campaign_countries);

            $campaign_referers = $this->Users->Statistics->find()
                ->select([
                    'referer_domain',
                    'count' => 'COUNT(referer_domain)',
                    'earnings' => 'SUM(publisher_earn)',
                ])
                ->where($campaign_where)
                ->order(['earnings' => 'DESC'])
                ->group(['referer_domain'])
                ->toArray();

            $this->set('campaign_referers', $campaign_referers);
        }
    }

    /**
     * Fraud report by IP - views, earnings, referer ratio (Google vs Direct vs other)
     */
    public function ips()
    {
        $this->loadModel('Statistics');

        $ipStats = $this->Statistics->find()
            ->select([
                'ip' => 'Statistics.ip',
                'views' => 'COUNT(Statistics.id)',
                'earnings' => 'SUM(Statistics.publisher_earn)',
            ])
            ->where(['Statistics.publisher_earn >' => 0])
            ->group(['Statistics.ip'])
            ->order(['earnings' => 'DESC'])
            ->limit(500)
            ->toArray();

        $ipDetails = [];
        foreach ($ipStats as $row) {
            $refererBreakdown = $this->Statistics->find()
                ->select([
                    'referer_domain',
                    'count' => 'COUNT(*)',
                ])
                ->where([
                    'Statistics.ip' => $row->ip,
                    'Statistics.publisher_earn >' => 0,
                ])
                ->group(['referer_domain'])
                ->toArray();

            $totalRef = 0;
            $googleCount = 0;
            $directCount = 0;
            foreach ($refererBreakdown as $r) {
                $totalRef += $r->count;
                $ref = strtolower($r->referer_domain ?? '');
                if (strpos($ref, 'google') !== false) {
                    $googleCount += $r->count;
                } elseif ($ref === 'direct' || $ref === '') {
                    $directCount += $r->count;
                }
            }

            $ipDetails[] = [
                'ip' => $row->ip,
                'views' => $row->views,
                'earnings' => $row->earnings,
                'referer_breakdown' => $refererBreakdown,
                'google_ratio' => $totalRef > 0 ? round(100 * $googleCount / $totalRef, 1) : 0,
                'direct_ratio' => $totalRef > 0 ? round(100 * $directCount / $totalRef, 1) : 0,
                'other_ratio' => $totalRef > 0 ? round(100 * ($totalRef - $googleCount - $directCount) / $totalRef, 1) : 0,
            ];
        }

        $this->set('ipDetails', $ipDetails);
    }
}
