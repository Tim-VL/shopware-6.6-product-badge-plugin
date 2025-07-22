import template from './swag-product-badges-detail.html.twig';
import './swag-product-badges-detail.scss';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;
const { mapPropertyErrors } = Component.getComponentHelper();

Component.register('swag-product-badges-detail', {
    template,

    inject: [
        'repositoryFactory',
        'acl'
    ],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('placeholder')
    ],

    shortcuts: {
        'SYSTEMKEY+S': {
            active() {
                return this.allowSave;
            },
            method: 'onSave'
        },
        ESCAPE: 'onCancel'
    },

    data() {
        return {
            badge: null,
            isLoading: false,
            processSuccess: false,
            repository: null
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    computed: {
        ...mapPropertyErrors('badge', ['name', 'position1', 'position2']),
    
        isCreateMode() {
            return this.$route.name === 'swag.product.badges.create';
        },
    
        allowSave() {
            if (!this.badge || !this.acl) {
                return false;
            }
            
            return this.badge.isNew()
                ? this.acl.can('swag_product_badge.creator')
                : this.acl.can('swag_product_badge.editor');
        },
    
        tooltipSave() {
            if (!this.allowSave) {
                return {
                    message: this.$t('sw-privileges.tooltip.warning'),
                    disabled: this.allowSave,
                    showOnDisabledElements: true
                };
            }
    
            const systemKey = this.$device.getSystemKey();
    
            return {
                message: `${systemKey} + S`,
                appearance: 'light'
            };
        },
    
        tooltipCancel() {
            return {
                message: 'ESC',
                appearance: 'light'
            };
        }
    },
    

    created() {
        this.repository = this.repositoryFactory.create('swag_product_badge');
        this.getBadge();
    },

    methods: {
        getBadge() {
            this.isLoading = true;

            if (this.isCreateMode) {
                this.badge = this.repository.create(Shopware.Context.api);
                this.badge.active = true;
                this.badge.position1 = 'top-left';
                this.badge.position2 = 'top-left';

                this.isLoading = false;
                return;
            }

            const criteria = new Criteria();
            criteria.addAssociation('translations');

            this.repository
                .get(this.$route.params.id, Shopware.Context.api, criteria)
                .then((entity) => {
                    this.badge = entity;
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },

        onSave() {
            this.isLoading = true;
        
            this.repository
                .save(this.badge, Shopware.Context.api)
                .then(() => {
                    this.processSuccess = true;
                    this.$emit('modal-close');
                    this.createNotificationSuccess({
                        title: this.$t('swag-product-badges.detail.titleSaveSuccess'),
                        message: this.$t('swag-product-badges.detail.messageSaveSuccess')
                    });
        
                    if (this.isCreateMode) {
                        this.$router.push({ name: 'swag.product.badges.detail', params: { id: this.badge.id } });
                    } else {
                        this.getBadge();
                    }
                })
                .catch((error) => {
                    console.error('Save error:', error);
                    this.createNotificationError({
                        title: this.$t('swag-product-badges.detail.titleSaveError'),
                        message: this.$t('swag-product-badges.detail.messageSaveError')
                    });
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
        

        onCancel() {
            this.$router.push({ name: 'swag.product.badges.index' });
        },

        saveFinish() {
            this.processSuccess = false;
        },

        onClickSave() {
            this.onSave();
        },
        
        onChangeLanguage(languageId) {
            this.getBadge();
        },
    
        setMediaItem({ targetId }) {
            this.badge.imageId = targetId;
        },
    
        onDropMedia(dropData) {
            this.setMediaItem({ targetId: dropData.id });
        },
    
        onUnlinkMedia() {
            this.badge.imageId = null;
        },
    
        openMediaSidebar() {
            this.$refs.mediaSidebarModal.openContent();
        }
    }
});
