/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

////Autorizovanie pre canUpdate
Vue.prototype.authorize = function(handler) {
    let user = window.App.user;
    return user ? handler(user) : false;
};

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

//Vue.component('example-component', require('./components/ExampleComponent.vue').default);


Vue.component('messenger-modul', require('./messenger/Messenger-modul.vue').default);
Vue.component('favorite-post', require('./posts/Favorite-post.vue').default);
Vue.component('notification', require('./components/Notification.vue').default);
Vue.component('video-item', require('./components/Video-Item.vue').default);
Vue.component('user-card', require('./users/User-card.vue').default);
Vue.component('organization-card', require('./organizations/Organization-card.vue').default);
Vue.component('page-header', require('./organizations/Favorite-header.vue').default);
Vue.component('bell', require('./users/Bell.vue').default);
Vue.component('login-0', require('./users/Login/Login-0.vue').default);
Vue.component('youtube-dash', require('./youtube/YoutubeDash.vue').default);
Vue.component('help-us', require('./posts/Help-us.vue').default);
Vue.component('swith-filter', require('./posts/Swith-filter.vue').default);
Vue.component('article-admin', require('./posts/Article-admin.vue').default);
Vue.component('replies', require('./comments/Replies.vue').default);
Vue.component('event-comments-look', require('./events/Event-comment-look.vue').default);
Vue.component('event-comments-offer', require('./events/Event-comment-offer.vue').default);
Vue.component('new-organization', require('./organizations/NewOrganization.vue').default);
Vue.component('ticket-form', require('./events/Ticket.vue').default);
Vue.component('event-info-panel', require('./events/EventInfoPanel.vue').default);
Vue.component('admin-modal', require('./components/Modal.vue').default);
Vue.component('get-organization', require('./organizations/GetOrganization.vue').default);
Vue.component('news-rss', require('./components/News-rss_0.vue').default);
Vue.component('form-organization', require('./events/Form-org_0.vue').default);
Vue.component('big-thing', require('./bigThink/big-thing_0.vue').default);
Vue.component('prayer-card', require('./prayer/prayer-card.vue').default);
Vue.component('new-prayer-index-page', require('./prayer/NewPrayerIndexPage.vue').default);
// Vue.component('post-counter', require('./posts/Video-counter.vue').default);


export const bus = new Vue();

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
});
