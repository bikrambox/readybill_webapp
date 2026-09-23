<?php

namespace Modules\GroceryGermany\Helpers;


use Illuminate\Support\Facades\DB;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\View;


use Modules\GroceryGermany\Entities\Shop;
use Modules\GroceryGermany\Entities\ReportGeneration;
use Modules\Authentication\Entities\User;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use DateTime;

class BillingHelpher
{
    public static function taxCalculation($sale_price, $quantity, $taxPercentage)
    {
        return number_format($sale_price * $quantity * ($taxPercentage / 100), 2);
    }


    // generate invoice number
    public static function generateInvoiceNumber($invoice_count)
    {
        // Get the current year
        $currentYear = date('Y');

        // Get the start of the financial year (1st April)
        $startOfFinancialYear = date('Y-m-d', strtotime('1st April ' . $currentYear));

        // Check if the current date is before the start of the financial year
        if (date('Y-m-d') < $startOfFinancialYear) {
            // If before, the financial year is the previous year
            $financialYear = $currentYear - 1;
        } else {
            // If on or after, the financial year is the current year
            $financialYear = $currentYear;
        }

        // Generate the invoice number
        $invoiceNumber = config('general.invoice_prefix') . $financialYear . '/' . $invoice_count;

        return $invoiceNumber;
    }
    // generate invoice number

    public static function generatePdf($data)
    {

        // Data to pass to the Blade view
        // $data = [
        //     'invoiceNumber' => 'INV123',
        //     'amount' => 100.00,
        // ];

        // Delete all files in the "report" folder
        //  $folderPath = public_path('storage/bill/');
        $folderPath = 'storage/bill/';
        $files = glob($folderPath . '*'); // Get all files in the folder

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file); // Delete each file
            }
        }
        // Delete all files in the "report" folder



        // Render the Blade view
        $html = View::make('bill', $data)->render();

        // Create an instance of Dompdf with options
        $options = new Options();
        $options->set('dpi', 150); // Adjust DPI as needed
        $options->set('defaultFont', 'DejaVu Sans'); // Set default font if needed
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        // Set paper size and orientation
        $options->set('defaultPaperSize', 'A4'); // or 'letter', 'legal', etc.
        // Custom paper size example:
        // $options->set('defaultPaperSize', [0, 0, 595.276, 841.89]); // Width and height in millimeters (A4 size)

        $dompdf = new Dompdf($options);


        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // Set paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();


        // Create 'media' folder if not exists
        $mediaFolder = 'storage/bill';
        if (!file_exists($mediaFolder)) {
            mkdir($mediaFolder, 0777, true);
        }

        // Generate a unique filename
        $filename = 'bill_' . time() . '.pdf';
        $filePath = 'bill/' . $filename;

        // Store the PDF in the "bills" folder
        // Storage::put('bills/' . $filename, $dompdf->output());
        // $dompdf->save("storage/".$filePath);
        $filePath = 'storage/bill/' . $filename;
        file_put_contents($filePath, $dompdf->output());

        // Return the path to the stored PDF
        return $filename;
    }


    public static function convertPriceToNumber($price, $decimalSeparator)
    {
        // if ($decimalSeparator === ',') {
        //     // Convert "45.550" to "45550" (remove thousands separator)
        //     $price = str_replace('.', '', $price);
        //     // Convert "45,50" to "45.50" (change decimal separator)
        //     $price = str_replace(',', '.', $price);
        // } elseif ($decimalSeparator === '.') {
        //     // Convert "45,550" to "45550" (remove thousands separator)
        //     $price = str_replace(',', '', $price);
        // }

        // $numericPrice = floatval($price);
        // if (is_nan($numericPrice)) {
        //     return 0;
        // }

        // // Format number with the correct decimal separator
        // $formattedPrice = number_format($numericPrice, 2, $decimalSeparator, '');

        // return $formattedPrice;

        
        // Default formatted price
        $formattedPrice = '0.00';

        // Check if the price is numeric
        if (is_numeric($price)) {
            // Format the price with 2 decimal places and replace '.' with the given decimal separator
            $formattedPrice = number_format((float) $price, 2, $decimalSeparator, '');
        }

        return $formattedPrice;

    }


    public static function billingResponse($user, $id)
    {
        $logo = 'NA';
        $user_name = 'NA';
        $business_name = 'NA';
        $address = 'NA';
        $mobile_number = 'NA';

        if ($user->isAdmin == 1) {
            $preferences = DB::table('preferences')->where('user_id', $user->user_id)->first();
            $logo = $user->shop->logo;

            $user_name = $user->shop->name;
            $business_name = $user->shop->business_name;
            $address = $user->shop->address;
            $gstin = $user->shop->gstin;

            $mobile_number = $user->mobile;
            $country_details = json_decode($user->country_details);

        } else if ($user->isAdmin == 0) {
            $staff = $user->staff;
            $shop = Shop::find($staff->addedBy);
            $preferences = DB::table('preferences')->where('user_id', $shop->user->user_id)->first();
            $logo = $shop->logo;

            $user_name = $shop->name;
            $business_name = $shop->business_name;
            $address = $shop->address;
            $gstin = $shop->gstin;

            $mobile_number = $shop->user->mobile;
            $country_details = json_decode($shop->user->country_details);

        }

        $billing = DB::table('billing')->find($id);

        if (!$billing) {
            return [];
        }

        $date = new \DateTime($billing->created_at);
        // $invoice_date = $date->format('d/m/Y - h:i:s A');
        $invoice_date = $date->format('d/m/Y - h:i A');



        $currency = $country_details->currency_symbol;
        $decimal_separator = $country_details->decimal_separator;
        $dial_code = $country_details->dial_code;


        $totalTaxAmount = 0;
        $taxGroups = [];

        foreach (json_decode($billing->item_list) as $item) {
            // Group tax1 (GST) by percentage
            $taxPercent = (float) $item->tax1->percent;
            if ($taxPercent != 0) {

                // Divide GST into CGST and SGST (each 50% of the GST amount)
                $vatAmount = (float) number_format((float) $item->tax1->amount, 2, '.', '');

                $key = number_format($taxPercent / 2, 2, '.', '');

                // Add VAT
                if (!isset($taxGroups['vat'][$key])) {
                    $taxGroups['vat'][$key] = [
                        'taxName' => 'VAT',
                        'totalAmount' => 0.0,
                        'totalTax' => 0.0
                    ];
                }

                $taxGroups['vat'][$key]['totalAmount'] = (float) number_format(
                    (float) $taxGroups['vat'][$key]['totalAmount'] + (float) $item->amount,
                    2,
                    '.',
                    ''
                );
                
                $taxGroups['vat'][$key]['totalTax'] = (float) number_format(
                    (float) $taxGroups['vat'][$key]['totalTax'] + $vatAmount,
                    2,
                    '.',
                    ''
                );


                // Accumulate total tax amount
                $totalTaxAmount = (float) number_format(
                    (float) $totalTaxAmount + $vatAmount,
                    2,
                    '.',
                    ''
                );
            }

        }



        // Retrieve base URL from environment, with a fallback
        $logo_url = env('LOGO_URL', '');

        // Initialize variables
        $logo = ($logo && $logo !== 'NA') ? $logo : '';
        $imageUrl = $logo ? rtrim($logo_url, '/') . '/' . ltrim($logo, '/') : '';
        $isLogo = 0;

        // Check if the image is valid (remote or local)
        if ($imageUrl) {
            if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                // Remote URL: Check if the image exists
                $headers = @get_headers($imageUrl);
                if ($headers && strpos($headers[0], '200') !== false) {
                    // Optionally verify content-type for images
                    $isLogo = 1;
                }
            } else {
                // Local file: Check if the file exists
                if (file_exists(public_path($imageUrl))) {
                    $isLogo = 1;
                }
            }
        }

        // Fallback to default image if no valid logo
        if (!$isLogo) {
            $imageUrl = '';
        }


        $data = [
            'invoice_number' => $billing->invoice_number,
            'item_list' => json_decode($billing->item_list),
            'total_price' => $billing->total_price,
            'invoice_count' => $billing->invoice_count + 1,
            'invoice_date' => $invoice_date,
            'grand_total' => $billing->total_price,
            'preferences' => $preferences,

            // 'logo' => $logo,
            // 'logo_url' => env('LOGO_URL'),

            'logo' => $imageUrl,
            'isLogo' => $isLogo,

            'business_name' => ucwords(strtolower($business_name)),
            'address' => $address,
            'gstin' => $gstin,
            'user_name' => $user_name,
            'mobile_number' => $dial_code . " " . $mobile_number,

            'currency' => $currency,
            'decimal_separator' => $decimal_separator,

            'totalTaxAmount' => number_format($totalTaxAmount, 2),
            'taxGroups' => $taxGroups,
        ];

        return $data;
    }

    public static function deleteReportRecord($report_id)
    {
        // Find the report or fail
        $report = ReportGeneration::find($report_id);

        if (!$report) {
            throw new \Exception("Report with ID {$report_id} not found.");
        }

        // Sanitize file name to prevent directory traversal
        $fileName = basename($report->file_path);
        $folderPath = storage_path('app/public/reports');
        $filePath = $folderPath . '/' . $fileName;

        // Start a database transaction
        return DB::transaction(function () use ($filePath, $report) {
            // Delete file if it exists
            if (File::exists($filePath)) {
                if (!File::delete($filePath)) {
                    throw new \Exception("Failed to delete file: {$filePath}");
                }
            }

            // Delete the database record
            $report->delete();

            return true;
        });
    }


    // public static function checkDataISPresentBetweenDates($user_id, $dateFrom, $dateTo)
    // {

    //     $user = User::find($user_id);
    //     $shopId = $user->isAdmin ? $user->shop->shop_id : $user->staff->addedBy;

    //     $table_name = 'billing';

    //     // Build query
    //     $selectQuery = $user->isAdmin ?
    //         "SELECT billing.*, COALESCE(staff.name, shops.name) AS user_name
    //      FROM $table_name AS billing
    //      LEFT JOIN shops ON billing.shop_id = shops.shop_id
    //      LEFT JOIN staff ON billing.user_id = staff.user_id AND staff.addedBy = :shopId1
    //      WHERE billing.shop_id = :shopId2" :
    //         "SELECT billing.*, staff.name AS user_name
    //      FROM $table_name AS billing
    //      LEFT JOIN shops ON billing.shop_id = shops.shop_id
    //      LEFT JOIN staff ON staff.addedBy = :shopId AND staff.user_id = :userId
    //      WHERE staff.addedBy = :shopId2 AND billing.user_id = staff.user_id";

    //     $parameters = $user->isAdmin ? [
    //         'shopId1' => $shopId,
    //         'shopId2' => $shopId
    //     ] : [
    //         'shopId' => $shopId,
    //         'userId' => $user->id,
    //         'shopId2' => $shopId
    //     ];

    //     $searchParameters = [];


    //     if (!empty($dateFrom) && !empty($dateTo)) {
    //         $dateFromFormatted = DateTime::createFromFormat('d/m/Y', $dateFrom);
    //         $dateToFormatted = DateTime::createFromFormat('d/m/Y', $dateTo);
    //         if ($dateFromFormatted && $dateToFormatted) {
    //             $dateFromFormatted->setTime(0, 0, 0);
    //             $dateToFormatted->setTime(23, 59, 59);
    //             $dateFromStr = $dateFromFormatted->format('Y-m-d H:i:s');
    //             $dateToStr = $dateToFormatted->format('Y-m-d H:i:s');
    //             $selectQuery .= " AND billing.created_at BETWEEN '$dateFromStr' AND '$dateToStr'";
    //         }
    //     }

    //     $selectQuery .= " ORDER BY created_at DESC";
    //     $items = DB::select(DB::raw($selectQuery), array_merge($parameters, $searchParameters));

    //     return count($items);
    // }

    
    public static function checkDataISPresentBetweenDates($user_id, $dateFrom, $dateTo)
    {
        $user = User::find($user_id);
        $shopId = $user->isAdmin ? $user->shop->shop_id : $user->staff->addedBy;

        $table_name = 'billing';

        // Build query
        $selectQuery = $user->isAdmin ?
            "SELECT billing.*, COALESCE(staff.name, shops.name) AS user_name
         FROM $table_name AS billing
         LEFT JOIN shops ON billing.shop_id = shops.shop_id
         LEFT JOIN staff ON billing.user_id = staff.user_id AND staff.addedBy = :shopId1
         WHERE billing.shop_id = :shopId2" :
            "SELECT billing.*, staff.name AS user_name
         FROM $table_name AS billing
         LEFT JOIN shops ON billing.shop_id = shops.shop_id
         LEFT JOIN staff ON staff.addedBy = :shopId AND staff.user_id = :userId
         WHERE staff.addedBy = :shopId2 AND billing.user_id = staff.user_id";

        $parameters = $user->isAdmin ? [
            'shopId1' => $shopId,
            'shopId2' => $shopId
        ] : [
            'shopId' => $shopId,
            'userId' => $user_id,
            'shopId2' => $shopId
        ];

        if (!empty($dateFrom) && !empty($dateTo)) {
            $dateFromFormatted = DateTime::createFromFormat('d/m/Y', $dateFrom);
            $dateToFormatted = DateTime::createFromFormat('d/m/Y', $dateTo);

            if ($dateFromFormatted && $dateToFormatted) {
                $dateFromFormatted->setTime(0, 0, 0);
                $dateToFormatted->setTime(23, 59, 59);

                // Use parameter binding instead of direct string concatenation
                $selectQuery .= " AND billing.created_at BETWEEN :dateFrom AND :dateTo";
                $parameters['dateFrom'] = $dateFromFormatted->format('Y-m-d H:i:s');
                $parameters['dateTo'] = $dateToFormatted->format('Y-m-d H:i:s');
            }
        }

        $selectQuery .= " ORDER BY billing.created_at DESC";

        // FIX: Remove DB::raw() wrapper - DB::select already expects a raw SQL string
        $items = DB::select($selectQuery, $parameters);

        return count($items);
    }

}