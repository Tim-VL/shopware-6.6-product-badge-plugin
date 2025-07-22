import template from './swag-product-badges-assignment.html.twig';
import './swag-product-badges-assignment.scss';

const { Component, Mixin } = Shopware;
const { Criteria, EntityCollection } = Shopware.Data;

Component.register('swag-product-badges-assignment', {
    template,

    inject: [
        'repositoryFactory'
    ],

    mixins: [
        Mixin.getByName('notification')
    ],

    props: {
        badgeId: {
            type: String,
            required: true
        },
        currentBadge: {
            type: Object,
            required: false,
            default: null
        }
    },    

    data() {
        return {
            assignments: null,
            isLoading: false,
            assignmentRepository: null,
            productRepository: null,
            badgeRepository: null,
            showProductModal: false,
            showImageModal: false,
            productCollection: null,
            selectedImages: [],
            productMediaData: {},
            productCriteria: null,
            currentBadge: null,
            showBulkDeleteModal: false,
            selection: {},
            selectionCount: 0,
            page: 1,
            limit: 25,
            total: 0
        };
    },

    computed: {
        assignmentColumns() {
            return [{
                property: 'product.name',
                dataIndex: 'product.name',
                label: this.$t('swag-product-badges.assignment.columnProductName'),
                allowResize: true,
                primary: true
            }, {
                property: 'product.productNumber',
                dataIndex: 'product.productNumber',
                label: this.$t('swag-product-badges.assignment.columnProductNumber'),
                allowResize: true
            }, {
                property: 'media',
                dataIndex: 'media',
                label: this.$t('swag-product-badges.assignment.columnImage'),
                allowResize: true,
                sortable: false
            }];
        },

        selectedProducts() {
            return this.productCollection ? Array.from(this.productCollection) : [];
        },
        areAllImagesSelected() {
            if (!this.selectedProducts.length) return false;
            
            let totalImages = 0;
            for (const product of this.selectedProducts) {
                const productMedia = this.getProductMedia(product);
                totalImages += productMedia.length;
            }
            
            return totalImages > 0 && this.selectedImages.length === totalImages;
        }
    },

    async created() {
        this.assignmentRepository = this.repositoryFactory.create('swag_product_badge_product');
        this.productRepository = this.repositoryFactory.create('product');
        this.badgeRepository = this.repositoryFactory.create('swag_product_badge');

        // Initialize product collection
        this.createEmptyProductCollection();
        this.createProductCriteria();
        await this.validateBadgeAndLoad();
    },

    methods: {
        areAllProductImagesSelected(productId) {
            const product = this.selectedProducts.find(p => p.id === productId);
            if (!product) return false;
            
            const productMedia = this.getProductMedia(product);
            if (productMedia.length === 0) return false;
            
            const selectedForProduct = this.selectedImages.filter(item => item.productId === productId);
            return selectedForProduct.length === productMedia.length;
        },

        toggleAllImages() {
            if (this.areAllImagesSelected) {
                // Deselect all images
                this.selectedImages = [];
            } else {
                // Select all images
                this.selectedImages = [];
                for (const product of this.selectedProducts) {
                    const productMedia = this.getProductMedia(product);
                    for (const media of productMedia) {
                        this.selectedImages.push({
                            productId: product.id,
                            productVersionId: product.versionId,
                            mediaId: media.id
                        });
                    }
                }
            }
        },
        toggleAllProductImages(productId) {
            const product = this.selectedProducts.find(p => p.id === productId);
            if (!product) return;
            
            const productMedia = this.getProductMedia(product);
            const isAllSelected = this.areAllProductImagesSelected(productId);
            
            if (isAllSelected) {
                // Deselect all images for this product
                this.selectedImages = this.selectedImages.filter(item => item.productId !== productId);
            } else {
                // Remove any existing selections for this product first
                this.selectedImages = this.selectedImages.filter(item => item.productId !== productId);
                
                // Add all images for this product
                for (const media of productMedia) {
                    this.selectedImages.push({
                        productId: product.id,
                        productVersionId: product.versionId,
                        mediaId: media.id
                    });
                }
            }
        },
        createEmptyProductCollection() {
            this.productCollection = new EntityCollection(
                this.productRepository.route,
                this.productRepository.entityName,
                Shopware.Context.api
            );
        },

        clearProductCollection() {
            const itemsToRemove = Array.from(this.productCollection);
            itemsToRemove.forEach(item => {
                this.productCollection.remove(item.id);
            });
        },

        async validateBadgeAndLoad() {
            try {
                const criteria = new Criteria();
                criteria.addFilter(Criteria.equals('id', this.badgeId));
                const badge = await this.badgeRepository.search(criteria, Shopware.Context.api);
                
                if (!badge || badge.length === 0) {
                    console.error('Badge not found with ID:', this.badgeId);
                    this.createNotificationError({
                        title: 'Badge Not Found',
                        message: `Badge with ID ${this.badgeId} does not exist.`
                    });
                    return;
                }

                this.currentBadge = badge.first();
                this.getAssignments();

            } catch (error) {
                console.error('Error validating badge:', error);
                this.createNotificationError({
                    title: 'Validation Error',
                    message: 'Could not validate badge. Please check the console for details.'
                });
            }
        },
        
        onSelectionChanged(selection, selectionCount) {
            this.selection = selection;
            this.selectionCount = selectionCount;
        },
    
        onBulkDeleteButtonClick() {
            if (this.selectionCount === 0) {
                return;
            }
            this.showBulkDeleteModal = true;
        },
    
        onCloseBulkDeleteModal() {
            this.showBulkDeleteModal = false;
        },
    
        async onConfirmBulkDelete() {
            this.showBulkDeleteModal = false;
            
            try {
                const deletePromises = Object.keys(this.selection).map(id => 
                    this.onDeleteAssignment(id)
                );
                
                await Promise.all(deletePromises);
                
                // Reset selection state
                this.selection = {};
                this.selectionCount = 0;
                
                // Reset to first page and refresh
                this.page = 1;
                this.getAssignments();
                
                // Clear the data grid selection
                if (this.$refs.assignmentGrid) {
                    this.$refs.assignmentGrid.resetSelection();
                }
            } catch (error) {
                console.error('Error bulk deleting assignments:', error);
            }
        },

        onDeleteAssignment(id) {
            this.assignmentRepository
                .delete(id, Shopware.Context.api)
                .then(() => {
                    this.createNotificationSuccess({
                        title: this.$t('swag-product-badges.assignment.titleUnassignSuccess'),
                        message: this.$t('swag-product-badges.assignment.messageUnassignSuccess')
                    });
                    this.getAssignments();
                })
                .catch((error) => {
                    console.error('Delete assignment error:', error);
                    this.createNotificationError({
                        title: this.$t('swag-product-badges.assignment.titleUnassignError'),
                        message: this.$t('swag-product-badges.assignment.messageUnassignError')
                    });
                });
        },
        
        createProductCriteria() {
            this.productCriteria = new Criteria();
            this.productCriteria.addAssociation('media');
            
            // active products only
            this.productCriteria.addFilter(Criteria.equals('active', true));
            
            // sorting
            this.productCriteria.addSorting(Criteria.sort('name', 'ASC'));
        },

        onPageChange(opts) {
            this.page = opts.page;
            this.limit = opts.limit;
            this.getAssignments();
        },        

        onRefresh() {
            this.page = 1;
            this.getAssignments();
        },

        
        // getAssignments() {
        //     if (!this.currentBadge) {
        //         console.warn('Cannot load assignments - badge not validated yet');
        //         return;
        //     }
            
        //     this.isLoading = true;
        //     const criteria = new Criteria(this.page, this.limit);
        //     criteria.addFilter(Criteria.equals('swagProductBadgeId', this.badgeId));
        //     criteria.addAssociation('product');
        //     criteria.addAssociation('media');
            
        //     criteria.addSorting(Criteria.sort('createdAt', 'DESC'));
        
        //     this.assignmentRepository
        //         .search(criteria, Shopware.Context.api)
        //         .then((result) => {
        //             this.assignments = result;
        //             this.total = result.total;
        //         })
        //         .catch((error) => {
        //             console.error('Error loading assignments:', error);
        //             this.assignments = [];
        //             this.total = 0;
        //         })
        //         .finally(() => {
        //             this.isLoading = false;
        //         });
        // },

        async getAssignments() {
            if (!this.currentBadge) {
                console.warn('Cannot load assignments - badge not validated yet');
                return;
            }
            
            this.isLoading = true;
            const criteria = new Criteria(this.page, this.limit);
            criteria.addFilter(Criteria.equals('swagProductBadgeId', this.badgeId));
            criteria.addAssociation('product');
            criteria.addAssociation('media');
            
            criteria.addSorting(Criteria.sort('createdAt', 'DESC'));
        
            try {
                const result = await this.assignmentRepository.search(criteria, Shopware.Context.api);
                
               
                if (result && result.length > 0) {
                    const parentIdsToFetch = new Set();
                    
                    result.forEach(assignment => {
                        if (assignment.product && assignment.product.parentId && 
                            (!assignment.product.name || assignment.product.name.trim() === '')) {
                            parentIdsToFetch.add(assignment.product.parentId);
                        }
                    });
                    
                    let parentProducts = {};
                    if (parentIdsToFetch.size > 0) {
                        const parentCriteria = new Criteria();
                        parentCriteria.addFilter(Criteria.equalsAny('id', Array.from(parentIdsToFetch)));
                        
                        const parentResult = await this.productRepository.search(parentCriteria, Shopware.Context.api);
                        
                        parentResult.forEach(parent => {
                            parentProducts[parent.id] = parent;
                        });
                    }
                    
                    result.forEach(assignment => {
                        if (assignment.product && assignment.product.parentId && 
                            (!assignment.product.name || assignment.product.name.trim() === '')) {
                            
                            const parentProduct = parentProducts[assignment.product.parentId];
                            if (parentProduct) {
                                assignment.product.name = parentProduct.name || '';
                                
                                if (!assignment.product.translated) {
                                    assignment.product.translated = {};
                                }
                                
                                if (parentProduct.translated && parentProduct.translated.name) {
                                    assignment.product.translated.name = parentProduct.translated.name;
                                } else if (parentProduct.name) {
                                    assignment.product.translated.name = parentProduct.name;
                                }
                            }
                        }
                    });
                }
                
                this.assignments = result;
                this.total = result.total;
            } catch (error) {
                console.error('Error loading assignments:', error);
                this.assignments = [];
                this.total = 0;
            } finally {
                this.isLoading = false;
            }
        },        

        onAddProducts() {
            if (!this.currentBadge) {
                this.createNotificationError({
                    title: 'Badge Not Ready',
                    message: 'Badge validation is still in progress. Please wait.'
                });
                return;
            }

            this.clearProductCollection();
            this.showProductModal = true;
        },

        onCloseProductModal() {
            this.showProductModal = false;
            this.clearProductCollection();
        },

        onUpdateProductCollection(collection) {
            this.productCollection = collection;
        },

        async onShowImageSelection() {
            if (!this.productCollection || this.productCollection.length === 0) {
                console.warn('No products selected');
                this.createNotificationWarning({
                    title: this.$t('swag-product-badges.assignment.titleNoSelection'),
                    message: this.$t('swag-product-badges.assignment.messageNoSelection') || 'Please select at least one product'
                });
                return;
            }

            await this.loadProductMedia();
            this.showProductModal = false;
            this.showImageModal = true;
        },
        
        async loadProductMedia() {
            if (!this.productCollection || this.productCollection.length === 0) {
                console.warn('No selected products to load media for');
                return;
            }
        
            const assignmentCriteria = new Criteria();
            assignmentCriteria.addAssociation('media');
            const allAssignments = await this.assignmentRepository.search(assignmentCriteria, Shopware.Context.api);
        
            // Filter out only combinations assigned to THIS specific badge
            const thisAssignedCombinations = new Set();
            allAssignments.forEach(assignment => {
                if (assignment.productId && assignment.mediaId && assignment.swagProductBadgeId === this.badgeId) {
                    thisAssignedCombinations.add(`${assignment.productId}-${assignment.mediaId}`);
                }
            });
        
            const productIds = Array.from(this.productCollection).map(product => product.id);
            
            const parentIdsToFetch = new Set();
            Array.from(this.productCollection).forEach(product => {
                if (product.parentId && (!product.name || product.name.trim() === '')) {
                    parentIdsToFetch.add(product.parentId);
                }
            });
            
            let parentProducts = {};
            if (parentIdsToFetch.size > 0) {
                const parentCriteria = new Criteria();
                parentCriteria.addFilter(Criteria.equalsAny('id', Array.from(parentIdsToFetch)));
                
                const parentResult = await this.productRepository.search(parentCriteria, Shopware.Context.api);
                
                parentResult.forEach(parent => {
                    parentProducts[parent.id] = parent;
                });
            }
            
            Array.from(this.productCollection).forEach(product => {
                if (product.parentId && (!product.name || product.name.trim() === '') && parentProducts[product.parentId]) {
                    const parentProduct = parentProducts[product.parentId];
                    product.name = parentProduct.name || '';
                    
                    if (!product.translated) {
                        product.translated = {};
                    }
                    
                    if (parentProduct.translated && parentProduct.translated.name) {
                        product.translated.name = parentProduct.translated.name;
                    } else if (parentProduct.name) {
                        product.translated.name = parentProduct.name;
                    }
                }
            });
        
            for (const productId of productIds) {
                try {
                    const criteria = new Criteria();
                    criteria.addAssociation('media');
                    
                    const product = await this.productRepository.get(productId, Shopware.Context.api, criteria);
                    
                    let mediaArray = [];
                    let hasOwnMedia = false;
                    
                    if (product && product.media) {
                        if (Array.isArray(product.media)) {
                            mediaArray = product.media;
                        } else if (product.media.elements) {
                            mediaArray = Object.values(product.media.elements);
                        } else if (typeof product.media === 'object') {
                            mediaArray = Object.values(product.media);
                        }
                        
                        hasOwnMedia = mediaArray.length > 0;
                    }
                    
                    if (!hasOwnMedia && product && product.parentId) {
                        try {
                            const parentCriteria = new Criteria();
                            parentCriteria.addAssociation('media');
                            
                            const parentProduct = await this.productRepository.get(product.parentId, Shopware.Context.api, parentCriteria);
                            
                            if (parentProduct && parentProduct.media) {
                                if (Array.isArray(parentProduct.media)) {
                                    mediaArray = parentProduct.media;
                                } else if (parentProduct.media.elements) {
                                    mediaArray = Object.values(parentProduct.media.elements);
                                } else if (typeof parentProduct.media === 'object') {
                                    mediaArray = Object.values(parentProduct.media);
                                }
                            }
                        } catch (parentError) {
                            console.warn('Error loading parent product media for product:', productId, parentError);
                        }
                    }
                    
                    const unassignedMedia = [];
                    for (const productMedia of mediaArray) {
                        if (productMedia && productMedia.media && productMedia.media.id) {
                            const combination = `${productId}-${productMedia.media.id}`;
                            // Only exclude if this specific badge-image combination already exists
                            if (!thisAssignedCombinations.has(combination)) {
                                unassignedMedia.push({
                                    id: productMedia.media.id,
                                    fileName: productMedia.media.fileName || 'Unknown',
                                    url: productMedia.media.url || '',
                                    alt: productMedia.media.alt || ''
                                });
                            }
                        }
                    }
                    
                    this.productMediaData[productId] = unassignedMedia;
                } catch (error) {
                    console.error('Error loading media for product:', productId, error);
                    this.productMediaData[productId] = [];
                }
            }
        },

        getProductMedia(product) {
            return this.productMediaData[product.id] || [];
        },

        onCloseImageModal() {
            this.showImageModal = false;
            this.selectedImages = [];
            this.productMediaData = {};
        },

        onBackToProductSelection() {
            this.showImageModal = false;
            this.showProductModal = true;
            this.selectedImages = [];
        },

        isImageSelected(productId, mediaId) {
            return this.selectedImages.some(item =>
                item.productId === productId && item.mediaId === mediaId
            );
        },

        toggleImageSelection(productId, mediaId) {
            const index = this.selectedImages.findIndex(item =>
                item.productId === productId && item.mediaId === mediaId
            );

            if (index > -1) {
                this.selectedImages.splice(index, 1);
            } else {
                const product = this.selectedProducts.find(p => p.id === productId);
                if (product) {
                    this.selectedImages.push({
                        productId: productId,
                        productVersionId: product.versionId,
                        mediaId: mediaId
                    });
                }
            }
        },

        async onAssignProductsWithImages() {
            if (!this.currentBadge) {
                this.createNotificationError({
                    title: 'Badge Not Ready',
                    message: 'Badge validation failed. Cannot create assignments.'
                });
                return;
            }
        
            if (this.selectedImages.length === 0) {
                this.createNotificationWarning({
                    title: this.$t('swag-product-badges.assignment.titleNoImageSelection'),
                    message: this.$t('swag-product-badges.assignment.messageNoImageSelection') || 'Please select at least one image'
                });
                return;
            }
        
            // Validate and create assignments
            const validatedAssignments = [];
        
            for (const item of this.selectedImages) {
                if (!item.productId) {
                    console.error('Missing productId for assignment:', item);
                    continue;
                }
        
                let mediaId = null;
                if (item.mediaId) {
                    try {
                        const mediaRepository = this.repositoryFactory.create('media');
                        const media = await mediaRepository.get(item.mediaId, Shopware.Context.api);
                        if (media) {
                            mediaId = item.mediaId;
                        } else {
                            console.warn('Media not found, setting to null:', item.mediaId);
                        }
                    } catch (error) {
                        console.warn('Media validation failed, setting to null:', item.mediaId, error);
                    }
                }
        
                const assignment = {
                    productId: item.productId,
                    productVersionId: item.productVersionId || 'live',
                    swagProductBadgeId: this.badgeId,
                    mediaId: mediaId 
                };
        
                validatedAssignments.push(assignment);
            }
        
            if (validatedAssignments.length === 0) {
                this.createNotificationError({
                    title: 'Validation Error',
                    message: 'No valid assignments could be created. Please check your selection.'
                });
                return;
            }
        
            this.createAssignmentsSequentially(validatedAssignments);
        },        

        async createAssignmentsSequentially(assignments) {
            const successfulAssignments = [];
            const failedAssignments = [];
            
            for (const assignmentData of assignments) {
                try {
                    // Checks if assignment already exists to prevent duplicates
                    const existingCriteria = new Criteria();
                    existingCriteria.addFilter(Criteria.equals('productId', assignmentData.productId));
                    existingCriteria.addFilter(Criteria.equals('swagProductBadgeId', assignmentData.swagProductBadgeId));
                    
                    if (assignmentData.mediaId) {
                        existingCriteria.addFilter(Criteria.equals('mediaId', assignmentData.mediaId));
                    } else {
                        existingCriteria.addFilter(Criteria.equals('mediaId', null));
                    }
                    
                    const existingAssignments = await this.assignmentRepository.search(existingCriteria, Shopware.Context.api);
                    
                    if (existingAssignments.length > 0) {
                        failedAssignments.push({ assignment: assignmentData, error: 'Assignment already exists' });
                        continue;
                    }
                    
                    const assignment = this.assignmentRepository.create(Shopware.Context.api);
                    
                    assignment.productId = assignmentData.productId;
                    assignment.productVersionId = assignmentData.productVersionId;
                    assignment.swagProductBadgeId = assignmentData.swag;
                    assignment.productId = assignmentData.productId;
                    assignment.productVersionId = assignmentData.productVersionId;
                    assignment.swagProductBadgeId = assignmentData.swagProductBadgeId;
                    assignment.mediaId = assignmentData.mediaId;
                    
                    await this.assignmentRepository.save(assignment, Shopware.Context.api);
                    
                    successfulAssignments.push(assignmentData);
                    
                } catch (error) {
                    console.error('Failed to create assignment:', assignmentData, error);
                    failedAssignments.push({ assignment: assignmentData, error: error });
                }
            }
            
            if (successfulAssignments.length > 0) {
                this.createNotificationSuccess({
                    title: this.$t('swag-product-badges.assignment.titleAssignSuccess'),
                    message: this.$t('swag-product-badges.assignment.descriptionAssignSuccess') + `${successfulAssignments.length} product(s) to badge.`
                });
                this.getAssignments(); 
            }
            
            if (failedAssignments.length > 0) {
                console.error('Failed assignments details:', failedAssignments);
                this.createNotificationError({
                    title: this.$t('swag-product-badges.assignment.titleAssignError'),
                    message: this.$t('swag-product-badges.assignment.descriptionAssignError') 
                });
            }
            
            if (successfulAssignments.length > 0) {
                this.onCloseImageModal();
                this.onCloseProductModal();
            }
        },

        onAssignProducts() {
            if (!this.currentBadge) {
                this.createNotificationError({
                    title: 'Badge Not Ready',
                    message: 'Badge validation failed. Cannot create assignments.'
                });
                return;
            }
            
            if (!this.productCollection || this.productCollection.length === 0) {
                this.createNotificationWarning({
                    title: this.$t('swag-product-badges.assignment.titleNoSelection'),
                    message: this.$t('swag-product-badges.assignment.messageNoSelection') || 'Please select at least one product'
                });
                return;
            }
        
            const assignments = Array.from(this.productCollection).map(product => ({
                productId: product.id,
                productVersionId: product.versionId,
                swagProductBadgeId: this.badgeId,
                mediaId: null // No specific image
            }));
        
            this.createAssignmentsSequentially(assignments);
        },
        
        onDeleteAssignment(id) {
            this.assignmentRepository
                .delete(id, Shopware.Context.api)
                .then(() => {
                    this.createNotificationSuccess({
                        title: this.$t('swag-product-badges.assignment.titleUnassignSuccess'),
                        message: this.$t('swag-product-badges.assignment.messageUnassignSuccess')
                    });
                    this.getAssignments();
                })
                .catch((error) => {
                    console.error('Delete assignment error:', error);
                    this.createNotificationError({
                        title: this.$t('swag-product-badges.assignment.titleUnassignError'),
                        message: this.$t('swag-product-badges.assignment.messageUnassignError')
                    });
                });
        }
    }
});
