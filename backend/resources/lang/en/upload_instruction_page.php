<?php

return [

    'title' => 'How to upload',
    'subtitle' => 'To easily upload XLS or CSV data, we have a sample Dataset 
                    <button type="button" class="btn btn-link m-0 p-0 downloadDataset">here</button> 
                    that you can download and edit according to your needs and upload it.',

    'section1' => [
        'heading' => 'How to upload inventory data in CSV or XLS format?',
        'sub_heading' => 'XLS:',
        'para' =>[
            '1' => 'You can add your inventory items one-by-one using the “<b>Add Inventory menu</b>”. This
                    is convenient when there are only few items that you can manually add them one-by-one, but
                    this is not the best idea when you have to upload hundreds of items at once.',
            '2' => 'The “<b>Upload Data</b>” button in the “<b>Add Inventory</b>” page allows you to upload
                    inventory data in CSV or XLS format. All you have to do is keep your data in the following format –',
            '3' => '<b>ITEM NAME:</b> Name of the item or product that you are adding. There can be many
                    products by the same name. Therefore, you should give a unique name that you
                    always know. For example: Surf Excel 100, Surf Excel 500, Parle G Small, Parle G
                    Large etc.',
            '4' => '<b>QUANTITY:</b> Only if you are maintaining stocks, then this is required or
                    mandatory. If you are maintaining stock quantities then you will always be able to tell the
                    available stock in your shop. <span class="text-danger"> If you are not maintaining stocks
                    then you can keep this field empty. Since this field is optional unless you enable Stock Quantity, you
                    can also put any number greater than 0.</span>',
            '5' => '<b>MINIMUM STOCK ALERT:</b> Only if you are maintaining stocks then you can use this
                    field to alert you when a particular item has gone below your minimum stock
                    quantity. For example, (say) you have a fast-selling product “Amulya Powder 500”
                    and you always want to keep a minimum of 10 packets available in your stock. Then
                    you can set a <b>MINIMUM STOCK ALERT</b> for this product to 10. This field is not
                    mandatory.',
            '6' => '<b>MRP:</b> MRP is the Market Price of the product that is usually printed in the
                    Packet itself. If you want to save the MRP then you have to enable “<b>Do you maintain
                    MRP?</b>” from the <b>Preferences</b> menu. You also have the option of not showing the MRP in
                    the bill or invoice. This can be done from the <b>Preferences</b> menu.',
            '7' => '<b>SALE PRICE:</b> SALE PRICE is the price at which you are selling the product.
                    This is a mandatory field and is required for all purposes.',
            '8' => '<b>UNIT:</b> Every product has a UNIT. Units are like – Bag, Box, Bottle, Piece,
                    Can, Kg, Gram etc. For example, Maggi Tomato Ketchup’s unit is Bottle while Good Knight’s
                    unit can be piece or pack. You can find the list of all units in the “<b>Add
                    Inventory</b>” page. UNIT is a mandatory field and is always required. You will have to use it in
                    their short form otherwise the upload will not be successful. See below table for all
                    available Units and their Short forms.',
            '9' => '<b>HSN:</b> HSN stands for “Harmonized System of Nomenclature”. This code is used
                    for classification of products. Evey product has its own unique HSN number. You can find
                    the <b>HSN Code List &amp; GST</b> here <a class="text-decoration-underline"
                    href="https://cleartax.in/s/gst-hsn-lookup" target="_blank">https://cleartax.in/s/gst-hsn-lookup</a>. You can
                    enable and disable HSN from the <b>Preferences</b> menu. If you disable HSN/ SAC code then
                    you don’t need to enter it in your Inventory data.',
            '10' => '<b>Barcode: </b>A <b>barcode</b> is a unique code assigned to each product, which can be scanned using a barcode scanner for faster billing and inventory management. This field will be available only if the “Enable Bar Code Scanning” option is enabled in the Preferences menu. Using barcodes improves billing speed, reduces manual errors, and makes product identification easier. This field is optional and can be enabled or disabled as per your needs.',

            '11' => '<b>GST:</b> As per the Govt. of India rules, Goods and Services Tax are mandatory.
                    Every item is accompanied with its GST value. This is a required field.',
            '12' => '<b>CESS:</b> Normally Grocery Items does not have CESS Tax, but some products may
                    have CESS Tax. If a product has CESS Tax, then you have to enter the CESS Tax value.',
            '13' => 'Below is an example from a part of a XLS file',
            '14' => 'For example, in this case, the shop owner
                    does not maintain Stock Quantity and Minimum stock cannot be maintained. So, the shop
                    owner has kept these fields as empty.',
            '15' => 'In the below table, the fields that has a <span class="text-danger">red star</span>,
                    means they are always mandatory.',
            '16' => 'Both GST and CESS are Taxes. Normally, CESS in not required. In such cases, you will
                    have to put a number between 0 and 100. It will accept only numbers and decimals.',
            '17' => 'Apart from these mandatory fields, you can make other fields mandatory from the
                    <b>Preferences</b> menu – as per your requirement.',
            '18' => '<b>VAT:</b> Each product has a tax percentage. For example, <b>VAT (Value Added Tax)</b> may be applicable at rates such as 7% or 19%, depending on the product. You should enter the appropriate tax percentage for each item. THIS FIELD IS NOT MANDATORY. Even if there is no tax applicable, you will have to enter ‘0’.'

                ]
    ],
    'section2' => [
        'heading' => 'CSV (Comma Separated Value):',
        'para'=>[
            '1' => 'You can also upload inventory data as a CSV file. Manually creating a CSV file can be
                    tedious. Therefore, we suggest you to follow the instruction below to easily convert a XLS file
                    to a CSV file.',
            '2' => 'Open the Excel file',
            '3' => 'Select File',
            '4' => 'Select Save As',
            '5' => 'In the Save as type box, select CSV (Comma delimited)',
            '6' => 'Choose a location to save the file',
            '7' => 'Click Save',
            '8' => 'Alternatively, you can use any online application that converts your
                    Excel file to a CSV file.',
        ]

    ]

];