Create virtual product and give it's SKU in Store config of Customer Wallet

-> Product visiblity = Not Visible Individually
-> not in any category
-> has no weight
-> Wallet SKU is getting from store config which product must be virtual


To not apply discount in wallet product
-> Create belove sales price rule
    -> Go to the Magento 2 Admin Panel and navigate to Marketing -> Cart Price Rules.
    -> Find the rule you want to exclude the product from and click on it to edit.
    -> Scroll down to the Conditions section and click on the + icon to add a new condition.
    -> In the dropdown menu, select Product Attribute Combination.
    -> In the next dropdown menu, select SKU.
    -> In the next field, select the operator "is not" and enter the SKU of the product you want to exclude from the rule.
    -> Click on the Save Rule button to save the changes.

Customer Wallet will not allow customer to increase its QTY
customer will not able to add other product with if money request in cart
if other products in cart then money request will not be able to add into cart

NOTE : if testing is enabled then money request time you can pay using COD or Bank transfer

if money request successfully done then customer wallet request will create as processing
after admin approved it will be credited into customers wallet

Customer can pay using wallet amount along with other payment methods Order will be placed zero subtotal

if customer wants to send money to friends then they have to add their details first after admin approved
they can send money to them, amount will be credit/debit respectively



make changes in core file
    Magento\Quote\Model\PaymentMethodManagement.php line no 82 to 84 commmented this code
        <!-- to open in code -->
        code vendor/magento/module-quote/Model/PaymentMethodManagement.php

    // if (!$this->zeroTotalValidator->isApplicable($payment->getMethodInstance(), $quote)) {
    //     throw new InvalidTransitionException(__('The requested Payment Method is not available.'));
    // }
    <!-- above issue done create di for that -->


frontend side custoemr order grid all done with detail of wallet used, done in pdf also

frontend reorder grid mate ni phtl file
    code vendor/magento/module-sales/view/frontend/templates/order/history.phtml
    /var/www/html/magento245/vendor/magento/module-sales/view/frontend/templates/order/info/buttons.phtml
    for every order detailed view

frontend
 ->customer wallet request done with all validation -> done
 ->in cart summary with wallet applied amount -> done
 ->use wallet and place order with zero subtotal -> done
 ->credit/debit frontend/backend -> done
 ->


++++++++++++++ Four companies are in market who has wallet system ++++++++++++++

webkul : https://magento-modules-demo-demo.webkul.com/demomanagement/viewdemo/index/demoid/12/
amasty : https://amasty.com/wallet-for-magento-2.html
magedelight : https://www.magedelight.com/e-wallet-magento-2.html
cedcommerce : https://cedcommerce.com/magento-2-extensions/wallet-system


