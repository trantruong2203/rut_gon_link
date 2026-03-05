<?php

namespace App\Controller\Admin;

use App\Controller\Admin\AppAdminController;
use Cake\Http\Exception\NotFoundException;

class PagesController extends AppAdminController
{

    public function index()
    {
        $query = $this->Pages->find();
        $pages = $this->paginate($query);

        $this->set('pages', $pages);
    }

    public function add()
    {

        $page = $this->Pages->newEntity([]);
        
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            if ($this->request->getData('slug') !== null && !empty($this->request->getData('slug'))) {
                $data['slug'] = $this->Pages->createSlug($this->request->getData('slug'));
            } else {
                $data['slug'] = $this->Pages->createSlug($this->request->getData('title'));
            }

            $page = $this->Pages->patchEntity($page, $data);
            
            if ($this->Pages->save($page)) {
                $this->Flash->success(__('Page has been added.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Oops! There are mistakes in the form. Please make the correction.'));
        }
        $this->set('page', $page);
    }
    
    public function edit($id = null)
    {

        if (!$id) {
            throw new NotFoundException(__('Invalid Page'));
        }
        
        if( $this->request->getQuery('lang') !== null && isset(get_site_languages()[$this->request->getQuery('lang')]) ) {
            //$page->_locale = $this->request->getQuery('lang');
            $this->Pages->locale($this->request->getQuery('lang'));
        }
        
        $page = $this->Pages->get($id);
        if (!$page) {
            throw new NotFoundException(__('Invalid Page'));
        }
        
        if ($this->request->is(['post', 'put'])) {
            $data = $this->request->getData();
            if ($this->request->getData('slug') !== null && !empty($this->request->getData('slug'))) {
                $data['slug'] = $this->Pages->createSlug($this->request->getData('slug'), $id);
            } else {
                $data['slug'] = $this->Pages->createSlug($this->request->getData('title'), $id);
            }

            $page = $this->Pages->patchEntity($page, $data);
            
            if ($this->Pages->save($page)) {
                $this->Flash->success(__('Page has been updated.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Oops! There are mistakes in the form. Please make the correction.'));
        }
        $this->set('page', $page);
    }
    
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        
        /*
        if(in_array($id, [1, 2, 3, 4, 5]) ) {
            $this->Flash->error(__('You can not delete this page.'));
            return $this->redirect(['action' => 'index']);
        }
        */

        $page = $this->Pages->findById($id)->first();
        
        if ($this->Pages->delete($page)) {
            $this->Flash->success(__('The page with id: {0} has been deleted.', $page->id));
            return $this->redirect(['action' => 'index']);
        }
    }
}
