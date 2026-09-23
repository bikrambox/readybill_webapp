<?php

    return [

        // LOGIN HELPER
        'Invalid input' => 'Invalid input',
        'Login successful' => 'Login successful',
        'An unexpected error occured.' => 'An unexpected error occured.',
        'Invalid credentials.' => 'Invalid credentials.',
        'User data not found.' => 'User data not found.',
        'Please complete your registration before using the system.' => 'Please complete your registration before using the system.',
        'Unable to retrieve shop details.' => 'Unable to retrieve shop details.',
        'Your account has been deactivated. Please contact the administrator.' => 'Your account has been deactivated. Please contact the administrator.',
        'User logged in successfully.' => 'User logged in successfully.',
        // LOGIN HELPER


        // REGISTER HELPER
        'User Exsits but shop details is not present' => 'User Exsits but shop details is not present',
        'Sorry! Unable to process the OTP' => 'Sorry! Unable to process the OTP',
        'The OTP has already been sent. You can use the same OTP.' => 'The OTP has already been sent. You can use the same OTP.',
        'The email has already been registered.' => 'The email has already been registered.',
        'Invalid user.' => 'Invalid user.',
        // REGISTER HELPER

        // USER HELPER 
        'User not found.' => 'User not found.',
        'Dial code not found.' => 'Dial code not found.',
        'Invalid user data: country code or mobile number missing.' => 'Invalid user data: country code or mobile number missing.',
        'API key exceeds the maximum allowed size.' => 'API key exceeds the maximum allowed size.',
        'Failed to generate API key after' => 'Failed to generate API key after',
        'attempts' => 'attempts',
        'Shop not found' => 'Shop not found',
        'Subscription assigned successfully' => 'Subscription assigned successfully',
        'Failed to assign subscription' => 'Failed to assign subscription',
        'No subscription found for the shop' => 'No subscription found for the shop',
        'The shop subscription has expired' => 'The shop subscription has expired',
        'The subscription payment status is not valid' => 'The subscription payment status is not valid',
        'Valid subscription' => 'Valid subscription',
        'Old table does not exist' => 'Old table does not exist',
        'New table name already exists' => 'New table name already exists',
        'Table renamed successfully' => 'Table renamed successfully',
        // USER HELPER 

        // API KEY CHECK MIDDLEWARE
        'Invalid API Key' => 'Invalid API Key',
        'API Key Not Found' => 'API Key Not Found',
        'API Key is not matching' => 'API Key is not matching',
        'Unauthorized' => 'Unauthorized',
        'Internal Server Error' => 'Internal Server Error',

        // API KEY CHECK MIDDLEWARE
        
        // CHECK IS ADMIN MIDDLEWARE
        "User dont' have permission to access" => "User dont' have permission to access",
        // CHECK IS ADMIN MIDDLEWARE


        // CHECK USER IS ACTIVE MIDDLEWARE
        'Unauthorized access' => 'Unauthorized access',
        'Your account has been deactivated' => 'Your account has been deactivated',
        // CHECK USER IS ACTIVE MIDDLEWARE


        // CHECK WEB IS ADMIN MIDDLEWARE
        'You are not authorized to access this page' => 'You are not authorized to access this page',
        // CHECK WEB IS ADMIN MIDDLEWARE


        // CHECK SHOP SUBSCRIPTION FOR WEB MIDDLEWARE
        'Please log in to continue' => 'Please log in to continue',
        // CHECK SHOP SUBSCRIPTION FOR WEB MIDDLEWARE

        // LOGIN CONTROLLER
        'Successfully logout' => 'Successfully logout',
        'Shop Login Successfully' => 'Shop Login Successfully',
        // LOGIN CONTROLLER


        // REGISTER CONTROLLER
        'OTP Successfully Send' => 'OTP Successfully Send',
        'OTP Verified Successfully' => 'OTP Verified Successfully',
        'User Registered Successfully' => 'User Registered Successfully',
        'Shop Registered Successfully' => 'Shop Registered Successfully',
        // REGISTER CONTROLLER
        
        
        // AUTH CONTROLLER
        'Validation Error' => 'Validation Error',
        'User data is updated successfully.' => 'User data is updated successfully.',
        'Valid API Key' => 'Valid API Key',
        'Staff and associated user deleted successfully' => 'Staff and associated user deleted successfully',
        'Mobile Number Successfully Updated' => 'Mobile Number Successfully Updated',
        // AUTH CONTROLLER
        
        
        // BILLING CONTROLLER
        'New Bill Successfully created' => 'New Bill Successfully created',
        'Invalid date format. Use dd/mm/yyyy' => 'Invalid date format. Use dd/mm/yyyy',
        'Future dates are not allowed' => 'Future dates are not allowed',
        'Date range cannot exceed 6 months' => 'Date range cannot exceed 6 months',
        'Bill Successfully Updated' => 'Bill Successfully Updated',
        // BILLING CONTROLLER

        // CHANGE PASSWORD CONTROLLER
        'The new password cannot be the same as the current password' => 'The new password cannot be the same as the current password',
        'The mobile number is not verified. Please try again' => 'The mobile number is not verified. Please try again',
        'Sorry! No User Found' => 'Sorry! No User Found',
        'Password Successfully Updated' => 'Password Successfully Updated',
        // CHANGE PASSWORD CONTROLLER


        // DELETE CONTROLLER
        'No OTP record found for this phone number' => 'No OTP record found for this phone number',
        'OTP has expired. Please request a new OTP' => 'OTP has expired. Please request a new OTP',
        'Invalid OTP. Please try again' => 'Invalid OTP. Please try again',
        'Maximum attempts reached' => 'Maximum attempts reached',
        'minutes' => 'minutes',
        'Invalid OTP. Attempt Left' => 'Invalid OTP. Attempt Left',
        'Account deleted successfully' => 'Account deleted successfully',
        'Failed to delete account' => 'Failed to delete account',
        // DELETE CONTROLLER

        // DONWLOAD DATA CONTROLLER
        'File not found' => 'File not found',
        'Export successful' => 'Export successful',
        'An error occurred while downloading dataset' => 'An error occurred while downloading dataset',
        // DONWLOAD DATA CONTROLLER


        // ITEM CONTROLLER
        'No Product Found' => 'No Product Found',
        'Table not found' => 'Table not found',
        'Item not found' => 'Item not found',
        'Item deleted successfully' => 'Item deleted successfully',
        'No matching items found' => 'No matching items found',
        'Items deleted successfully' => 'Items deleted successfully',
        'Failed to delete items' => 'Failed to delete items',
        // ITEM CONTROLLER
        
        
        // OTP CONTROLLER
        'The provided mobile number does not match our records' => 'The provided mobile number does not match our records',
        // OTP CONTROLLER


        // ITEM ON CART CONTROLLER
        'Item Not Found' => 'Item Not Found',
        'New Item Successfully Added on Cart' => 'New Item Successfully Added on Cart',
        'Item List' => 'Item List',
        'Item Updated Successfully' => 'Item Updated Successfully',
        'All items deleted successfully' => 'All items deleted successfully',
        'Invalid location provided' => 'Invalid location provided',
        // ITEM ON CART CONTROLLER


        // PUSH NOTIFICATION CONTROLLER
        'Device Token Successfully Stored' => 'Device Token Successfully Stored',
        // PUSH NOTIFICATION CONTROLLER


        // REPORT CONTROLLER
        'No transactions found for the selected date range. Please choose a different one' => 'No transactions found for the selected date range. Please choose a different one',
        'A previous Report request is in queue, please wait for completion' => 'A previous Report request is in queue, please wait for completion',
        'Report generation requested. You will be notified once it is ready' => 'Report generation requested. You will be notified once it is ready',
        'Report re-generation requested. You will be notified once it is ready' => 'Report re-generation requested. You will be notified once it is ready',
        // REPORT CONTROLLER

        // UPLOAD DATA CONTROLLER
        'No active jobs found' => 'No active jobs found',
        'An error occurred while checking active jobs' => 'An error occurred while checking active jobs',
        'Waiting to start' => 'Waiting to start',
        'Excel processing started' => 'Excel processing started',
        'An error occurred while processing the request' => 'An error occurred while processing the request',
        'No data available' => 'No data available',
        'Job dispatched, processing data' => 'Job dispatched, processing data',
        'An error occurred' => 'An error occurred',
        'Invalid cell index' => 'Invalid cell index',
        'New Item Successfully Added' => 'New Item Successfully Added',
        'Item successfully updated' => 'Item successfully updated',
        'No changes made' => 'No changes made',
        'No table found, returning empty data' => 'No table found, returning empty data',
        'Export to inventory started' => 'Export to inventory started',
        'The Excel file contains' => 'The Excel file contains',
        'rows, but the maximum allowed is 50,000' => 'rows, but the maximum allowed is 50,000',
        'File processed successfully' => 'File processed successfully',
        'Some rows contain errors' => 'Some rows contain errors',
        'An error occurred while processing the Excel file' => 'An error occurred while processing the Excel file',
        'No temporary table found' => 'No temporary table found',
        'Items Successfully Added' => 'Items Successfully Added',
        'Validation Error! No database changes were made' => 'Validation Error! No database changes were made',
        'An error occurred while exporting to inventory' => 'An error occurred while exporting to inventory',
        'User preferences not found' => 'User preferences not found',
        'Data fetched successfully' => 'Data fetched successfully',
        'An error occurred while fetching data' => 'An error occurred while fetching data',
        // UPLOAD DATA CONTROLLER


        // DATASET CONTROLLER
        'No items found in temporary table' => 'No items found in temporary table',
        'Database operation failed' => 'Database operation failed',
        'Dataset Preview' => 'Dataset Preview',
        '' => '',
        // DATASET CONTROLLER

        // HSN CODE VALIDATION
        'The HSN code must be a numeric value between 2 and 16 digits and cannot be all zeros' => 'The HSN code must be a numeric value between 2 and 16 digits and cannot be all zeros',
        'The HSN code must be a numeric value between 2 and 16 digits or empty and cannot be all zeros' => 'The HSN code must be a numeric value between 2 and 16 digits or empty and cannot be all zeros',
        // HSN CODE VALIDATION


        'The minimum stock alert cannot be accepted without specifying a stock' => 'The minimum stock alert cannot be accepted without specifying a stock',
        'Invalid minimum stock alert value' => 'Invalid minimum stock alert value',
        
        // JOBS
        'Starting data fetch' => 'Starting data fetch',
        'User not found' => 'User not found',
        'Checking temporary table' => 'Checking temporary table',
        'Applying pagination' => 'Applying pagination',
        'Fetching data' => 'Fetching data',
        'Validating data' => 'Validating data',
        'Generating errors' => 'Generating errors',
        'Preparing response' => 'Preparing response',
        'Starting Excel processing' => 'Starting Excel processing',
        'Parsing Excel file' => 'Parsing Excel file',
        'Failed: The Excel file contains' => 'Failed: The Excel file contains',
        'Fetching user preferences' => 'Fetching user preferences',
        'Collecting item names for duplicate checking' => 'Collecting item names for duplicate checking',
        'Processing data chunks' => 'Processing data chunks',
        'Excel processing completed successfully' => 'Excel processing completed successfully',
        'Failed: An error occurred while processing the Excel file' => 'Failed: An error occurred while processing the Excel file',
        'Starting export to inventory' => 'Starting export to inventory',
        'Failed: No temporary table found' => 'Failed: No temporary table found',
        'Collecting item names for validation' => 'Collecting item names for validation',
        'Validated chunk' => 'Validated chunk',
        'Inserting data into main table' => 'Inserting data into main table',
        'Failed: Database operation failed' => 'Failed: Database operation failed',
        // JOBS
];