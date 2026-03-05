<?php

namespace App\Controller\Admin;

use App\Controller\Admin\AppAdminController;
use Cake\Http\Exception\NotFoundException;
use Cake\Routing\Router;

class WithdrawsController extends AppAdminController
{

    public function index()
    {
        $query = $this->Withdraws->find()
            ->contain(['Users']);
        $withdraws = $this->paginate($query);

        $this->set('withdraws', $withdraws);

        $publishers_earnings = $this->Withdraws->Users->find()
            ->select(['total' => 'SUM(publisher_earnings)'])
            ->first();
        $this->set('publishers_earnings', $publishers_earnings->total);

        $referral_earnings = $this->Withdraws->Users->find()
            ->select(['total' => 'SUM(referral_earnings)'])
            ->first();
        $this->set('referral_earnings', $referral_earnings->total);

        $pending_withdrawn = $this->Withdraws->find()
            ->select(['total' => 'SUM(amount)'])
            ->where(['status' => 2])
            ->first();

        $this->set('pending_withdrawn', $pending_withdrawn->total);

        $total_withdrawn = $this->Withdraws->find()
            ->select(['total' => 'SUM(amount)'])
            ->where(['status' => 3])
            ->first();

        $this->set('total_withdrawn', $total_withdrawn->total);
    }

    public function edit($id = null)
    {
        if (!$id) {
            throw new NotFoundException(__('Invalid Withdraw'));
        }

        $withdraw = $this->Withdraws->find()->contain(['Users'])->where(['Withdraws.id' => $id])->first();
        if (!$withdraw) {
            throw new NotFoundException(__('Invalid Withdraw'));
        }

        if ($this->request->is(['post', 'put'])) {
            $data = $this->request->getData();
            $data['amount'] = $withdraw->amount;
            $withdraw = $this->Withdraws->patchEntity($withdraw, $data);
            if ($this->Withdraws->save($withdraw)) {
                $this->Flash->success(__('The withdraw has been updated.'));
                return $this->redirect(['action' => 'index']);
            } else {
                debug($withdraw->errors());
                $this->Flash->error(__('Oops! There are mistakes in the form. Please make the correction.'));
            }
        }
        $this->set('withdraw', $withdraw);
    }

    public function approve($id)
    {
        $this->request->allowMethod(['post', 'put']);

        $withdraw = $this->Withdraws->get($id);

        $withdraw->status = 2; // 2 = Approved (pending payment), cÃ³ thá»ƒ reject tá»« Ä‘Ã¢y

        if ($this->Withdraws->save($withdraw)) {
            $this->Flash->success(__('The withdraw with id: {0} has been approved.', $id));
            return $this->redirect(['action' => 'index']);
        } else {
            $this->Flash->error(__('Unable to approve the withdraw. Please try again.'));
            return $this->redirect(['action' => 'index']);
        }
    }

    public function complete($id)
    {
        $this->request->allowMethod(['post', 'put']);

        $withdraw = $this->Withdraws->get($id); // Tá»± throw RecordNotFoundException náº¿u khÃ´ng tÃ¬m tháº¥y

        $withdraw->status = 3;

        if ($this->Withdraws->save($withdraw)) {
            $user = $this->Withdraws->Users->get($withdraw->user_id);
            if ($user && $withdraw->method === 'wallet') {
                $user->wallet_money += $withdraw->amount;
                $this->Withdraws->Users->save($user);
            }
            $this->Flash->success(__('The withdraw has been completed.'));
            return $this->redirect(['action' => 'index']);
        } else {
            $this->Flash->error(__('Unable to complete the withdraw. Please try again.'));
            return $this->redirect(['action' => 'index']);
        }
    }

    public function reject($id)
    {
        $this->request->allowMethod(['post', 'put']);

        $withdraw = $this->Withdraws->get($id); // Tá»± throw RecordNotFoundException náº¿u khÃ´ng tÃ¬m tháº¥y

        if ($withdraw->status != 2) {
            $this->Flash->error(__('Only pending withdrawals can be rejected.'));
            return $this->redirect(['action' => 'index']);
        }

        $withdraw->status = 4;

        if ($this->Withdraws->save($withdraw)) {
            $user = $this->Withdraws->Users->get($withdraw->user_id);
            if ($user) {
                $user->publisher_earnings += $withdraw->publisher_earnings;
                $user->referral_earnings += $withdraw->referral_earnings;
                $this->Withdraws->Users->save($user);
            }
            $this->Flash->success(__('Withdrawal has been rejected.'));
            return $this->redirect(['action' => 'index']);
        }
    }
}

