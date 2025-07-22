import './init/api-service.init';
import './module/swag-product-badges';

import './page/sw-product-detail';
import './view/sw-product-detail-badges';

Shopware.Module.register('sw-product-detail-badges', {
 
  routeMiddleware(next, currentRoute) {
    if (currentRoute.name === 'sw.product.detail') {
      currentRoute.children.push({
        name: 'sw.product.detail.badges',
        path: '/sw/product/detail/:id/badges',
        component: 'sw-product-detail-badges',
        meta: {
          parentPath: 'sw.product.index'
        }
      });
    }
    next(currentRoute);
  }
});



