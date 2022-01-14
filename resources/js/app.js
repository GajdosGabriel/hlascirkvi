/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

import Vue from 'vue';

//Autorizovanie pre canUpdate
Vue.prototype.authorize = function(handler) {
    let user = window.App.user;
    return user ? handler(user) : false;
};


////////////  ACL   /////////////////
// console.log(window.App.user);

import Auth from './Auth'
Vue.prototype.$auth = new Auth(window.App.user);
///////////////////////////////

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
Vue.component('radio-button', require('./navigation/RadioButton.vue').default);
Vue.component('video-item', require('./components/Video-Item.vue').default);
Vue.component('user-card', require('./users/User-card.vue').default);
Vue.component('organization-card', require('./organizations/Organization-card.vue').default);
Vue.component('organization-page-header', require('./organizations/Organization-page-header.vue').default);
Vue.component('login-card', require('./auth/LoginCard.vue').default);
Vue.component('youtube-dash', require('./youtube/YoutubeDash.vue').default);
Vue.component('swith-filter', require('./posts/Swith-filter.vue').default);
Vue.component('comments', require('./comments/Comments.vue').default);
Vue.component('comments-items', require('./comments/Reply.vue').default);
Vue.component('event-comments-look', require('./events/Event-comment-look.vue').default);
Vue.component('event-comments-offer', require('./events/Event-comment-offer.vue').default);
Vue.component('new-organization', require('./organizations/NewOrganization.vue').default);
Vue.component('ticket-form', require('./events/Ticket.vue').default);
Vue.component('event-info-panel', require('./events/EventInfoPanel.vue').default);
Vue.component('get-organization', require('./organizations/GetOrganization.vue').default);
Vue.component('news-rss', require('./components/News-rss_0.vue').default);
Vue.component('form-organization', require('./events/Form-org_0.vue').default);
Vue.component('big-thing', require('./bigThink/big-thing_0.vue').default);
Vue.component('prayers-card', require('./prayer/prayers-card.vue').default);
Vue.component('prayers-index-page', require('./prayer/prayers-index-page.vue').default);
Vue.component('prayers-index-page2', require('./prayer/prayers-index-page2.vue').default);
Vue.component('post-card', require('./posts/card/card.vue').default);
Vue.component('event-picture-viewer', require('./events/Event-picture-viewer.vue').default);
Vue.component('navigation-main', require('./navigation/Navigation-main.vue').default);
Vue.component('article-dropdown', require('./posts/Article-dropdown.vue').default);
Vue.component('event-dropdown', require('./events/Event-dropdown.vue').default);
Vue.component('c-article-dropdown', require('./components/c-article-dropdown.vue').default);
Vue.component('seminar-title', require('./seminars/seminar-title.vue').default);
Vue.component('seminar-info', require('./seminars/seminar-info.vue').default);
Vue.component('seminar-description', require('./seminars/seminar-description.vue').default);
Vue.component('comment-card', require('./comments/comments-card.vue').default);

// Vue.component('posts-card', require('./posts/card/posts.vue').default);
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
