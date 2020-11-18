<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;
use Cake\Routing\Router;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 *
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class UsersController extends AppController
{
    public function initialize()
    {
        parent::initialize();

        $components = array('Auth');
        $user = $this->Auth->user();
        $this->set(compact('user'));
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        $users = $this->paginate($this->Users);

        $this->set(compact('users'));
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => [],
        ]);

        $this->set('user', $user);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->Users->newEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * 認証スルー設定
     * @param Event $event
     * @return \Cake\Http\Response|null|void
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        // auth off
        $this->Auth->allow(['login', 'add', 'twgLoginCallback', 'passportLoginCallback']);
        // csrf off
        if($this->request->action == 'twgLoginCallback'){
            $this->getEventManager()->off($this->Csrf);
        }
    }

    /**
     * ログイン
     * @return \Cake\Http\Response|null
     */
    public function login()
    {
        if ($this->request->is('post')) {
            $user = $this->Auth->identify();
            if ($user) {
                $this->Auth->setUser($user);
                return $this->redirect($this->Auth->redirectUrl());
            }
            $this->Flash->error(__('ユーザ名もしくはパスワードが間違っています'));
        }
    }

    /**
     * ログアウト
     * @return \Cake\Http\Response|null
     */
    public function logout()
    {
      return $this->redirect($this->Auth->logout());
    }

    public function twgLoginCallback()
    {
        if ($this->request->query())
        {
            // アクセストークン取得
            $token = $this->request->query('access_token');
            // curlコマンドでユーザー情報取得
            $headers = ["Authorization: Bearer " . $token ];
            $ch = curl_init("http://res.comee.ml/api/userinfo");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            // ユーザー情報が取得できた場合
            if ($result)
            {
                $result = json_decode($result, true);
                // メールアドレスで登録状況確認
                return $this->ssoUserLogin($result['email'], $result['name']);
            }
        }
    }

    public function passportLoginCallback()
    {
        if ($this->request->query())
        {
            // 認可コード取得
            if ($code = $this->request->query('code')) {
                // アクセストークン要求
                $data = [
                    'grant_type' => 'authorization_code',
                    'client_id' => '1',
                    'client_secret' => '92APPFrBgfb3p24yZiWiDtAf9neoz8M7iB4ZrGWd',
                    'redirect_uri' => 'http://sample-site-a.comee.ml/users/passport-login-callback',
                    'code' => $code
                ];
                $ch = curl_init('http://passport-auth-sample.comee.ml/oauth/token');
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($ch);
                curl_close($ch);

                // アクセストークン取得
                if ($result) {
                    $result = json_decode($result, true);
                    if ($token = $result['access_token']) {
                        // $expires = $result['expires'];   // アクセストークン有効期限チェック
                        // 期限切れの場合の処理

                        // ユーザー情報要求
                        $headers = ["Authorization: Bearer " . $token ];
                        $ch = curl_init("http://passport-auth-sample.comee.ml/api/user");
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $result = curl_exec($ch);
                        curl_close($ch);
                        
                        // ユーザー情報取得
                        if ($result)
                        {
                            $result = json_decode($result, true);
                            if ($result['email'] && $result['name']) {
                                return $this->ssoUserLogin($result['email'], $result['name']);
                            } else {
                                echo 'ユーザー情報の取得に失敗';
                                exit;
                            }
                        } else {
                            echo 'ユーザー情報取得curlに失敗';
                            exit;
                        }
                    } else {
                        echo 'アクセストークンの取得に失敗';
                        exit;
                    }
                } else {
                    echo 'アクセストークン取得curlに失敗';
                    exit;
                }
            } else {
                echo '認可コードの取得に失敗';
                exit;
            }
        } else {
            return $this->redirect(['action' => 'login']);
        }
    }

    private function ssoUserLogin($email, $name) {
        // メールアドレスで登録状況確認
        $user = $this->Users->find()->where(['email' => $email])->first();
        if ($user)
        {
            // 登録済みの場合、ログイン
            $this->Auth->setUser($user);
        } else {
            // 登録されていない場合、パスワードを補完してユーザー登録
            $pw = substr(str_shuffle('1234567890abcdefghijklmnopqrstuvwxyz'), 0, 8);
            $user = $this->Users->newEntity();
            $user->name = $name;
            $user->email = $email;
            $user->password = $pw;
            if ($this->Users->save($user)) {
                // 登録に成功した場合、ログイン
                $this->Auth->setUser($user);
            } else {
                echo 'ユーザー登録に失敗';
                exit;
            }
        }
        return $this->redirect($this->Auth->redirectUrl());
    }
}
