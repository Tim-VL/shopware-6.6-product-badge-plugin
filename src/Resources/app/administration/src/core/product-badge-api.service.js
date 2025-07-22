const { ApiService } = Shopware.Classes;

class ProductBadgeApiService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'product-badge') {
        super(httpClient, loginService, apiEndpoint);
    }

    getBadgesForProduct(productId) {
        return this.httpClient
            .get(`${this.getApiBasePath()}/product/${productId}`, {
                headers: this.getBasicHeaders()
            })
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }

    // Get all product badges
    getBadges(criteria = null) {
        const apiRoute = `${this.getApiBasePath()}`;
        const headers = this.getBasicHeaders();
        
        let url = apiRoute;
        if (criteria) {
            const params = new URLSearchParams();
            Object.keys(criteria).forEach(key => {
                if (criteria[key] !== null && criteria[key] !== undefined) {
                    params.append(key, criteria[key]);
                }
            });
            if (params.toString()) {
                url += '?' + params.toString();
            }
        }

        return this.httpClient.get(url, { headers }).then((response) => {
            return ApiService.handleResponse(response);
        });
    }

    // Get single product badge
    getBadge(id) {
        const apiRoute = `${this.getApiBasePath()}/${id}`;
        const headers = this.getBasicHeaders();
        
        return this.httpClient.get(apiRoute, { headers }).then((response) => {
            return ApiService.handleResponse(response);
        });
    }

    // Create product badge
    createBadge(data) {
        const apiRoute = `${this.getApiBasePath()}`;
        const headers = this.getBasicHeaders();
        
        return this.httpClient.post(apiRoute, data, { headers }).then((response) => {
            return ApiService.handleResponse(response);
        });
    }

    // Update product badge
    updateBadge(id, data) {
        const apiRoute = `${this.getApiBasePath()}/${id}`;
        const headers = this.getBasicHeaders();
        
        return this.httpClient.patch(apiRoute, data, { headers }).then((response) => {
            return ApiService.handleResponse(response);
        });
    }

    // Delete product badge
    deleteBadge(id) {
        const apiRoute = `${this.getApiBasePath()}/${id}`;
        const headers = this.getBasicHeaders();
        
        return this.httpClient.delete(apiRoute, { headers }).then((response) => {
            return ApiService.handleResponse(response);
        });
    }

    // Assign badge to product
    assignBadgeToProduct(productId, productVersionId, badgeId, mediaId = null) {
        const apiRoute = `${this.getApiBasePath()}/assign`;
        const headers = this.getBasicHeaders();
        
        return this.httpClient.post(
            apiRoute, 
            {
                productId: productId,
                productVersionId: productVersionId,
                swagProductBadgeId: badgeId,
                mediaId: mediaId
            }, 
            { headers }
        ).then((response) => {
            return ApiService.handleResponse(response);
        });
    }

    // Unassign badge from product
    unassignBadgeFromProduct(assignmentId) {
        const apiRoute = `${this.getApiBasePath()}/unassign/${assignmentId}`;
        const headers = this.getBasicHeaders();
        
        return this.httpClient.delete(apiRoute, { headers }).then((response) => {
            return ApiService.handleResponse(response);
        });
    }

    // Get product badges for a specific product
    getProductBadges(productId) {
        const apiRoute = `/api/product/${productId}/badges`;
        const headers = this.getBasicHeaders();
        
        return this.httpClient.get(apiRoute, { headers }).then((response) => {
            return ApiService.handleResponse(response);
        });
    }

    // Get products assigned to a specific badge
    getBadgeProducts(badgeId) {
        const apiRoute = `${this.getApiBasePath()}/${badgeId}/products`;
        const headers = this.getBasicHeaders();
        
        return this.httpClient.get(apiRoute, { headers }).then((response) => {
            return ApiService.handleResponse(response);
        });
    }

    // Bulk assign badges to products
    bulkAssignBadges(assignments) {
        const apiRoute = `${this.getApiBasePath()}/bulk-assign`;
        const headers = this.getBasicHeaders();
        
        return this.httpClient.post(
            apiRoute, 
            {
                assignments: assignments
            }, 
            { headers }
        ).then((response) => {
            return ApiService.handleResponse(response);
        });
    }

    // Search badges with filters
    searchBadges(filters = {}, pagination = {}) {
        const apiRoute = `${this.getApiBasePath()}`;
        const headers = this.getBasicHeaders();
        
        let url = apiRoute;
        const params = new URLSearchParams();
        
        if (filters.active !== undefined) {
            params.append('active', filters.active);
        }
        if (filters.name) {
            params.append('name', filters.name);
        }
        
        // pagination parameters
        if (pagination.page) {
            params.append('page', pagination.page);
        }
        if (pagination.limit) {
            params.append('limit', pagination.limit);
        }
        if (pagination.sortBy) {
            params.append('sortBy', pagination.sortBy);
        }
        if (pagination.sortDirection) {
            params.append('sortDirection', pagination.sortDirection);
        }
        
        if (params.toString()) {
            url += '?' + params.toString();
        }
    
        return this.httpClient.get(url, { headers }).then((response) => {
            return ApiService.handleResponse(response);
        });
    }

    // Add this method to handle removal by product and badge ID
    removeBadgeFromProduct(productId, badgeId, productImageId = null) {
        const apiRoute = `${this.getApiBasePath()}/remove-from-product`;
        const headers = this.getBasicHeaders();
        
        return this.httpClient.delete(apiRoute, {
            data: {
                productId: productId,
                badgeId: badgeId,
                productImageId: productImageId
            },
            headers
        }).then((response) => {
            return ApiService.handleResponse(response);
        });
    }

    
}

export default ProductBadgeApiService;
