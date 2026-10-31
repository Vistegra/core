'use strict';

import './_modal.scss';

import {c} from "./const.js";
import {f} from "./func.js";

/**
 * Модальное окно
 * @param param {{modalId: string,
 * template: string,
 * showDefaultButton: boolean,
 * btnConfig: boolean }}
 */
export const Modal = (param = {}) => {
  let modal = Object.create(null),
      data = Object.create(null),
      {
        modalId = 'adminPopup',
        template = '',
        showDefaultButton = true,
        btnConfig = false,
      } = param;

  const findNode = (n, role) => n.querySelector(`[data-role="${role}"]`);

  modal.bindBtn = function () {
    this.wrap.querySelectorAll('.close-modal, .confirmYes, .closeBtn')
        .forEach(n => n.addEventListener('click', () => this.hide()));
  }
  modal.btnConfig = function (key, param = Object.create(null)) {
    let node = this.wrap.querySelector('.' + key.replace('.', ''));
    node && param && Object.entries(param).forEach(([k, v]) => {node[k] = v});
  }
  modal.onEvent = function () {
    const func = e => {
      if (e.key === 'Escape') {
        modal.hide();
        document.removeEventListener('keyup', func);
      }
    }
    document.addEventListener('keyup', func);
  }
  modal.querySelector = function (selector) { return this.wrap.querySelector(selector) }
  modal.querySelectorAll = function (selector) { return this.wrap.querySelectorAll(selector) }

  /**
   * Show modal window
   * @param {string|HTMLElement} title
   * @param {string|HTMLElement} content
   */
  modal.show = function (title, content = '') {
    this.title && title !== undefined && f.eraseNode(this.title).append(title);
    if (this.content && content) {
      f.eraseNode(this.content);
      typeof content === 'string' ? this.content.insertAdjacentHTML('afterbegin', content)
                                  : this.content.append(content);
    }

    data.bodyOver = document.body.style.overflow;
    data.scrollY = Math.max(window.scrollY, window.pageYOffset, document.body.scrollTop);
    document.body.style.overflow = 'hidden';

    if (document.documentElement.getBoundingClientRect().height > window.innerHeight && window.innerWidth > 800) {
      data.bodyPaddingRight = document.body.style.paddingRight;
      document.body.style.paddingRight = '16px';
    }

    this.wrap.style.display = 'flex';
    this.wrap.classList.add('active');
    this.window.classList.add('active');
    modal.onEvent();
  }

  modal.hide = function () {
    this.wrap.classList.remove('active');
    this.window.classList.remove('active');

    setTimeout( () => {
      this.wrap.style.display = 'none';
      document.body.style.overflow = data.bodyOver || 'initial';
      window.scrollTo(0, data.scrollY);
      if (document.body.style.paddingRight === '16px')
        document.body.style.paddingRight = data.bodyPaddingRight || 'initial';
    }, 300);
    //c.eraseNode(modal.content);
  }

  modal.destroy = function () {
    this.hide();
    this.wrap.querySelectorAll('.close-modal, .confirmYes, .closeBtn')
        .forEach(n => n.removeEventListener('click', () => this.hide()));
    this.wrap.remove();
  }

  modal.setTemplate = function () {
    const node = document.createElement('div');
    node.insertAdjacentHTML('afterbegin', template || templatePopup());

    this.wrap     = node.children[0];
    this.window   = findNode(node, 'window');
    this.title    = findNode(node, 'title');
    this.content  = findNode(node, 'content');
    this.btnField = findNode(node, 'bottomFieldBtn');

    if (btnConfig) this.btnConfig(btnConfig);
    else this.btnField && !showDefaultButton && f.eraseNode(this.btnField);

    let btnY = this.wrap.querySelector('.confirmYes, [data-action="confirmYes"], [data-target="confirmBtn"]'),
        btnN = this.wrap.querySelector('.closeBtn, [data-action="confirmNo"], [data-target="cancelBtn"]');
    btnY && (this.btnConfirm = btnY);
    btnN && (this.btnCancel = btnN);

    document.body.append(node.children[0]);
  }

  const templatePopup = () => {
    return `
    <div class="modal-overlay" id="${modalId}">
      <div class="modal p-3" data-role="window">
        <button type="button" class="close-modal">
          <div class="close-icon"></div>
        </button>
        <div class="modal-title" data-role="title">Title</div>
        <div class="w-100 pt-20" data-role="content"></div>
        <div class="modal-button" data-role="bottomFieldBtn">
          <input type="button" class="confirmYes btn btn-success" value="Подтвердить" data-action="confirmYes">
          <input type="button" class="closeBtn btn btn-warning" value="Отмена" data-action="confirmNo">
        </div>
      </div>
    </div>`;
  }

  modal.setTemplate();
  //btnConfig && modal.btnConfig(btnConfig);
  modal.bindBtn();

  return modal;
}
