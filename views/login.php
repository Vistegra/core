<?php if ( !defined('MAIN_ACCESS')) die('access denied!');

/**
 * @var boolean $wrongPass - from controller
 * @var string $login - from Request
 * @var string $pass - from Request
 */

!isset($pageTarget) && $pageTarget = '';
$actionLink = 'index.php';
$wrongString = $wrongPass ? '<div class="notification-container error" role="alert">Неправильный логин или пароль</div><br>' : '';

$publicLink = !ONLY_LOGIN && PUBLIC_PAGE ? '<a href="/' . PUBLIC_PAGE . '">Открытая страница</a>' : '';

/* Исользовать global что бы в базовом шаблоне не использовать структуру (надо будет инструкцию потом написать) */
$field['global'] = <<<global
<main class="container-fluid mx-auto">
  <section class="d-flex justify-content-center align-items-center" style="height: 100vh">
    <div class="col-xl-4 col-lg-6 col-md-10 mx-auto mt-5">
      <form id="authForm" action="$actionLink" method="POST" class="m-1">
        <div class="card">
          <div class="card-body">
            <div class="form-header bg-blue">
              <h3 class="font-weight-500 my-2 py-1"><i class="fas fa-user"></i> Авторизация:</h3>
            </div>
           
            <div class="md-form mb-5">
              <input name="login" type="text" id="orangeForm-name" class="form-control" value="$login" placeholder="Логин" required"> 
            </div>

            <div class="md-form mb-5">
              <input name="password" type="password" id="orangeForm-pass" class="form-control" value="$pass" placeholder="Пароль" required">
            </div>

            <div class="text-center">
              <button class="btn btn-info btn-lg">Войти</button>
            </div>
                        
            <input name="mode" type="hidden" value="auth">
            <input name="authAction" type="hidden" value="login">
            <input name="clientPageTarget" type="hidden" value="$pageTarget">
            $publicLink
          </div>
        </div>
      </form>
      <br>
      $wrongString
    </div>
  </section>
</main>
global;
