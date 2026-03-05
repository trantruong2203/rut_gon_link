<?php

namespace App\Controller\Admin;

use App\Controller\Admin\AppAdminController;
use Cake\Http\Exception\NotFoundException;

class KeywordTasksController extends AppAdminController
{
    public function index()
    {
        $query = $this->KeywordTasks->find()
            ->contain(['Campaigns'])
            ->order(['KeywordTasks.sort_order' => 'ASC', 'KeywordTasks.id' => 'DESC']);
        $keywordTasks = $this->paginate($query);

        $this->set('keywordTasks', $keywordTasks);
    }

    public function add()
    {
        $keywordTask = $this->KeywordTasks->newEntity([]);

        if ($this->request->is('post')) {
            $keywordTask = $this->KeywordTasks->patchEntity($keywordTask, $this->request->getData());
            if ($this->KeywordTasks->save($keywordTask)) {
                $this->Flash->success(__('Keyword task has been added.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Oops! There are mistakes in the form. Please make the correction.'));
        }

        $campaigns = $this->KeywordTasks->Campaigns->find('list')
            ->where(['Campaigns.status' => 1])
            ->order(['Campaigns.name' => 'ASC'])
            ->toArray();
        $campaigns = ['' => __('-- All campaigns --')] + $campaigns;

        $this->set(compact('keywordTask', 'campaigns'));
    }

    public function edit($id = null)
    {
        if (!$id) {
            throw new NotFoundException(__('Invalid keyword task'));
        }

        $keywordTask = $this->KeywordTasks->get($id, ['contain' => ['Campaigns']]);
        if (!$keywordTask) {
            throw new NotFoundException(__('Invalid keyword task'));
        }

        if ($this->request->is(['post', 'put'])) {
            $keywordTask = $this->KeywordTasks->patchEntity($keywordTask, $this->request->getData());
            if ($this->KeywordTasks->save($keywordTask)) {
                $this->Flash->success(__('Keyword task has been updated.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Oops! There are mistakes in the form. Please make the correction.'));
        }

        $campaigns = $this->KeywordTasks->Campaigns->find('list')
            ->where(['Campaigns.status' => 1])
            ->order(['Campaigns.name' => 'ASC'])
            ->toArray();
        $campaigns = ['' => __('-- All campaigns --')] + $campaigns;

        $this->set(compact('keywordTask', 'campaigns'));
    }

    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        if (!$id) {
            throw new NotFoundException(__('Invalid keyword task'));
        }

        $keywordTask = $this->KeywordTasks->get($id);
        if ($this->KeywordTasks->delete($keywordTask)) {
            $this->Flash->success(__('Keyword task has been deleted.'));
        } else {
            $this->Flash->error(__('Unable to delete keyword task.'));
        }
        return $this->redirect(['action' => 'index']);
    }
}
