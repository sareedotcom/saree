# Theme Editor Argento Home

## Installation

### For clients

Please do not install this module. It will be installed automatically as a dependency.

### For developers

Use this approach if you have access to our private repositories!

For Swissuplabs developers only!

```bash
cd <magento_root>

composer config repositories.swissup composer https://docs.swissuplabs.com/packages/
composer require swissup/module-theme-editor-argento-home:dev-master --prefer-source
bin/magento module:enable Swissup_ThemeEditorArgentoHome
bin/magento setup:upgrade
```
