# Fork CMS Catalog module
This catalog module is insipred by the created by Webleads. We upgraded this module from Fork version 3 to version 5. By this we are able to use more of the Fork features.

## Capabilities
At the moment it is almost the same as the Webleads module, but we left out some code and are still working on this, our changes are:
* Improved the products by adding:
  * Related products
  * Special prices (sale prices)
  * Added a article number
  * Added VAT options

We also removed some components:
* The media manager has been removed infavour of the default Fork media manager
* Removed comments on products (may return in the future)

## How to install

### 1. Upload the module
Upload this module as usual, copy the `Catalog` folder from the `Backend` and `Frontend`.

### 2. Run composer
This module requires extra depencies, you can install these by running:

```composer require tetranz/select2entity-bundle```

### 3. Install extra JavaScript and CSS
```yarn add select2```

```guild build```

Now you should be able to run this module.

At the moment the backend only is fixed and we are still working on this.
