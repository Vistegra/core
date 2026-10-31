Ядро CMS
git submodule add 
git submodule update --remote

константы:
 OUTSIDE = false - по умолчанию
 OUTSIDE = true - возврат страницы в json с кучей параметров. Обращаться к index.php
 OUTSIDE = 'S'  - возврат страницы в виде строки
 
 
 вывод калькулятора инлайн используя php.
 <?= require './calc/outside.php'; ?>

Настройки:

  Доступ к открытой странице только после регистрации
    Доступен через режим outside - всегда.
    true - регистрация обязательна
    false - по умолчанию.
  'ONLY_LOGIN' => true,
  
  /*
   * Возможно устарело
   * Страница для доступа без регистрации: файл с таким именем должен быть в public/views/
   */
  'PUBLIC_PAGE' => 'calculator',


Изменяя доступные страницы запустить createResourceFile из package.json
