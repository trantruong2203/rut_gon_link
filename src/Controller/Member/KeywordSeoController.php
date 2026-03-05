<?php

namespace App\Controller\Member;

use App\Controller\Member\AppMemberController;
use Cake\Http\Exception\NotFoundException;

class KeywordSeoController extends AppMemberController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->FormProtection->setConfig('unlockedActions', ['submit'], true);
    }

    public function index()
    {
        // Get all running SEO campaigns
        $campaigns = $this->Campaigns->find()
            ->where([
                'Campaigns.keyword_seo_status' => 'running',
                'Campaigns.keyword_seo_code IS NOT' => null
            ])
            ->order(['Campaigns.seo_current_views' => 'ASC'])
            ->limit(20)
            ->toArray();

        $this->set('campaigns', $campaigns);
    }

    public function start($id = null)
    {
        if (!$id) {
            throw new NotFoundException(__('Invalid campaign'));
        }

        $campaign = $this->Campaigns->findById($id)->first();
        
        if (!$campaign || empty($campaign->keyword_seo_code) || $campaign->keyword_seo_status !== 'running') {
            throw new NotFoundException(__('Campaign not found or not available'));
        }

        // Check if this IP already completed today
        $ip = $this->request->clientIp();
        $today = date('Y-m-d');

        // For now, we'll handle the check in submit action
        // Store campaign in session for tracking
        $this->request->getSession()->write('KeywordSeo.campaign_id', $campaign->id);

        $this->set('campaign', $campaign);
    }

    public function submit()
    {
        if ($this->request->is('post')) {
            $code = $this->request->getData('code');
            $campaignId = $this->request->getData('campaign_id');

            if (empty($code) || empty($campaignId)) {
                $this->Flash->error(__('Please enter the code and select a campaign.'));
                return $this->redirect(['action' => 'index']);
            }

            // Find campaign by ID and code
            $campaign = $this->Campaigns->find()
                ->where([
                    'Campaigns.id' => $campaignId,
                    'Campaigns.keyword_seo_code' => $code,
                    'Campaigns.keyword_seo_status' => 'running'
                ])
                ->first();

            if (!$campaign) {
                $this->Flash->error(__('Invalid code or campaign not found.'));
                return $this->redirect(['action' => 'index']);
            }

            // Check if IP already completed today
            // TODO: Create a tracking table for better tracking
            // For now, we'll just increment the view count
            // In production, you'd want to track: campaign_id + ip + date

            $ip = $this->request->clientIp();
            $userId = $this->Auth->user('id');

            // Get publisher earning per view from options
            $publisherEarn = (float)get_option('keyword_seo_publisher_earn', 0.05);

            // Update campaign views
            $campaign->seo_current_views = $campaign->seo_current_views + 1;

            // Check if target reached
            if ($campaign->seo_current_views >= $campaign->seo_target_views) {
                $campaign->keyword_seo_status = 'completed';
            }

            if ($this->Campaigns->save($campaign)) {
                // Update user balance
                $user = $this->Users->findById($userId)->first();
                if ($user) {
                    $user->publisher_earnings = $user->publisher_earnings + $publisherEarn;
                    $this->Users->save($user);
                }

                $this->Flash->success(__('Congratulations! You earned $%s. Campaign progress: %d/%d', 
                    number_format($publisherEarn, 2),
                    $campaign->seo_current_views,
                    $campaign->seo_target_views
                ));
            } else {
                $this->Flash->error(__('An error occurred. Please try again.'));
            }

            return $this->redirect(['action' => 'index']);
        }

        return $this->redirect(['action' => 'index']);
    }

    public function result()
    {
        // Show result after submission
        $this->set('success', $this->request->getQuery('success', false));
        $this->set('message', $this->request->getQuery('message', ''));
    }
}
