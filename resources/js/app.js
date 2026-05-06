import './bootstrap';
import { createApp } from 'vue';
import AppComponent from './components/AppComponent.vue';

const app = createApp({});
app.component('app-component', AppComponent);
app.mount('#app');
