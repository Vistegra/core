'use strict';

import 'primevue/resources/themes/saga-blue/theme.css';
import 'primevue/resources/primevue.css';

import { createApp } from 'vue';
import PrimeVue from 'primevue/config';

import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import ToggleButton from 'primevue/togglebutton';
import Checkbox from 'primevue/checkbox';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import InputNumber from 'primevue/inputnumber';
import Dropdown from 'primevue/dropdown';
import MultiSelect from 'primevue/multiselect';
import TreeSelect from 'primevue/treeselect';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
//import FileUpload from 'primevue/fileupload';
import Calendar from 'primevue/calendar';

import Image from 'primevue/image';

import Tooltip from 'primevue/tooltip';


import App from './app';

const app = createApp(App);
app.use(PrimeVue);
app.component('p-dialog', Dialog);
app.component('p-button', Button);
app.component('p-toggle-button', ToggleButton);
app.component('p-checkbox', Checkbox);
app.component('p-input-text', InputText);
app.component('p-textarea', Textarea);
app.component('p-input-number', InputNumber);
app.component('p-select', Dropdown);
app.component('p-multi-select', MultiSelect);
app.component('p-tree-select', TreeSelect);
app.component('p-table', DataTable);
app.component('p-t-column', Column);
//app.component('p-file', FileUpload);
app.component('p-calendar', Calendar);


app.component('p-image', Image);

app.directive('tooltip', Tooltip);

/*[{'m-button': MyButton}]
 .forEach(([component, param]) => {
 app.component(component, param);
 });*/

app.config.errorHandler = (err, vm, info) => {
  debugger
  console.error(err, 'error', false);
  console.error(info, 'error', false);
  // обработка ошибки
  // `info` — специфическая для Vue информация об ошибке,
  // например, в каком хуке жизненного цикла была найдена ошибка
}

app.mount('#catalogForm');

export const catalog = {init() {}};
