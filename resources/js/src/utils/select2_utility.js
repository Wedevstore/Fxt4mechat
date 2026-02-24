// src/utils/select2_utility.js
import $ from 'jquery';
import 'select2';

export default {
  mounted(el) {
    $(el).select2();
  },
  unmounted(el) {
    $(el).select2('destroy');
  }
};
