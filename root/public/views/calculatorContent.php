<?php
global $authStatus, $dbContent, $orderId;
$class = 'd-none';
?>
<div class="content">
  <main>
    <div class="container">
      <h1 class="heading-block__h1">Калькулятор</h1>
      <form action="#" id="formParam">

        <section class="calc-block">
          <span class="calc-block__title">Ширина, м</span>
          <span class="calc-block__text">описание</span>
          <div class="calc-form">
            <div class="cl_input-number">
              <button type="button" class="cl_input-number__btn inputChange"
                      data-change="-0.1" data-input="width">-</button>
              <input type="number" class="cl_input-number__input" name="width" value="5" min="1" max="100">
              <button type="button" class="cl_input-number__btn inputChange"
                      data-change="0.1" data-input="width">+</button>
            </div>
          </div>
        </section>

        <section class="calc-block">
          <span class="calc-block__title">Длина, м</span>
          <span class="calc-block__text">описание</span>
          <div class="calc-form">
            <div class="cl_input-number">
              <button type="button" class="cl_input-number__btn inputChange"
                      data-change="-0.1" data-input="length">-</button>
              <input type="number" class="cl_input-number__input" name="length" value="5" min="1" max="100">
              <button type="button" class="cl_input-number__btn inputChange"
                      data-change="0.1" data-input="length">+</button>
            </div>
          </div>
          <span class="prompt-text">описание</span>
        </section>

        <section class="calc-block">
          <span class="calc-block__title">Цвет</span>
          <div class="calc-form d-flex">
            <div class="form-group">
              <input class="custom-radio style-box bg-green" type="radio" id="color1" name="color" value="color1"
                     checked>
              <label for="color1">Зеленый</label>
            </div>
            <div class="form-group">
              <input class="custom-radio style-box bg-gray" type="radio" id="polycarbonate-2" name="color"
                     value="color2">
              <label for="color2">Бронзовый</label>
            </div>
            <div class="form-group">
              <input class="custom-radio style-box bg-amber" type="radio" id="polycarbonate-3" name="color"
                     value="color3">
              <label for="color3">Янтарный</label>
            </div>
          </div>
        </section>

        <section class="calc-block">
          <span class="calc-block__title">Переключатель</span>

          <div class="frame-list d-flex">
            <div class="radio-tab style2 col">
              <input hidden type="radio" name="paintType" id="frame-list-1"
                     value="paintG" data-target="paintTypeG" checked>
              <label for="frame-list-1" class="radio-tab__label">ТАБ1</label>
            </div>

            <div class="radio-tab style2 col">
              <input hidden type="radio" name="paintType" id="frame-list-2"
                     value="paintH" data-target="paintTypeH">
              <label for="frame-list-2" class="radio-tab__label">ТАБ2</label>
            </div>
          </div>

          <div class="d-flex calc-form paintTypeG">
            <div class="form-group">
              <input class="custom-radio style-circle" type="radio" id="frame-1" name="colorTab1" value="1" checked>
              <label for="frame-1">Серый</label>
            </div>
            <div class="form-group">
              <input class="custom-radio style-circle" type="radio" id="frame-2" name="colorTab1" value="2">
              <label for="frame-2">Коричневый</label>
            </div>
          </div>

          <div class="calc-form d-flex paintTypeH">
            <div class="form-group">
              <input class="custom-radio style-circle" type="radio" id="frame3" name="colorTab2" value="1" checked>
              <label for="frame3">Серый</label>
            </div>
            <div class="form-group">
              <input class="custom-radio style-circle" type="radio" id="frame4" name="colorTab2" value="2">
              <label for="frame4">Зеленый</label>
            </div>
            <div class="form-group">
              <input class="custom-radio style-circle" type="radio" id="frame5" name="colorTab2" value="3">
              <label for="frame5">Коричневый</label>
            </div>
          </div>

        </section>

        <section class="calc-block">
          <span class="calc-block__title">Доставка</span>
          <div class="calc-form">
            <div class="flex-column align-start">
              <span class="">Расстояние, км</span>
              <div class="cl_input-number">
                <button type="button" class="cl_input-number__btn inputChange"
                        data-change="-5" data-input="delivery">-
                </button>
                <input type="number" name="delivery" value="5" min="0" class="cl_input-number__input">
                <button type="button" class="cl_input-number__btn inputChange"
                        data-change="5" data-input="delivery">+
                </button>
              </div>
            </div>
          </div>
        </section>

        <button type="button" id="btnCalc">Рассчитать</button>
      </form>

      <section class="container">
        <?php if ($orderId && is_numeric($orderId)) {
          $class = '';
        } else {
          $orderId = '';
          $class = 'd-none';
        } ?>
        <span class="<?= $class ?>">
          СМЕТА №
          <span id="orderNum" data-order="<?= $orderId ?>"><?= $orderId ?></span>
        </span>

        <div class="">
          <table class="estimate-table">
            <thead>
            <tr>
              <th>Наименование</th>
              <th>Стоимость</th>
              <th>Количество</th>
              <th>Итог, руб</th>
            </tr>
            </thead>
            <tbody id="tbody"></tbody>
          </table>
          <div id="total"></div>
        </div>

        <?php if ($authStatus) { ?>
          <div class="d-flex w-100" id="btnField">
            <button type="button" class="cl_btn st-blue m-1 col" id="saveOrderModal">Сохранить</button>
            <button type="button" class="cl_btn st-blue m-1" data-action="savePdf" data-type="pdfType1">Скачать .PDF
            </button>
            <button type="button" class="cl_btn st-blue m-1" id="btnSendMailModal">Отправить .PDF</button>
            <button type="button" class="cl_btn st-white m-1 col" data-action="printReport" data-type="printType1">
              Печать
            </button>
          </div>
        <?php } ?>

      </section>
    </div>
  </main>
</div>

<div class="modal-overlay" id="modalWrap">
  <div class="modal">
    <div class="modalC"></div>
  </div>
</div>

<!-- dev btn -->
<input type="button" id="devOn">
<?= $dbContent ?>
