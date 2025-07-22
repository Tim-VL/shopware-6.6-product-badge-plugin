import template from './sw-product-detail-badges.html.twig';
import './sw-product-detail-badges.scss';
const { Mixin } = Shopware;

Shopware.Component.register('sw-product-detail-badges', {
  template,
  inject: ['ProductBadgeApiService', 'repositoryFactory'],

  mixins: [
    Mixin.getByName('notification')
  ],
  
  data() {
    return {
      badges: [],
      availableBadges: [],
      isLoading: false,
      showAddBadgeModal: false,
      productImages: [],
      badgeProductCombinations: [],
      columns: [
        {
          property: 'image',
          label: this.$t('swag-product-badges.productDetail.columns.badgeImage'),
          allowResize: true,
        },
        {
          property: 'productImage',
          label: this.$t('swag-product-badges.productDetail.columns.productImage'),
          allowResize: true,
        },
        {
          property: 'label',
          label: this.$t('swag-product-badges.productDetail.columns.label'),
          allowResize: true,
        },
        {
          property: 'active',
          label: this.$t('swag-product-badges.productDetail.columns.active'),
          allowResize: true,
          align: 'center',
        },
        {
          property: 'createdAt',
          label: this.$t('swag-product-badges.productDetail.columns.createdAt'),
          allowResize: true,
        }
      ],
      modalColumns: [
        {
          property: 'badgeName',
          label: this.$t('swag-product-badges.productDetail.modal.columnBadgeName'),
          allowResize: true,
        },
        {
          property: 'badgeImage',
          label: this.$t('swag-product-badges.productDetail.modal.columnBadgeImage'),
          allowResize: true,
        },
        {
          property: 'productImage',
          label: this.$t('swag-product-badges.productDetail.modal.columnProductImage'),
          allowResize: true,
        },
        {
          property: 'actions',
          label: this.$t('swag-product-badges.productDetail.modal.columnActions'),
          allowResize: true,
        }
      ]
    };
  },

  created() {
    this.loadBadges();
    this.loadProductImages();
  },

  computed: {
    dateFilter() {
        return Shopware.Filter.getByName('date');
    },

    productId() {
      return this.$route.params.id;
    }
  },

  methods: {
    loadBadges() {
      this.isLoading = true;
      this.ProductBadgeApiService.getBadgesForProduct(this.productId)
        .then((response) => { 
          this.badges = response.data; 
        })
        .catch((error) => {
          console.error('Error loading badges:', error);
        })
        .finally(() => { 
          this.isLoading = false; 
        });
    },

    async loadAvailableBadges() {
      try {
        const response = await this.ProductBadgeApiService.searchBadges({ active: true }, { limit: 100 });
        this.availableBadges = response.data || [];
        await this.createBadgeProductCombinations();
      } catch (error) {
        console.error('Error loading available badges:', error);
      }
    },

    async loadProductImages() {
      const productRepository = this.repositoryFactory.create('product');
      const criteria = new Shopware.Data.Criteria();
      criteria.addAssociation('media');
      
      try {
        const product = await productRepository.get(this.productId, Shopware.Context.api, criteria);
        this.productImages = product.media || [];
      } catch (error) {
        console.error('Error loading product images:', error);
      }
    },

    async getExistingAssignments() {
      try {
        const response = await this.ProductBadgeApiService.getBadgesForProduct(this.productId);
        return response.data || [];
      } catch (error) {
        console.error('Error loading existing assignments:', error);
        return [];
      }
    },

    async onAddBadge() {
      await this.loadAvailableBadges();
      this.showAddBadgeModal = true;
    },

    onCloseAddBadgeModal() {
      this.showAddBadgeModal = false;
      this.badgeProductCombinations = [];
    },

    async createBadgeProductCombinations() {
      const existingAssignments = await this.getExistingAssignments();
      
      // Create a set of badge-image combinations that are already assigned
      const assignedBadgeImageCombinations = new Set();
      existingAssignments.forEach(assignment => {
          const mediaId = assignment.mediaId || assignment.productImage?.id;
          const badgeId = assignment.id || assignment.swagProductBadgeId;
          if (mediaId && mediaId !== 'null' && badgeId) {
              assignedBadgeImageCombinations.add(`${badgeId}-${mediaId}`);
          }
      });
  
      this.badgeProductCombinations = [];
      
      // Create combinations for all badges and images, excluding only specific badge-image pairs
      this.availableBadges.forEach(badge => {
          this.productImages.forEach(productImage => {
              const imageId = productImage.media.id;
              const combinationKey = `${badge.id}-${imageId}`;
              
              // Only exclude if this specific badge-image combination already exists
              if (!assignedBadgeImageCombinations.has(combinationKey)) {
                  this.badgeProductCombinations.push({
                      id: combinationKey,
                      badge: badge,
                      productImage: productImage,
                      badgeName: badge.name,
                      badgeImage: badge.image
                  });
              }
          });
      });
    },
  
    async onAssignBadge(combination) {
      try {
          // Get product version ID
          const productRepository = this.repositoryFactory.create('product');
          const product = await productRepository.get(this.productId, Shopware.Context.api);
          const productVersionId = product.versionId;
  
          await this.ProductBadgeApiService.assignBadgeToProduct(
              this.productId,
              productVersionId, // Now providing the actual version ID
              combination.badge.id,
              combination.productImage?.media?.id || null
          );
  
          this.createNotificationSuccess({
               title: this.$t('swag-product-badges.assignment.titleAssignSuccess'),
              message: this.$t('swag-product-badges.assignment.descriptionAssignSuccess')
          });
  
          // Remove only the specific badge-image combination that was just assigned
          const assignedCombinationId = combination.id;
          this.badgeProductCombinations = this.badgeProductCombinations.filter(
              item => item.id !== assignedCombinationId
          );
  
          this.loadBadges();
      } catch (error) {
          console.error('Error assigning badge:', error);
          this.createNotificationError({
              title: 'Error',
              message: 'Could not assign badge'
          });
      }
    },  

    async onRemoveBadge(badgeAssignment) {
      try {
        await this.ProductBadgeApiService.removeBadgeFromProduct(
          this.productId,
          badgeAssignment.id || badgeAssignment.swagProductBadgeId,
          badgeAssignment.productImage?.id || badgeAssignment.mediaId || null
        );

        this.createNotificationSuccess({
          title: this.$t('swag-product-badges.assignment.titleUnassignSuccess'),
          message: this.$t('swag-product-badges.assignment.messageUnassignSuccess')
        });

        this.loadBadges();
      } catch (error) {
        console.error('Error removing badge:', error);
        this.createNotificationError({
          title: 'Error',
          message: 'Could not remove badge'
        });
      }
    },
  }
});
