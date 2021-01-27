require('./bootstrap');

window.Vue = require('vue');

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('v-header', require('./components/header').default);
Vue.component('v-footer', require('./components/footer').default);
Vue.component('v-spinner', require('./components/spinner').default);
Vue.mixin(require('./mixins/invalid').default)

import router from "./router";

const app = new Vue({
    el: '#app',
    router
});
