<?php global $main;
if ($main && $main->checkStatus('ok')) { ?>
<nav class="navbar navbar-expand-lg navbar-transparent border-bottom">
  <div class="container-fluid">
    <div class="collapse navbar-collapse justify-content-end">
      <ul class="navbar-nav" id="authBlock">
        <li class="nav-item"><?= $main->getLogin('name') ?></li>
        <li class="nav-item">
          <span class="exit-icon d-flex" data-action="exit">
            <i class="material-icons font-blue" data-action="exit">login_out</i>
          </span>
        </li>
      </ul>
    </div>
  </div>
</nav>
<?php } else { ?>
  <div class="auth-block">
  </div>
<?php } ?>
