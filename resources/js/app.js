import './bootstrap';
import { createApp } from 'vue';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// Import all Vue components
import DashboardApp from './components/dashboard/DashboardApp.vue';
import DemoApp from './components/demo/DemoApp.vue';
import StoresApp from './components/stores/StoresApp.vue';
import ProductsApp from './components/products/ProductsApp.vue';
import ChannelsApp from './components/channels/ChannelsApp.vue';
import CampaignsApp from './components/campaigns/CampaignsApp.vue';
import ListingsApp from './components/listings/ListingsApp.vue';

// Mount each component if its mount point exists in the DOM
const mounts = {
    '#demo-app':      DemoApp,
    '#dashboard-app': DashboardApp,
    '#stores-app': StoresApp,
    '#products-app': ProductsApp,
    '#channels-app': ChannelsApp,
    '#campaigns-app': CampaignsApp,
    '#listings-app': ListingsApp,
};

Object.entries(mounts).forEach(([selector, Component]) => {
    const el = document.querySelector(selector);
    if (el) createApp(Component).mount(el);
});
