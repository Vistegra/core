Ядро CMS:\
git submodule add https://github.com/Vistegra/core.git\
git submodule update --remote

Быстрая установка зависимостей и сборка ассетов (из корня проекта):\
`powershell -ExecutionPolicy Bypass -File ./core/setup.ps1`

константы: \
 OUTSIDE = false - по умолчанию \
 OUTSIDE = true - возврат страницы в json с кучей параметров. Обращаться к index.php \
 OUTSIDE = 'S'  - возврат страницы в виде строки
 
 
 вывод калькулятора inline используя php.
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

[tooltips](https://f3oall.github.io/awesome-notifications/docs/) \
[sweetalert2](https://sweetalert2.github.io/)

name | type | desc
---|---|---
f.log() | function | обычный консоль лог, проверяет перменную Debug
f.capitalize() | function | Первый символ строки в верхний регистр
f.camelize() | function | строку в camelCase
f.gI() | function | getElementById + поиск в shadowDom
f.qS() | function | querySelector + поиск в shadowDom
f.qA() | function | querySelectorAll + поиск в shadowDom. Быстрая установка свойств
f.gT() | function | Получить шаблон как string
f.gTNode() | function | Получить шаблон как node
f.getDataAsAssoc() | function | Дата из input[hidden] как объект
f.getDataAsMap() | function | Дата из input[hidden] как Map
f.getDataAsSet() | function | Дата из input[hidden] как Set
f.getDataAsArray() | function | Дата из input[hidden] как массив
f.createElement() | function | Создать node из string
f.show() | function | показать node (убрать d-none)
f.hide() | function | скрыть node (добавить d-none)
f.eraseNode() | function | Очистить node
f.replaceLetter() | function | Заменить Аналогичные Русские Символы на Латинские. Например для поиска
f.replaceTemplate() | function | Рендер шаблона
f.relatedOption() | function | Зависимые поля похоже на у Vue
f.saveFile() | function | Скачать заданный объект как файл
f.initMask() | function | Маска ввода
f.enable() | function | Активировать элементы (убрать disabled и класс disabled)
f.disable() | function | Деактивировать элементы (добавить disabled и класс disabled)
f.setLoading() | function | Активировать загрузку на элементе
f.removeLoading() | function | Удалить загрузку на элементе
f.printReport() | function | Печать по умолчанию (использовать как шаблон)
f.downloadPdf() | function | Скачать PDF файл отправив объект с отчетом
f.saveOrder() | function | Сохранить отчет (доработать)
f.sendOrder() | function | Отправить отчет на почту (доработать)
f.getRate() | function | Получить курсы из input[hidden]
f.flashNode() | function | Подсветка узла
f.objectFlat() | function | Приведение любой перменной в одномерный массив
f.toBool() | function | Приведение любой переменной в бинарный тип
f.toNumber() | function | Попытка перевести любую строку в число
f.parseNumber() | function | Аналогично toNumber
f.setFormat() | function | Формат 1 000
f.transLit() | function | Транслитерация с русского на латиницу
f.getSetting() | function | Получить пользовательские настройки cms
f.Debugger() | class | Отчет для калькуляторов (доработать)
f.Modal() | function | Модальное окно.
f.initModal() | function | Аналогично f.Modal()
f.initPrint() | function | Функция печати с ожиданиями картинок и стилей с сервера.
f.observer() | function | Паттерн observer
f.searchInit() | function | Поиск (обычно пользователей)
f.InitSaveVisitorsOrder() | function | Сохранение клиентских заказов
f.LoaderIcon() | function | Более универсальный загрузчик
f.LocalStorage() | class | Работа с localStorage
f.OneTimeFunction() | class | Одноразовый функции
f.Pagination() | class | Pagination (например для таблиц)
f.SelectedRow() | class | Сортировка по столбцам с запросами к БД (например для таблиц)
f.SelectedRow() | class | Выделение строк (например для таблиц)
f.showMsg() | function | Показать сообщение
f.Valid() | class | Валидация форм + загрузка файлов
f.User() | class | Вся информация о текущем пользователе.
