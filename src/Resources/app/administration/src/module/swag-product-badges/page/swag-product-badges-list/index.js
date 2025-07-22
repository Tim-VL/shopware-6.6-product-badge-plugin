import template from './swag-product-badges-list.html.twig';
import './swag-product-badges-list.scss';

const { Component, Mixin } = Shopware;

Component.register('swag-product-badges-list', {
    template,

    inject: [
        'ProductBadgeApiService',
        'acl'
    ],

    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            badges: null,
            items: null,
            isLoading: true,
            sortBy: 'name',
            sortDirection: 'ASC',
            naturalSorting: true,
            showDeleteModal: false,
            showBulkDeleteModal: false, 
            total: 0,
            term: '',
            page: 1,
            limit: 25,
            selection: {},
            selectionCount: 0
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        columns() {
            return [{
                property: 'name',
                dataIndex: 'name',
                label: this.$t('swag-product-badges.list.columnName'),
                routerLink: 'swag.product.badges.detail',
                inlineEdit: 'string',
                allowResize: true,
                primary: true,
                sortable: false
            }, {
                property: 'label',
                dataIndex: 'label',
                label: this.$t('swag-product-badges.list.columnLabel'),
                allowResize: true,
                sortable: false
            }, {
                property: 'image',
                dataIndex: 'image',
                label: this.$t('swag-product-badges.list.columnImage'),
                allowResize: true,
                sortable: false
            }, {
                property: 'active',
                dataIndex: 'active',
                label: this.$t('swag-product-badges.list.columnActive'),
                inlineEdit: 'boolean',
                allowResize: true,
                align: 'center',
                sortable: false
            }, {
                property: 'createdAt',
                dataIndex: 'createdAt',
                label: this.$t('swag-product-badges.list.columnCreatedAt'),
                allowResize: true,
                sortable: false
            }];
        },
        dateFilter() {
            return Shopware.Filter.getByName('date');
        }
    },

    created() {
        
        this.getList();
    },

    methods: {
        async getList() {
            this.isLoading = true;
            
            try {
                const filters = {};
                if (this.term) {
                    filters.name = this.term;
                }
        
                const paginationParams = {
                    page: this.page,
                    limit: this.limit,
                    sortBy: this.sortBy,
                    sortDirection: this.sortDirection
                };
        
                const result = await this.ProductBadgeApiService.searchBadges(filters, paginationParams);
                
                if (result && result.data) {
                    this.items = result.data;
                    this.total = result.meta?.total || 0;
                } else {
                    this.items = [];
                    this.total = 0;
                }
                
            } catch (error) {
                console.error('Error loading badges via API:', error);
                this.createNotificationError({
                    title: this.$t('swag-product-badges.list.titleLoadError') || 'Loading Error',
                    message: this.$t('swag-product-badges.list.messageLoadError') || 'Could not load badges'
                });
                this.items = [];
                this.total = 0;
            } finally {
                this.isLoading = false;
            }
        },           

        onSearch(term) {
            this.term = term;
            this.selection = {};
            this.selectedItems = [];
            this.getList();
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
                    this.ProductBadgeApiService.deleteBadge(id)
                );
                
                await Promise.all(deletePromises);
                
                this.createNotificationSuccess({
                    title: this.$t('swag-product-badges.list.titleBulkDeleteSuccess'),
                    message: this.$t('swag-product-badges.list.messageBulkDeleteSuccess')
                });
                
                // Reset selection state
                this.selection = {};
                this.selectionCount = 0;
                
                // Clear the data grid selection
                if (this.$refs.dataGrid) {
                    this.$refs.dataGrid.resetSelection();
                }
                
                await this.getList();
            } catch (error) {
                console.error('Error bulk deleting badges:', error);
                this.createNotificationError({
                    title: this.$t('swag-product-badges.list.titleBulkDeleteError'),
                    message: this.$t('swag-product-badges.list.messageBulkDeleteError')
                });
            }
        },

        onPageChange(data) {
            this.page = data.page;
            this.limit = data.limit;
            this.getList();
        },
        
        onSortColumn(data) {
            this.sortBy = data.sortBy;
            this.sortDirection = data.sortDirection;
            this.naturalSorting = data.naturalSorting;
            this.getList();
        },

        onChangeLanguage() {
            this.getList();
        },

        onRefresh() {
            this.getList();
        },

        async onDeleteBadge(badgeId) {
            try {
                await this.ProductBadgeApiService.deleteBadge(badgeId);
                this.createNotificationSuccess({
                    title: this.$t('swag-product-badges.list.titleDeleteSuccess') || 'Success',
                    message: this.$t('swag-product-badges.list.messageDeleteSuccess') || 'Badge deleted successfully'
                });
                await this.getList();
            } catch (error) {
                console.error('Error deleting badge:', error);
                this.createNotificationError({
                    title: this.$t('swag-product-badges.list.titleDeleteError') || 'Error',
                    message: this.$t('swag-product-badges.list.messageDeleteError') || 'Could not delete badge'
                });
            }
        },

        async createBadgeViaApi(badgeData) {
            try {
                await this.ProductBadgeApiService.createBadge(badgeData);
                this.createNotificationSuccess({
                    title: 'Success',
                    message: 'Badge created successfully'
                });
                await this.getList();
            } catch (error) {
                console.error('Error creating badge:', error);
                this.createNotificationError({
                    title: 'Create Error',
                    message: 'Could not create badge'
                });
            }
        },

        async assignBadgeToProduct(productId, productVersionId, badgeId, mediaId = null) {
            try {
                await this.ProductBadgeApiService.assignBadgeToProduct(
                    productId, 
                    productVersionId, 
                    badgeId, 
                    mediaId
                );
                this.createNotificationSuccess({
                    title: 'Success',
                    message: 'Badge assigned to product successfully'
                });
            } catch (error) {
                console.error('Error assigning badge:', error);
                this.createNotificationError({
                    title: 'Assignment Error',
                    message: 'Could not assign badge to product'
                });
            }
        },

        async bulkAssignBadges(assignments) {
            try {
                await this.ProductBadgeApiService.bulkAssignBadges(assignments);
                this.createNotificationSuccess({
                    title: 'Success',
                    message: 'Badges assigned successfully'
                });
            } catch (error) {
                console.error('Error bulk assigning badges:', error);
                this.createNotificationError({
                    title: 'Bulk Assignment Error',
                    message: 'Could not assign badges'
                });
            }
        }
    }
});
