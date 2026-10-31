'use strict';

import {Catalog} from "./Main";

export class Section extends Catalog {
  constructor() {
    super('section');
    this.setParam();
    this.setNodes();
    this.onEvent();

    f.observer.subscribe('searchInput', (d, c) => this.searchEvent(c));
  }

  setParam() {
    this.sectionList = new Map();

    this.queryParam.tableName = 'section';
    this.queryParam.dbAction  = 'loadSection';
    this.queryParam.sectionId = 0;
    this.oneFunc = new f.OneTimeFunction('msgEmpty', this.showMsgEmpty);
  }
  setNodes() {
    this.node.main     = f.qS('#sectionField');
    this.node.cSection = document.createElement('div');

    this.tmp.sectionWrap = f.gTNode('#sectionWrap');
    this.tmp.section     = f.gT('#section');
    this.tmp.form        = f.gTNode('#sectionForm');
  }
  showMsgEmpty(sections) {
    if (!sections.length) f.showMsg('Создайте свой первый раздел!', 'warning');
  }

  addSectionList(sections) {
    let parent = this.sectionList.get(this.queryParam.sectionId) || {ID: 0};

    for(let i of this.sectionList.entries()) { // обновление секции
      if(i[1].parent.ID === parent.ID) this.sectionList.delete(i[0]);
    }
    sections.forEach(i => this.sectionList.set(i.ID, Object.assign(i, {parent: parent})));
  }
  appendSection(sections) {
    let section;

    this.oneFunc.exec('msgEmpty', sections);
    if (!sections.length) {
      section = this.node.main.querySelector(`[data-id="${this.queryParam.sectionId}"]`);
      this.cl_closeSection(section);
      section.click();
      return;
    }
    this.addSectionList(sections);

    let wrap = this.tmp.sectionWrap.cloneNode(true);
    wrap.innerHTML = f.replaceTemplate(this.tmp.section, sections);

    let id = sections[0].parent.ID;
    section = this.node.main.querySelector(`[data-id="${id}"] + .subSection`);
    f.eraseNode(section).append(wrap);
  }
  loadSection() {
    this.query().then(data => data['section'] && this.appendSection(data['section']));
  }

  cl_openSection(s = this.node.cSection) {
    if (s.classList.contains('closeSection')) {
      s.classList.remove('closeSection');
      s.classList.add('openSection');
    }
  }
  cl_closeSection(s = this.node.cSection) {
    if (s.classList.contains('openSection')) {
      s.classList.add('closeSection');
      s.classList.remove('openSection');
    }
  }
  getParentSection(id) {
    let section = this.sectionList.get(id);
    return (section.parent && section.parent['ID']) || 0;
  }

  searchEvent(clearSearch) {
    clearSearch ? f.show(this.node.main) : f.hide(this.node.main);
  }

  // Events function
  //--------------------------------------------------------------------------------------------------------------------
  // Выбрать
  clickSection(target) {
    this.node.cSection.classList.remove('focused');
    this.node.cSection = target;
    target.classList.add('focused');
  }
  // Создать
  createSection() {
    let form         = this.tmp.form,
        sectionNode  = this.node.cSection,
        parentId     = sectionNode.dataset.id || false,
        nodeParentId = form.querySelector('[name="parentId"]');

    // TODO Добавить проверку раздела
    nodeParentId.value = parentId || 0;
    // TODO обновлять список разделов

    this.queryParam.form = form;
    this.M.show('Создание раздел', form);

    this.reloadAction = {
      dbAction : 'loadSection',
      sectionId: parentId || 0,
      callback: data => {
        this.cl_openSection(sectionNode); // Открыть секцию если закрыта
        this.appendSection(data['section']);

        // Выделить новую секцию
        let name = form.querySelector('[name="name"]').value,
            id = data['section'].find(s => s.name === name)['ID'];
        sectionNode = this.node.main.querySelector(`[data-id="${id}"]`);
        sectionNode && sectionNode.click();
      },
    };
  }
  // Открыть
  openSection() {
    this.queryParam.sectionId = this.node.cSection.dataset.id || false;
    if (!this.queryParam.sectionId) return;
    f.observer.fire('openSection', this.queryParam.sectionId);
  }
  // Изменить
  changeSection() {
    let form = this.tmp.form, node;

    this.queryParam.sectionId = this.node.cSection.dataset.id || false;
    if (!this.queryParam.sectionId) { return; }

    let parentId = this.getParentSection(this.queryParam.sectionId),
        section = this.sectionList.get(this.queryParam.sectionId);

    node = form.querySelector('[name="name"]');
    node.value = section.name;

    node = form.querySelector('[name="code"]');
    node.value = section.name;

    node = form.querySelector('[name="parentId"]');
    node.value = parentId;

    this.queryParam.form = form;
    this.M.show('Изменить раздел', form);

    this.reloadAction = {
      dbAction : 'loadSection',
      sectionId: parentId,
      callback: data => {
        this.appendSection(data['section']);

        let section = this.node.main.querySelector(`[data-id="${node.value}"]`);
        section.click();
        /*this.queryParam.dbAction = 'loadSection';
        this.queryParam.sectionId = node.value;
        this.loadSection();*/
      },
    };
  }
  // Удалить
  delSection() {
    this.queryParam.sectionId = this.node.cSection.dataset.id || false;
    if (!this.queryParam.sectionId) { this.M.hide(); return; }

    this.M.show('Удалить раздел', 'Удалятся вложенные элементы');
    this.reloadAction = {
      dbAction : 'loadSection',
      sectionId: this.getParentSection(this.queryParam.sectionId),
      callback: data => this.appendSection(data['section']),
    };
  }
  // Открыть нижний уровень
  dbClickSection(e) {
    let target = e.target;
    !target.dataset.id && (target = target.closest(['data-id']));
    if (!target) return;

    target.classList.toggle('closeSection');
    target.classList.toggle('openSection');

    if (target.classList.contains('openSection')) {
      this.queryParam.dbAction  = 'loadSection';
      this.queryParam.sectionId = target.dataset.id;
      this.loadSection();
    }
  }

  // Bind events
  //--------------------------------------------------------------------------------------------------------------------

  onEvent() {
    this.node.main.addEventListener('click', e => this.commonEvent(e));
    this.node.main.addEventListener('dblclick', e => this.dbClickSection(e));
  }
}
