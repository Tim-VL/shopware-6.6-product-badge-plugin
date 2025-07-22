import './page/swag-product-badges-list';
import './page/swag-product-badges-detail';
import './component/swag-product-badges-assignment';

const { Module } = Shopware;

Module.register('swag-product-badges', {
    type: 'plugin',
    name: 'ProductBadges',
    title: 'swag-product-badges.general.mainMenuItemGeneral',
    description: 'swag-product-badges.general.descriptionTextModule',
    color: '#ff3d58',
    icon: 'regular-cog',

    routes: {
        index: {
            component: 'swag-product-badges-list',
            path: 'index',
            meta: {
                parentPath: 'sw.settings.index.plugins'
            }
        },
        detail: {
            component: 'swag-product-badges-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'swag.product.badges.index'
            }
        },
        create: {
            component: 'swag-product-badges-detail',
            path: 'create',
            meta: {
                parentPath: 'swag.product.badges.index'
            }
        }
    },
    settingsItem: [{
        name: 'swag-product-badges',
        label: 'swag-product-badges.general.mainMenuItemGeneral',
        to: 'swag.product.badges.index',
        icon: 'regular-cog',
        group: 'plugins'
    }],
    navigation: [{
        id: 'swag-product-badges',
        label: 'swag-product-badges.general.mainMenuItemGeneral',
        parent: 'sw-catalogue',
        path: 'swag.product.badges.index',
        position: 100,
        icon: 'regular-cog'
    }]
});
