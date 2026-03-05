<?php

namespace App\Controller\Admin;

use App\Controller\Admin\AppAdminController;
use Cake\Http\Exception\NotFoundException;

class PostsController extends AppAdminController
{

    public function index()
    {
        $query = $this->Posts->find();
        $posts = $this->paginate($query);

        $this->set('posts', $posts);
    }

    public function add()
    {

        $post = $this->Posts->newEntity([]);
        
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            if ($this->request->getData('slug') !== null && !empty($this->request->getData('slug'))) {
                $data['slug'] = $this->Posts->createSlug($this->request->getData('slug'));
            } else {
                $data['slug'] = $this->Posts->createSlug($this->request->getData('title'));
            }

            $post = $this->Posts->patchEntity($post, $data);
            
            if ($this->Posts->save($post)) {
                $this->Flash->success(__('Post has been added.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Oops! There are mistakes in the form. Please make the correction.'));
        }
        $this->set('post', $post);
    }
    
    public function edit($id = null)
    {

        if (!$id) {
            throw new NotFoundException(__('Invalid Post'));
        }
        
        if( $this->request->getQuery('lang') !== null && isset(get_site_languages()[$this->request->getQuery('lang')]) ) {
            //$post->_locale = $this->request->getQuery('lang');
            $this->Posts->locale($this->request->getQuery('lang'));
        }
        
        $post = $this->Posts->get($id);
        if (!$post) {
            throw new NotFoundException(__('Invalid Post'));
        }
        
        if ($this->request->is(['post', 'put'])) {
            $data = $this->request->getData();
            if ($this->request->getData('slug') !== null && !empty($this->request->getData('slug'))) {
                $data['slug'] = $this->Posts->createSlug($this->request->getData('slug'), $id);
            } else {
                $data['slug'] = $this->Posts->createSlug($this->request->getData('title'), $id);
            }

            $post = $this->Posts->patchEntity($post, $data);
            
            if ($this->Posts->save($post)) {
                $this->Flash->success(__('Post has been updated.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Oops! There are mistakes in the form. Please make the correction.'));
        }
        $this->set('post', $post);
    }
    
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        
        /*
        if(in_array($id, [1, 2, 3, 4, 5]) ) {
            $this->Flash->error(__('You can not delete this post.'));
            return $this->redirect(['action' => 'index']);
        }
        */

        $post = $this->Posts->findById($id)->first();
        
        if ($this->Posts->delete($post)) {
            $this->Flash->success(__('The post with id: {0} has been deleted.', $post->id));
            return $this->redirect(['action' => 'index']);
        }
    }
}
