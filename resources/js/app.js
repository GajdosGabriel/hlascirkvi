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

import { createApp } from 'vue';

import Auth from './Auth';

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

const app = createApp({});

app.config.globalProperties.authorize = function (handler) {
    const user = window.App.user;
    return user ? handler(user) : false;
};
app.config.globalProperties.$auth = new Auth(window.App.user);

const components = {
    'favorite-post': FavoritePost,
    notification: Notification,
    'radio-button': RadioButton,
    'video-item': VideoItem,
    'user-card': UserCard,
    'organization-card': OrganizationCard,
    'organization-page-header': OrganizationPageHeader,
    'youtube-dash': YoutubeDash,
    'comments-post': Comments,
    'comment-item': CommentItem,
    'new-organization': NewOrganization,
    'prayers-card': PrayersCard,
    'prayers-index-page': PrayersIndexPage,
    'prayers-index-page2': PrayersIndexPage2,
    'new-prayer-button': NewPrayerButton,
    'post-publish-buttons': PostPublishButtons,
    'picture-viewer': PictureViewer,
    'navigation-main': NavigationMain,
    'mobile-menu': MobileMenu,
    'article-dropdown': ArticleDropdown,
    'c-article-dropdown': CArticleDropdown,
    'seminar-title': SeminarTitle,
    'seminar-info': SeminarInfo,
    'seminar-description': SeminarDescription,
    'comments-card': CommentsCard,
    'dropdown-slot': DropdownSlot,
};

Object.entries(components).forEach(([name, component]) => app.component(name, component));
app.mount('#app');

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
