# shopware-6.6-product-badge-plugin

Boost user engagement and drive sales by showcasing attractive product badges (labels or images) on your product listings and detail pages.

## Key Features

✅ Customizable Badges – Add labels or images to highlight special offers (e.g., "Sale," "New," "Bestseller").

✅ Flexible Placement – Display badges on product cards, listings, and detail pages.

✅ Boost Conversions – Draw attention to promotions, discounts, and featured products.

✅ Mobile-Friendly – Responsive design ensures badges look great on all devices.

✅ Easy Configuration – Simple setup via Shopware Admin with no coding required.

## Compatibility

✔ Shopware 6.5, 6.6 & 6.7 – Fully tested and optimized for the latest versions.

Guide for Product Badges Plugin for Shopware 6.5, 6.6 &amp; 6.7

## Installation

- Go to project root/custom/static-plugins and upload the Product Badges plugin file
  
- Go to your project root and edit the composer.json file
  
- In the require object of your composer.json file, write the following line
  
  ```cmd
  "swag/product-badges": "1.0.0"
  ```
- Run the following command in the terminal

  ```cmd
    composer require swag/product-badges
  ```
- Done

## Plugin Configurations

- In the admin panel, go to Extension > My Extensions
  
- Install and then activate the plugin

  <img width="1298" height="611" alt="1 The plugin is activated" src="https://github.com/user-attachments/assets/1a2bb590-4ca7-40ff-8a3c-625f55276c10" />

- After activating the plugin, click on Configure to manage the Badge Label and Image size
  
- <img width="1298" height="611" alt="2 Click on configure for badge image and label settings" src="https://github.com/user-attachments/assets/f800ed1a-afd3-4499-8886-a127c9acdb97" />

- You can configure the following Global settings for your badge label and the images.

   - Badge Label Text Color
     
   - Badge Label Background Color
     
   - Badge Label Font Size
     
   - Badge Image width and height
 
  <img width="1298" height="611" alt="3 badge label and image configurations" src="https://github.com/user-attachments/assets/88dbcab0-b429-411b-a98b-de806e175507" />

## Manage, Create, Update the product's badges under Settings

- In the admin panel, go to Settings > Extension and click on the Product Badges module

  <img width="1298" height="611" alt="4 settings extension product badges" src="https://github.com/user-attachments/assets/1ca407fc-296e-4a35-badd-7034c7d1ced7" />

- Create, update, remove, and search the product badges.

 <img width="1298" height="611" alt="5 update or add new product badges" src="https://github.com/user-attachments/assets/1150fb55-cb16-4b04-a187-b4331fcac134" />

## Adding a new Badge

- In the above image, you can click on the Add Badge button to add a new badge.

- You will see the following fields in the add badge form

   - Name: Name of the badge to identify it in the admin panel
 
   - Badge Label: This will appear in the front store on the assigned image of the product if this badge has no badge image uploaded.
 
   - Active: You can enable or disable the badge
 
   - Position for Product List: In front store product list page or category pages, this will define the position of the badge label or the badge image. Basically, there are four positions available

      - Top-left, Top-right, Bottom-left, and Bottom-right

   - Position for the Product details page in the front store for the badge image or the badge label
 
   - Upload the badge image: This badge image will appear in the front store if it is assigned to any of the product's images.
 
- At last, you will see a message that to assign product images, we will need to save the badge first.

<img width="1298" height="611" alt="6 create a new badge 1" src="https://github.com/user-attachments/assets/e595f192-d6d3-43db-9c8c-f8cec89c5e48" />
<img width="1298" height="611" alt="7 create a new badge 2" src="https://github.com/user-attachments/assets/68bd9492-650b-4cb7-959b-59da3a312afe" />

- After saving the badge, we will see the option to assign the Products or Product's images to the newly created badge.

<img width="1298" height="611" alt="8 create a new badge 3" src="https://github.com/user-attachments/assets/a854018e-82ba-4be3-94d3-3375fe4017e4" />

- After clicking on Assign Products, we will see a pop-up message with the product selection

   - Here you can select multiple products.
 
     <img width="1298" height="611" alt="9 select product and click next to assign the images of the selected product" src="https://github.com/user-attachments/assets/8736b412-ff43-4fe4-9c52-f232be08fb01" />

 
   - Click Next to assign the images of the selected products.
 
     <img width="1298" height="611" alt="10 select images and click assign" src="https://github.com/user-attachments/assets/3ca6b93b-e263-4741-b6f3-c7093de58637" />

   - You can select the individual image or multiple images at once.
 
   - Click assign.
 
<img width="1298" height="611" alt="11 Afater assigning the product images you can manage in badge details" src="https://github.com/user-attachments/assets/bc92cda5-7eef-4161-bda2-f8bd99235788" />

- Now you can see the assigned images of the selected products in the Badge details(Above Image).

- You can also manage the Product Badges under the Product Details > Product Badges Tab

- Go to Catalogues > Product and view any product of your choice

  <img width="1298" height="611" alt="13 product badges tab in the product details page" src="https://github.com/user-attachments/assets/5d11fa5c-86b4-4192-864f-d95b05e147bd" />

- Click on Add Badge. You will see the list of the current product's images and the existing badges

<img width="1298" height="611" alt="13 assign badge " src="https://github.com/user-attachments/assets/6457693d-a86e-4cee-84bb-9809db0ed2b1" />

- Assigned badges list view

<img width="1298" height="611" alt="14 assigned badges in product details admin view" src="https://github.com/user-attachments/assets/2dd7fa01-dbfb-41fc-a86b-45d3711deb40" />

## Front Store View, Product List, and the Product Details View

<img width="1298" height="611" alt="15 Front-store list view badge label" src="https://github.com/user-attachments/assets/18d4ced6-657c-4421-aaa2-5337c1cfdac2" />
<img width="1298" height="611" alt="16 front store product details view badge label" src="https://github.com/user-attachments/assets/616a33ce-77bf-4145-a2af-f64ed6710e3f" />
<img width="1298" height="611" alt="17 front store product list badge label 2" src="https://github.com/user-attachments/assets/611494ee-d170-4b1a-9658-357178431e9b" />
<img width="1298" height="611" alt="18 front store product list with badge image" src="https://github.com/user-attachments/assets/71e7bf54-d761-4614-8606-29954a4aef0e" />
<img width="1298" height="611" alt="19 product details with badge image " src="https://github.com/user-attachments/assets/438422e8-5ece-48c4-a970-b79a01f01da0" />

## The plugin is responsible for the Multiple Devices

<img width="1298" height="611" alt="20 Front Store Responsive" src="https://github.com/user-attachments/assets/b712a290-4eef-49ef-9ea6-87dcd1d975bb" />



