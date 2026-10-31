'use strict';

import './_valid.scss';

import {c} from "./const";

// Валидация
export class Valid {
  constructor(param) {
    let {
          sendFunc = () => {},
          formNode = false,
          formSelector = '#authForm',
          submitNode = false,
          submitSelector = '#btnConfirmSend',
          fileFieldSelector = false, // Если поля не будет тогда просто after
          initMask = true,
          phoneMask = false,
        } = param;

    this.valid = new Set();
    try {
      this.form = formNode || document.querySelector(formSelector);
      this.btn  = submitNode || this.form.querySelector(submitSelector) || document.querySelector(submitSelector);
    } catch (e) {
      console.log(e.message); return;
    }

    this.initParam(param);

    // Form
    this.inputNodes = this.form.querySelectorAll('input[required]');
    this.inputNodes.forEach(n => {
      this.countNodes++;
      if (n.type === 'checkbox') n.addEventListener('click', e => this.validate(e));
      else {
        n.addEventListener('keyup', e => this.keyEnter(e));

        initMask && n.type === 'tel' && f.maskInit && f.maskInit(n, phoneMask);
      }
      n.addEventListener('blur', e => this.validate(e)); // может и не нужна
      this.validate(n);
    });

    // Files
    this.fileInput = this.form.querySelector('input[type="file"]');
    if (this.fileInput) {
      fileFieldSelector && (this.fileField = this.form.querySelector(fileFieldSelector)); // Возможно понадобиться много полей
      this.files = {};
    }

    // Send Btn
    this.btn.onclick = e => this.confirm(e, sendFunc);
    if (this.countNodes === 0 || this.debug) this.btnActivate();
    else if (this.valid.size < this.countNodes) this.btnDisabled();

    this.onEventForm();
  }

  initParam(param) {
    let {
          cssClass = {
            error: 'cl-input-error',
            valid: 'cl-input-valid',
          },
          debug = c.DEBUG || false,
        } = param;
    this.cssClass = cssClass;
    this.debug = debug;
    this.countNodes = 0;
  }

  // Активировать/Деактивировать кнопки
  btnActivate() {
    if (this.valid.size >= this.countNodes) delete this.btn.dataset.disabled;
    else this.btn.dataset.disabled = '1';
  }

  btnDisabled() {
    this.valid.clear();
    this.btnActivate();
  }

  checkFileInput() {
    let error = false;

    for (const file of Object.values(this.fileInput.files)) {
      let id = Math.random() * 10000 | 0;

      file.fileError = file.size > 1024*1024;
      if (file.fileError && !error) error = true;

      this.files[id] && (id += '1');
      this.files[id] = file;
    }
    this.clearInput(this.fileInput.files);
    this.showFiles();

    if (error) {
      this.setErrorValidate(this.fileInput);
      this.btn.setAttribute('disabled', 'disabled');
    } else {
      this.setValidated(this.fileInput);
      this.btnActivate();
    }
  }

  keyEnter(e) {
    if (e.key === 'Enter') {
      e.target.dispatchEvent(new Event('blur'));
      e.target.blur();
    } else {
      setTimeout(() => this.validate(e), 10);
    }
  }

  validate(e, ignoreValue = false) {
    let node = e.target || e, reg;
    if (node.value.length > 0 || ignoreValue) {
      switch (node.name) {
        case 'name':
          if (node.value.length < 2) { this.setErrorValidate(node); }
          else this.setValidated(node);
          break;

        case 'phone': case 'tel':
          reg = /[^\d|\(|\)|\s|\-|_|\+]/;
          if (node.value.length < 18 || reg.test(String(node.value).toLowerCase())) {
            this.setErrorValidate(node);
          } else this.setValidated(node);
          break;

        case 'email':
          reg = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
          if (!reg.test(String(node.value).toLowerCase())) {
            this.setErrorValidate(node);
          } else this.setValidated(node);
          break;

        default: {
          this.setValidated(node);
        }
      }
    } else this.removeValidateClasses(node);
    !ignoreValue && this.btnActivate();
  }

  // Показать/Скрыть (ошибки) валидации
  setErrorValidate(node) {
    this.removeValidateClasses(node);
    node.classList.add(this.cssClass.error);
  }

  setValidated(node) {
    this.removeValidateClasses(node);
    node.classList.add(this.cssClass.valid);
    this.valid.add(node.id);
  }

  showFiles() {
    let html = '';

    Object.entries(this.files).forEach(([i, file]) => {
      html += this.getFileTemplate(file, i);
    });

    if (this.fileField) this.fileField.innerHTML = html;
    else this.fileInput.insertAdjacentHTML('afterend', '<div>' + html + '</div>');
  }

  removeValidateClasses(node) {
    node.classList.remove(this.cssClass.error, this.cssClass.valid);
    this.valid.delete(node.id);
  }

  confirm(e, sendFunc) {
    if (e.target.dataset.disabled) {
      this.inputNodes.forEach(target => this.validate({target}, true));
      return;
    }

    const formData = new FormData(this.form),
          finished = () => {

            this.form.querySelectorAll('input')
                .forEach(n => {
                  n.value = '';
                  this.removeValidateClasses(n);
                });
            this.btnDisabled();

            //  добавить удаление события проверки файла
          }

    this.fileInput && formData.delete(this.fileInput.name);
    this.files && Object.entries(this.files).forEach(([id, file]) => {
      formData.append(id, file, file.name);
    });

    sendFunc(formData, finished, e);
  }

  clickCommon(e) {
    let target = e.target.dataset.action ? e.target : e.target.closest('[data-action]'),
        action = target && target.dataset.action;

    if (!action) return false;

    let select = {
      'removeFile': () => this.removeFile(target),
    }

    select[action] && select[action]();
  }

  removeFile(target) {
    delete this.files[target.dataset.id];
    this.checkFileInput();
  }

  onEventForm() {
    this.form.addEventListener('click', (e) => this.clickCommon(e));
    this.fileInput && this.fileInput.addEventListener('change', this.checkFileInput.bind(this));
  }

  clearInput(node) {
    let input = document.createElement('input');
    input.type = 'file';
    node.files = input.files;
  }

  getFileTemplate(file, i) {
    return `<div class="attach__item ${file.fileError ? this.cssClass.error : ''}">
        <span class="bold">${file.name}</span>
        <span class="table-basket__cross"
              data-id="${i}"
              data-action="removeFile"></span></div>`;
  }
}
