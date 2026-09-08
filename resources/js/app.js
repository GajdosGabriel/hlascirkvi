/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import './bootstrap';
import { initCopyLink } from './copyLink';
import { initArticle } from './article';

// Prehliadač obrázkov na čiernej ploche; visí na `document`, žiadne volanie netreba.
import './lightbox';

import Vue from 'vue';

import Auth from './Auth';

//Autorizovanie pre canUpdate
Vue.prototype.authorize = function(handler) {
    let user = window.App.user;
    return user ? handler(user) : false;
};


////////////  ACL   /////////////////
// console.log(window.App.user);

Vue.prototype.$auth = new Auth(window.App.user);
///////////////////////////////

/**
 * Global components. Vite resolves these at build time, so they are listed as
 * static imports rather than the require() calls Laravel Mix used.
 */

import FavoritePost from './posts/Favorite-post.vue';
import Notification from './components/Notification.vue';
import RadioButton from './navigation/RadioButton.vue';
import VideoItem from './components/Video-Item.vue';
import UserCard from './users/User-card.vue';
import OrganizationCard from './organizations/Organization-card.vue';
import OrganizationPageHeader from './organizations/Organization-page-header.vue';
import LoginCard from './auth/LoginCard.vue';
import YoutubeDash from './youtube/YoutubeDash.vue';
import Comments from './comments/Comments.vue';
import CommentItem from './comments/Comment-Item.vue';
import NewOrganization from './organizations/NewOrganization.vue';
import PrayersCard from './prayer/prayers-card.vue';
import PrayersIndexPage from './prayer/prayers-index-page.vue';
import PrayersIndexPage2 from './prayer/prayers-index-page2.vue';
import NewPrayerButton from './prayer/components/NewPrayerButton.vue';
import PostPublishButtons from './posts/card/buttons.vue';
import PictureViewer from './posts/Picture-viewer.vue';
import NavigationMain from './navigation/Navigation-main.vue';
import MobileMenu from './navigation/MobileMenu.vue';
import ArticleDropdown from './posts/Article-dropdown.vue';
import CArticleDropdown from './components/c-article-dropdown.vue';
import SeminarTitle from './seminars/seminar-title.vue';
import SeminarInfo from './seminars/seminar-info.vue';
import SeminarDescription from './seminars/seminar-description.vue';
import CommentsCard from './comments/comments-card.vue';
import DropdownSlot from './components/DropdownSlot.vue';

Vue.component('favorite-post', FavoritePost);
Vue.component('notification', Notification);
Vue.component('radio-button', RadioButton);
Vue.component('video-item', VideoItem);
Vue.component('user-card', UserCard);
Vue.component('organization-card', OrganizationCard);
Vue.component('organization-page-header', OrganizationPageHeader);
Vue.component('login-card', LoginCard);
Vue.component('youtube-dash', YoutubeDash);
Vue.component('comments-post', Comments);
Vue.component('comment-item', CommentItem);
Vue.component('new-organization', NewOrganization);
Vue.component('prayers-card', PrayersCard);
Vue.component('prayers-index-page', PrayersIndexPage);
Vue.component('prayers-index-page2', PrayersIndexPage2);
Vue.component('new-prayer-button', NewPrayerButton);
Vue.component('post-publish-buttons', PostPublishButtons);
Vue.component('picture-viewer', PictureViewer);
Vue.component('navigation-main', NavigationMain);
Vue.component('mobile-menu', MobileMenu);
Vue.component('article-dropdown', ArticleDropdown);
Vue.component('c-article-dropdown', CArticleDropdown);
Vue.component('seminar-title', SeminarTitle);
Vue.component('seminar-info', SeminarInfo);
Vue.component('seminar-description', SeminarDescription);
Vue.component('comments-card', CommentsCard);
Vue.component('dropdown-slot', DropdownSlot);


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

/*
 * Vue pri pripojení prekreslí celý #app, čiže zahodí pôvodné uzly aj
 * s poslucháčmi, ktoré na ne stihli pripnúť skripty v šablónach (tie bežia
 * počas parsovania, teda pred týmto modulom). Šablóny sa preto vešajú na
 * túto udalosť a spúšťajú sa až nad hotovým stromom.
 */
document.dispatchEvent(new CustomEvent('app:ready'));

// Až za mountom Vue — poslucháče sa vešajú na uzly, ktoré zostanú v strome.
initCopyLink();

// Obe časti sa samy ukončia, keď ich uzly na stránke nie sú.
initArticle();
