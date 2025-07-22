const { Application } = Shopware;
import ProductBadgeApiService from '../core/product-badge-api.service';

Application.addServiceProvider('ProductBadgeApiService', (container) => {
    const initContainer = Application.getContainer('init');
    return new ProductBadgeApiService(initContainer.httpClient, container.loginService);
});
