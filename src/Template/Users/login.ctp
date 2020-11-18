<div class="users form">
  <?= $this->Flash->render() ?>
  <?= $this->Form->create() ?>
  <fieldset>
    <legend><?= __('ユーザ名とパスワードを入力してください') ?></legend>
    <?= $this->Form->control('name') ?>
    <?= $this->Form->control('password') ?>
  </fieldset>
  <div style="margin: 20px;">
    <?= $this->Form->button(__('Login')); ?>
  </div>
  <?= $this->Form->end() ?>
</div>
<div style="margin: 22px; text-align: right">
    <a href="http://passport-auth-sample.comee.ml/oauth/authorize?client_id=1&response_type=code&scope=*&redirect_uri=http://sample-site-a.comee.ml/users/passport-login-callback" class="btn" style="background-color: navy;">Passport Sample Login</a>
    <a href="http://auth.comee.ml/authorization?response_type=token&client_id=19273204454143&response_mode=form_post&redirect_uri=https://sample-site-a.comee.ml/users/twg-login-callback&scope=openid+email+profile&nonce=lkdjafjlkdsja" class="btn" style="background-color: green;">Authlete Sample Login</a>
    <a href="/users/add" class="btn" style="background-color: maroon;">Rigister</a>
</div>
