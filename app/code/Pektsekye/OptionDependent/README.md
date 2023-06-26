  
**Dependent Custom Options 1.0 for Magento 2.1**  Oct 5 2016 

Check the latest README file:
http://hottons.com/demo/m2/od/README.html


This addition makes product custom options dependent on each other.  
An option that was set as "child" will be hidden until the "parent" option value is selected.  
This extension has .csv import/export for product custom options.  


**Index:**

*   Installation
*   An example of how to set dependency
*   Export options
*   Import options



### Installation

**1)** Upload 58 new files :  

    app/code/Pektsekye/OptionDependent/Block/Adminhtml/Od/Export.php
    app/code/Pektsekye/OptionDependent/Block/Adminhtml/Product/Edit/Js.php
    app/code/Pektsekye/OptionDependent/Block/Product/View/Js.php
    app/code/Pektsekye/OptionDependent/composer.json
    app/code/Pektsekye/OptionDependent/Controller/Adminhtml/Od/Export.php
    app/code/Pektsekye/OptionDependent/Controller/Adminhtml/Od/Export/Export.php
    app/code/Pektsekye/OptionDependent/Controller/Adminhtml/Od/Export/Import.php
    app/code/Pektsekye/OptionDependent/Controller/Adminhtml/Od/Export/Index.php
    app/code/Pektsekye/OptionDependent/Controller/Adminhtml/Product/Edit/Option.php
    app/code/Pektsekye/OptionDependent/Controller/Adminhtml/Product/Edit/Option/ImportDependency.php
    app/code/Pektsekye/OptionDependent/etc/adminhtml/acl.xml
    app/code/Pektsekye/OptionDependent/etc/adminhtml/di.xml
    app/code/Pektsekye/OptionDependent/etc/adminhtml/events.xml
    app/code/Pektsekye/OptionDependent/etc/adminhtml/menu.xml
    app/code/Pektsekye/OptionDependent/etc/adminhtml/routes.xml
    app/code/Pektsekye/OptionDependent/etc/di.xml
    app/code/Pektsekye/OptionDependent/etc/frontend/di.xml
    app/code/Pektsekye/OptionDependent/etc/module.xml
    app/code/Pektsekye/OptionDependent/Helper/Data.php
    app/code/Pektsekye/OptionDependent/i18n/en_US.csv
    app/code/Pektsekye/OptionDependent/LICENSE.txt
    app/code/Pektsekye/OptionDependent/Model/Catalog/Product/Option/Type/DatePlugin.php
    app/code/Pektsekye/OptionDependent/Model/Catalog/Product/Option/Type/FilePlugin.php
    app/code/Pektsekye/OptionDependent/Model/Catalog/Product/Option/Type/SelectPlugin.php
    app/code/Pektsekye/OptionDependent/Model/Catalog/Product/Option/Type/TextPlugin.php
    app/code/Pektsekye/OptionDependent/Model/Catalog/Product/Type/Plugin.php
    app/code/Pektsekye/OptionDependent/Model/CsvImportHandler.php
    app/code/Pektsekye/OptionDependent/Model/Observer/OptionSaveAfter.php
    app/code/Pektsekye/OptionDependent/Model/Option.php
    app/code/Pektsekye/OptionDependent/Model/ResourceModel/Option.php
    app/code/Pektsekye/OptionDependent/Model/ResourceModel/Option/Collection.php
    app/code/Pektsekye/OptionDependent/Model/ResourceModel/Value.php
    app/code/Pektsekye/OptionDependent/Model/ResourceModel/Value/Collection.php
    app/code/Pektsekye/OptionDependent/Model/Value.php
    app/code/Pektsekye/OptionDependent/Plugin/Catalog/Ui/DataProvider/Product/Form/Modifier/CustomOptions.php
    app/code/Pektsekye/OptionDependent/Plugin/Catalog/Model/Product/Option/Repository.php
    app/code/Pektsekye/OptionDependent/README.md
    app/code/Pektsekye/OptionDependent/registration.php
    app/code/Pektsekye/OptionDependent/Setup/InstallSchema.php
    app/code/Pektsekye/OptionDependent/view/adminhtml/layout/CATALOG_PRODUCT_COMPOSITE_CONFIGURE.xml
    app/code/Pektsekye/OptionDependent/view/adminhtml/layout/catalog_product_new.xml
    app/code/Pektsekye/OptionDependent/view/adminhtml/layout/optiondependent_od_export_index.xml
    app/code/Pektsekye/OptionDependent/view/adminhtml/requirejs-config.js
    app/code/Pektsekye/OptionDependent/view/adminhtml/templates/od/export.phtml
    app/code/Pektsekye/OptionDependent/view/adminhtml/templates/product/composite/configure/js.phtml
    app/code/Pektsekye/OptionDependent/view/adminhtml/templates/product/edit/js.phtml
    app/code/Pektsekye/OptionDependent/view/adminhtml/web/product/edit/main.css
    app/code/Pektsekye/OptionDependent/view/adminhtml/web/product/edit/main.js
    app/code/Pektsekye/OptionDependent/view/adminhtml/web/template/form/components/js.html
    app/code/Pektsekye/OptionDependent/view/adminhtml/web/template/form/element/input_children.html
    app/code/Pektsekye/OptionDependent/view/adminhtml/web/template/form/element/input_delete.html
    app/code/Pektsekye/OptionDependent/view/adminhtml/web/template/form/element/input_id_label.html
    app/code/Pektsekye/OptionDependent/view/adminhtml/web/template/form/element/input_id.html
    app/code/Pektsekye/OptionDependent/view/adminhtml/web/template/form/element/input_sku.html
    app/code/Pektsekye/OptionDependent/view/base/web/main.js
    app/code/Pektsekye/OptionDependent/view/frontend/layout/catalog_product_view.xml
    app/code/Pektsekye/OptionDependent/view/frontend/requirejs-config.js
    app/code/Pektsekye/OptionDependent/view/frontend/templates/product/view/js.phtml


**2)** Connect to your website via SSH:  
Type in Terminal of your computer:  
```sh
ssh -p 2222 username@yourdomain.com  
```
Then enter your server password  

If you are connected to your server change directory with command:  
```sh
cd /full_path_to_your_magento_root_directory  
```
Update magento modules with command:  
```sh
./bin/magento setup:upgrade  
```
NOTE: If it shows permission error make it executable with command: `chmod +x bin/magento `  

**3)** Manually remove  cached _requirejs diectory:  
pub/static/_requirejs  

**4)** Refresh magento cache:  
Go to Magento admin panel -> System -> Cache Managment  
Click the "Flush Magento Cache" button 


### An example of how to set dependency

**1)** Go to Magento admin panel -> Products -> Catalog  
**2)** Find a product with type Simple Product and without custom options then click the "Edit" link.  
**3)** Add a custom option to it: Title - "Gender", Input Type - "Drop-down".  
**4)** Add rows to it: "Mens", "Womens".  
**5)** Add second custom option: Title - "Size", Input Type - "Drop-down".  
**6)** Add four rows with titles: "S", "M", "L", "XL".  
**7)** Scroll the page back to the "Gender" option.  
**8)** Find the "Children" field of the "Mens" row.  
**9)** Enter two row ids of the "Size" option separated with commas: 4,5  
**10)** Find the "Children" field of the "Womens" row.  
**11)** Enter another two row ids of the "Size" option: 6,7  
**12)** Click the "Save And Continue Edit " button.  
**13)** Open your product page in the front-end.  
**14)** If you select gender "Mens" the size option must appear with two values S and M.

### To export options

**1)** Go to Magento admin panel -> System -> Dependent Product Options  
**2)** Click the "Export Product Custom Options" button. 
**3)** Find product_options.csv file in your browser downloads directory  

### To import options

**1)** Prepare options data with the Excel program:  
Required fields are five:  
*"product_sku", "option_title", "type", "value_title" (when type is "drop_down", "radio", "checkbox" or "multiple"), "row_id"*  
Valid option types are:  
*"field", "area", "file", "drop_down", "radio", "checkbox", "multiple", "date", "date_time", "time"* 
Valid price types are:  
*"fixed", "percent"* 

The "**row_id**" field should contain any unique per product number.  
This number will be used in the children field to reference an option or an option value.  

The "**children**" field contains comma separated "row_ids" of the product.  

Sample import file:
```csv
"product_sku","option_title","type","is_require","option_sort_order","max_characters","file_extension","image_size_x","image_size_y","value_title","price","price_type","sku","value_sort_order","row_id","children"
"24-MB04","Gender","drop_down","1","0","","","","","Mens","","","","0","2","4,5"
"24-MB04","Gender","drop_down","1","0","","","","","Womens","","","","1","3","6,7"
"24-MB04","Size","drop_down","1","1","","","","","S","","","","2","4",""
"24-MB04","Size","drop_down","1","1","","","","","M","","","","3","5",""
"24-MB04","Size","drop_down","1","1","","","","","L","","","","4","6",""
"24-MB04","Size","drop_down","1","1","","","","","XL","","","","5","7",""
```

**2)** Go to Magento admin panel -> System -> Dependent Product Options  
**3)** Choose your file and click the "Import Product Custom Options" button. 



















