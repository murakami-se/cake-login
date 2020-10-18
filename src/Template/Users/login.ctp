<div class="users form">
  <?= $this->Flash->render() ?>
  <?= $this->Form->create() ?>
  <fieldset>
    <legend><?= __('ユーザ名とパスワードを入力してください') ?></legend>
    <?= $this->Form->control('name') ?>
    <?= $this->Form->control('password') ?>
  </fieldset>
  <?= $this->Form->button(__('Login')); ?>
  <?= $this->Form->end() ?>
</div>
<div class="">
    <?= $this->Html->link('create', 'users/add') ?>
    <?= $this->Html->link('sso-login', 'http://twg-dev-auth/authorization?response_type=token&client_id=19273204454143&response_mode=form_post&redirect_uri=https://twg-dev-sample1/users/twg-login-callback?&scope=openid+email+profile&nonce=lkdjafjlkdsja') ?>
</div>