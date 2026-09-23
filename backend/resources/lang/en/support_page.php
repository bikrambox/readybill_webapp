<?php

// {!!  __('support_page.faq1.para.11') !!}

return [

    'title' => 'Support',
    'subtitle' => 'Frequent asked questions',

    'ask_your_questions' => 'Ask your questions',
    'contact_n_sale_support' => 'Contact Sales & Support: +91 88227 74191/ +91 98640 81806',

    'faq1' => [
        'quest' => 'How can I add items to inventory?',
        'para' => [
            '1' => 'When you start using ReadyBill for the first time, your store data will be empty. 
                    The first thing that you have to do is to add your product data, before you can start using it.',
            '2' => 'To add your products into the inventory, you need certain information in hand, about each product. 
                    By default, ReadyBill has not enabled all the fields of information data. 
                    You can click on the “<b>Settings</b>” menu on the left and enable or disable
                    data that you don’t want to show or maintain. For example, <b>Stock Quantity</b> is disabled by
                    default. If you want to maintain your stocks, then you must enable it from the Settings
                    page. Similarly, if you want to show the MRP in your receipt or invoice, you
                    must first enable MRP, then enable MRP in invoice. Only then MRP can be shown in the
                    receipt or invoice.',
            '3' => 'For example:',
            '4' => '<b>Item Name:</b> Item Name is the name of your product that you are entering. 
                    (Say) you want to add “AASHIRVAAD SALT 1 KG”. Enter this product name “AASHIRVAAD
                    SALT 1 KG” in the Item Name field.',
            '5' => 'Note: If you have multiple products by the same name but weight is different, then
                    you should name them accordingly. For example, “AASHIRVAAD SALT 1 KG” and
                    “AASHIRVAAD SALT 500 G” are different. If you store them with the same name,
                    then you cannot distinguish which one you are selling.',
            '6' => '<b>Stock Quantity:</b> If you want to maintain stock quantity in your shop, 
                    you should enter the current quantity of stock available in your shop. The stock quantity
                    can help you in many ways. With this, you can always tell the amount of stock available
                    in your shop for any product.',
            '7' => '<b>Minimum Stock alert:</b> Only if you are maintaining stocks then you can use this field
                    to alert you when a particular item has gone below your minimum stock quantity. 
                    For example, (say) you have a fast-selling product “Amulya Powder 500” and you
                    always want to keep a minimum of 10 packets available in your stock. Then you can set a
                    <b>MINIMUM STOCK ALERT</b> for this product to 10. As soon as the stock of this product goes below 10,
                    you will see the product highlighted in red in the inventory page. THIS FIELD IS NOT MANDATORY.',
            '8' => '<b>Unit:</b> Every product has a UNIT. Units are like – Bag, Box, Bottle, Piece, Can, Kg,
                    Gram etc. For example, Maggi Tomato Ketchup’s unit is Bottle while Good Knight
                    refill unit can be <b>piece</b> or <b>pack</b>. You can find the list of all
                    units in the “Add Inventory” page. UNIT IS A MANDATORY FIELD and is always required.',
            '9' => '<b>Rate:</b> Rate is the Sale Price i.e., the price at which
                    you are selling the product. THIS IS A MANDATORY FIELD and is required for all purposes. 
                    This Rate is printed in the receipt or invoice.',
            '10' => '<b>Tax:</b> Each product has a tax percentage. For example, the Govt. of India has made
                    Goods and Services Tax (GST) mandatory. There can be additional taxes apart from
                    GST, like CESS. If the Product has a CESS Tax, then you will also have to enter
                    the CESS Tax value. THIS FIELD IS NOT MANDATORY. Even if there is no tax, you will have to enter ‘0’.',
                
            '11' => '<b>HSN: </b><b>HSN (Harmonized System of Nomenclature) and SAC (Service Accounting Code)</b> are standardized codes used for classifying goods and services under GST, where HSN is applicable for products and SAC is used for services. This field will be available only if the “Use HSN/SAC Codes” option is enabled in the Settings menu. It helps in accurate tax calculation, GST compliance, and proper invoice generation. This field is optional and can be used based on your business requirements.',
            '12' => '<b>Barcode: </b>A <b>barcode</b> is a unique code assigned to each product, which can be scanned using a barcode scanner for faster billing and inventory management. This field will be available only if the “Enable Bar Code Scanning” option is enabled in the Settings menu. Using barcodes improves billing speed, reduces manual errors, and makes product identification easier. This field is optional and can be enabled or disabled as per your needs.',

            '13' => '<span class="text-success fw-bold">*TIP:</span> Instead of adding data one-by-one, you can also upload bulk data in
                    batches. There is an “Upload Data” button in the “Add Inventory” page. You can use it to
                    upload bulk data. More information on “How to upload inventory data in CSV or XLS
                    format?” can be found <a href="how-to-upload" target="_blank">here</a>',
            '14' => '<b>VAT:</b> Each product has a tax percentage. For example, <b>VAT (Value Added Tax)</b> may be applicable at rates such as 7% or 19%, depending on the product. You should enter the appropriate tax percentage for each item. THIS FIELD IS NOT MANDATORY. Even if there is no tax applicable, you will have to enter ‘0’.'
        ]

    ],
    'faq2' => [
        'quest' => 'How to quick sell?',
        'para' => [
            '1' => 'After you have added your products, now you are ready to sell.
                    In quick sell',
            '2' => '<b>For the Mobile app – </b>just tap the MIC button and say
                    the product name. For example, “Amul butter 1 piece” or “Amul Butter 1 Packet”. A list will open.
                    Select the right product. You will see the product and quantity automatically filled up in
                    the boxes. Now click <b>Add</b>.',
            '3' => 'Now you will see the product has been added below for billing.
                    Add more products in the same way. You can also use the keyboard and type your Product Name and
                    Quantity.',
            '4' => 'Below, you can also edit or change the quantities and price
                    for the product that you have already added.',
            '5' => 'Once done, you can either tap the Save button or the
                    <b>Print</b> icon at the top. Both the actions will save the transaction.',
            '6' => '<b>For the Desktop app –</b> You will have to type and enter
                    the product name and quantity and then click <b>Add</b>. Currently, the desktop app does not have the
                    speech/ voice feature. This will be implanted soon.',
        ]

    ],

    'faq3' => [
        'quest' => 'How to do a refund?',
        'para' => [
            '1' => 'The <b>Refund</b> works the same way as the <b>Quick Sell</b>.
                    The only difference is that – when you add a product for REFUND the price will be in negative. 
                    Here again you can do both <b>SAVE</b> and <b>PRINT</b>.',
        ]

    ],

    'faq4' => [
        'quest' => 'How can I add Employee or Staff?',
        'para' => [
            '1' => 'Click on the <b>Employees</b> menu on the left. Enter the
                    required details of your employee.',
            '2' => '<span class="text-danger">Note:</span> Employees cannot create
                    their Mobile Number and Password on their own.
                    After adding the employee, you will have to share the details with him/ her. Now
                    the employee will be able to login with the details. If the employee wants to change
                    his/ her Mobile Number or Address – only the Shop Owner (Admin) can do this.',
        ]

    ],

    'faq5' => [
        'quest' => 'How can my employees sell and do a billing?',
        'para' => [
            '1' => 'Employee has to login with his/ her credentials. Once logged
                    in, employee can do <b>Quick Sell</b> and <b>Refund</b>. Employees does not have all the privileges
                    that the shop owner has. All sales done by the employee are stored in transaction. When you
                    view the <b>Transaction</b>, you can see each invoice and who has done the sale and
                    what time.',
        ]

    ],

    'faq6' => [
        'quest' => 'How can I see the transactions?',
        'para' => [
            '1' => 'The “<b>Transaction</b>” menu is on the left side. You can see a
                    detailed list of all the transaction – who made the sell and at what time. You can also click on any
                    transaction and view more details. Here, you can also print a receipt or invoice.',
        ]

    ],

    'faq7' => [
        'quest' => 'How to use the Settings?',
        'para' => [
            '1' => 'The “<b>Settings</b>” menu is on the left. Clicking on it will open the
                    Settings page. Here you can make your settings by enabling or disabling the options.',
            '2' => '<b>Do you maintain MRP?</b> If you enable this option, then you will be asked to enter the <b>MRP</b> of
                    the product in “<b>Add Inventory</b>” page or in your XLS/ CS file if you are
                    entering data in bulk.',
            '3' => '<b>Do you want to show MRP in invoice?</b> If you enable this option, then the <b>MRP</b> will be shown in the receipt
                    or invoice. Note: Without enabling MRP option you cannot enable to show MRP in
                    invoice.',
            '4' =>  '<b>Do you want to maintain stock?</b> If you enable this option, then you have to enter (mandatory) your current
                    stock quantity when you add inventory items. Note: By default, “<b>Stock
                    Quantity</b>” is shown in “<b>Add Inventory</b>” page, but it is not
                    mandatory. When you enable this option here, the Stock Quantity becomes mandatory.',
            '5' => '<b>Do you want to HSN/ SAC code?</b> HSN stands for “<b>Harmonized System of Nomenclature</b>”. This code is used
                    for classification of products. Evey product has its own unique HSN number.
                    You can find the <b>HSN Code List &amp; GST</b> here <a
                    href="https://cleartax.in/s/gst-hsn-lookup" target="_blank">https://cleartax.in/s/gst-hsn-lookup</a>. 
                    You can enable or disable HSN from here. If you disable HSN/SAC code then you don’t need to enter it in your Inventory data.',
            '6' => '<b>Do you want to show HSN/ SAC code in invoice?</b>
                    If you have enabled this option, then you must also enable “Do you want
                    HSN/ SAC code?”. If you enable this option, then the HSN code will be
                    printed in the receipt or invoice',
        ]

    ],

    'faq8' => [
        'quest' => 'I have added stock but it is not working properly.',
        'para' => [
            '1' => 'To make the stock work properly as desired, you have to first enable Stock in “Settings” menu.',
        ]

    ],

    'faq9' => [
        'quest' => 'I tapped the MIC and nothing is showing up.',
        'para' => [
            '1' => 'Please confirm that you have added Inventory. Click on “View Inventory” and confirm.',
        ]

    ],


    'faq10' => [
        'quest' => 'In the Dataset, I see many rows in Red.',
        'para' => [
            '1' => 'The possible reasons are - there are duplicates or a mismatch in the accepted values. 
                    All alerts and reasons will be mentioned above the Dataset to help you fix them. 
                    Sometimes, the items that are already present in your Inventory are also present in the Dataset. 
                    The Dataset will then show them in Red because they are now duplicates.',
        ]

    ],

    'faq11' => [
        'quest' => 'How can I connect and use the Printer?',
        'para' => [
            '1' => 'ReadyBill Desktop Web Application (https://www.readybill.app/) supports any printer of your choice. Additionally, you can use the small thermal Bluetooth printers.',
            '2' => 'ReadyBill Mobile App supports both the 80 mm and 50 mm Bluetooth Thermal Printers. 
                    Some 80 mm thermal printers do not support iPhone. As soon as Apple provides support or fixes the issue for the 80 mm thermal printers, 
                    they will work with the app.',
            '3' => 'To use a thermal printer for your Android device, simply pair your device with your mobile. 
                    Once paired, the printers will show up in your list of Bluetooth Devices and it will also show up in the App. 
                    Tap on the “Printer” menu in your app and the desired printer will show up.',
            '4' => 'To use a thermal printer for your iOS device, simply pair your device with your mobile.
                    Once paired, the printers will show up in your list of Bluetooth Devices and it will also show up in the App. 
                    Tap on the “Printer” menu in your app and the desired printer will show up.',
        ]

    ],

    'Thank you for your query' => 'Thank you for your query',
    'We will review it and get back to you' => 'We will review it and get back to you',

    
];