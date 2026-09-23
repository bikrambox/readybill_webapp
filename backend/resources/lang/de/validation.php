<?php

    return [

        'Success' => 'Success',    ### new entry ###
        'Unauthenticated' => 'Unauthenticated',   ### new entry ###
        'Cleaning up' => 'Cleaning up',    ### new entry ###
        'Business Name' => 'Business Name',  ### new entry ###
        'Validation failed' => 'Validation failed',  ### new entry ###
 
        // NO SCRIPT RULE
        'invalid_script_content' => 'The :attribute field contains invalid content.',   ### new entry ###
        // NO SCRIPT RULE

        // LOGIN HELPER
        'Invalid input' => 'Ungültige Eingabe',
        'Login successful' => 'Anmeldung erfolgreich',
        'An unexpected error occured.' => 'Ein unerwarteter Fehler ist aufgetreten.',
        'Invalid credentials.' => 'Ungültige Zugangsdaten.',
        'User data not found.' => 'Benutzerdaten nicht gefunden.',
        'Please complete your registration before using the system.' => 'Bitte schließen Sie Ihre Registrierung ab, bevor Sie das System verwenden.',
        'Unable to retrieve shop details.' => 'Shop-Details können nicht abgerufen werden.',
        'Your account has been deactivated. Please contact the administrator.' => 'Ihr Konto wurde deaktiviert. Bitte kontaktieren Sie den Administrator.',
        'User logged in successfully.' => 'Benutzer erfolgreich angemeldet.',
        // LOGIN HELPER


        // REGISTER HELPER
        'User Exsits but shop details is not present' => 'Benutzer existiert, aber Shop-Details sind nicht vorhanden',
        'Sorry! Unable to process the OTP' => 'Entschuldigung! Das OTP konnte nicht verarbeitet werden',
        'The OTP has already been sent. You can use the same OTP.' => 'Das OTP wurde bereits gesendet. Sie können dasselbe OTP verwenden.',
        'The email has already been registered.' => 'Die E-Mail wurde bereits registriert.',
        'Invalid user.' => 'Ungültiger Benutzer.',
        // REGISTER HELPER

        // USER HELPER 
        'User not found.' => 'Benutzer nicht gefunden.',
        'Dial code not found.' => 'Ländervorwahl nicht gefunden.',
        'Invalid user data: country code or mobile number missing.' => 'Ungültige Benutzerdaten: Ländercode oder Mobilnummer fehlt.',
        'API key exceeds the maximum allowed size.' => 'Der API-Schlüssel überschreitet die maximal zulässige Größe.',
        'Failed to generate API key after' => 'Generierung des API-Schlüssels fehlgeschlagen nach',
        'attempts' => 'Versuchen',
        'Shop not found' => 'Shop nicht gefunden',
        'Subscription assigned successfully' => 'Abonnement erfolgreich zugewiesen',
        'Failed to assign subscription' => 'Abonnement konnte nicht zugewiesen werden',
        'No subscription found for the shop' => 'Kein Abonnement für den Shop gefunden',
        'The shop subscription has expired' => 'Das Shop-Abonnement ist abgelaufen',
        'The subscription payment status is not valid' => 'Der Zahlungsstatus des Abonnements ist nicht gültig',
        'Valid subscription' => 'Gültiges Abonnement',
        'Old table does not exist' => 'Alte Tabelle existiert nicht',
        'New table name already exists' => 'Neuer Tabellenname existiert bereits',
        'Table renamed successfully' => 'Tabelle erfolgreich umbenannt',
        // USER HELPER 

        // API KEY CHECK MIDDLEWARE
        'Invalid API Key' => 'Ungültiger API-Schlüssel',
        'API Key Not Found' => 'API-Schlüssel nicht gefunden',
        'API Key is not matching' => 'API-Schlüssel stimmt nicht überein',
        'Unauthorized' => 'Unbefugt',
        'Internal Server Error' => 'Interner Serverfehler',

        // API KEY CHECK MIDDLEWARE
        
        // CHECK IS ADMIN MIDDLEWARE
        "User dont' have permission to access" => "Benutzer hat keine Berechtigung für den Zugriff",
        // CHECK IS ADMIN MIDDLEWARE


        // CHECK USER IS ACTIVE MIDDLEWARE
        'Unauthorized access' => 'Unberechtigter Zugriff',
        'Your account has been deactivated' => 'Ihr Konto wurde deaktiviert',
        // CHECK USER IS ACTIVE MIDDLEWARE


        // CHECK WEB IS ADMIN MIDDLEWARE
        'You are not authorized to access this page' => 'Sie sind nicht berechtigt, auf diese Seite zuzugreifen',
        // CHECK WEB IS ADMIN MIDDLEWARE


        // CHECK SHOP SUBSCRIPTION FOR WEB MIDDLEWARE
        'Please log in to continue' => 'Bitte melden Sie sich an, um fortzufahren',
        // CHECK SHOP SUBSCRIPTION FOR WEB MIDDLEWARE

        // LOGIN CONTROLLER
        'Successfully logout' => 'Erfolgreich abgemeldet',
        'Shop Login Successfully' => 'Shop-Anmeldung erfolgreich',
        // LOGIN CONTROLLER


        // REGISTER CONTROLLER
        'OTP Successfully Send' => 'OTP erfolgreich gesendet',
        'OTP Verified Successfully' => 'OTP erfolgreich verifiziert',
        'User Registered Successfully' => 'Benutzer erfolgreich registriert',
        'Shop Registered Successfully' => 'Shop erfolgreich registriert',
        // REGISTER CONTROLLER
        
        
        // AUTH CONTROLLER
        'Validation Error' => 'Validierungsfehler',
        'User data is updated successfully.' => 'Benutzerdaten wurden erfolgreich aktualisiert.',
        'Valid API Key' => 'Gültiger API-Schlüssel',
        'Staff and associated user deleted successfully' => 'Mitarbeiter und zugehöriger Benutzer erfolgreich gelöscht',
        'Mobile Number Successfully Updated' => 'Mobilnummer erfolgreich aktualisiert',
        'The email has already registered' => 'The email has already registered',    ### new entry ###
        'Invalid OTP. Please try again. Attempt Left' => 'Invalid OTP. Please try again. Attempt Left',    ### new entry ###
        // AUTH CONTROLLER
        
        
        // BILLING CONTROLLER
        'New Bill Successfully created' => 'Neue Rechnung erfolgreich erstellt',
        'Bill has been shared successfully' => 'Bill has been shared successfully',     ### new entry ###
        'Invalid date format. Use dd/mm/yyyy' => 'Ungültiges Datumsformat. Verwenden Sie TT/MM/JJJJ',
        'Future dates are not allowed' => 'Zukunftsdaten sind nicht erlaubt',
        'Date range cannot exceed 6 months' => 'Der Datumsbereich darf 6 Monate nicht überschreiten',
        'Bill Successfully Updated' => 'Rechnung erfolgreich aktualisiert',
        'The mobile field is required' => 'The mobile field is required',   ### new entry ###
        // BILLING CONTROLLER

        // CHANGE PASSWORD CONTROLLER
        'The new password cannot be the same as the current password' => 'Das neue Passwort darf nicht mit dem aktuellen Passwort übereinstimmen',
        'The mobile number is not verified. Please try again' => 'Die Mobilnummer ist nicht verifiziert. Bitte versuchen Sie es erneut',
        'Sorry! No User Found' => 'Entschuldigung! Kein Benutzer gefunden',
        'Password Successfully Updated' => 'Passwort erfolgreich aktualisiert',
        // CHANGE PASSWORD CONTROLLER


        // DELETE CONTROLLER
        'No OTP record found for this phone number' => 'Kein OTP-Datensatz für diese Telefonnummer gefunden',
        'OTP has expired. Please request a new OTP' => 'OTP ist abgelaufen. Bitte fordern Sie ein neues OTP an',
        'Invalid OTP. Please try again' => 'Ungültiges OTP. Bitte versuchen Sie es erneut',
        'Maximum attempts reached' => 'Maximale Anzahl an Versuchen erreicht',
        'minutes' => 'Minuten',
        'Invalid OTP. Attempt Left' => 'Ungültiges OTP. Verbleibender Versuch',
        'Account deleted successfully' => 'Konto erfolgreich gelöscht',
        'Failed to delete account' => 'Konto konnte nicht gelöscht werden',
        // DELETE CONTROLLER

        // DONWLOAD DATA CONTROLLER
        'File not found' => 'Datei nicht gefunden',
        'Export successful' => 'Export erfolgreich',
        'An error occurred while downloading dataset' => 'Beim Herunterladen des Datensatzes ist ein Fehler aufgetreten',
        // DONWLOAD DATA CONTROLLER


        // ITEM CONTROLLER
        'No Product Found' => 'Kein Produkt gefunden',
        'Table not found' => 'Tabelle nicht gefunden',
        'Item not found' => 'Artikel nicht gefunden',
        'Item deleted successfully' => 'Artikel erfolgreich gelöscht',
        'No matching items found' => 'Keine passenden Artikel gefunden',
        'Items deleted successfully' => 'Artikel wurden erfolgreich gelöscht',
        'Failed to delete items' => 'Artikel konnten nicht gelöscht werden',
        'Item deleted from cart successfully' => 'Item deleted from cart successfully',    ### new entry ###
        'Failed to delete the item from cart' => 'Failed to delete the item from cart',    ### new entry ###
        // ITEM CONTROLLER
        
        
        // OTP CONTROLLER
        'The provided mobile number does not match our records' => 'Die angegebene Mobilnummer stimmt nicht mit unseren Datensätzen überein',
        // OTP CONTROLLER


        // ITEM ON CART CONTROLLER
        'Item Not Found' => 'Artikel nicht gefunden',
        'New Item Successfully Added on Cart' => 'Neuer Artikel erfolgreich zum Warenkorb hinzugefügt',
        'Item List' => 'Artikelliste',
        'Item Updated Successfully' => 'Artikel erfolgreich aktualisiert',
        'All items deleted successfully' => 'Alle Artikel wurden erfolgreich gelöscht',
        'Invalid location provided' => 'Ungültiger Standort angegeben',
        // ITEM ON CART CONTROLLER


        // PUSH NOTIFICATION CONTROLLER
        'Device Token Successfully Stored' => 'Geräte-Token erfolgreich gespeichert',
        // PUSH NOTIFICATION CONTROLLER


        // REPORT CONTROLLER
        'No transactions found for the selected date range. Please choose a different one' => 'Für den ausgewählten Zeitraum wurden keine Transaktionen gefunden. Bitte wählen Sie einen anderen Zeitraum',
        'A previous Report request is in queue, please wait for completion' => 'Eine vorherige Berichtsanforderung befindet sich in der Warteschlange. Bitte warten Sie auf die Fertigstellung',
        'Report generation requested. You will be notified once it is ready' => 'Berichterstellung angefordert. Sie werden benachrichtigt, sobald sie fertig ist',
        'Report re-generation requested. You will be notified once it is ready' => 'Neuerstellung des Berichts angefordert. Sie werden benachrichtigt, sobald er fertig ist',
        // REPORT CONTROLLER

        // UPLOAD DATA CONTROLLER
        'No active jobs found' => 'Keine aktiven Jobs gefunden',
        'An error occurred while checking active jobs' => 'Beim Überprüfen aktiver Jobs ist ein Fehler aufgetreten',
        'Waiting to start' => 'Warten auf Start',
        'Excel processing started' => 'Excel-Verarbeitung gestartet',
        'An error occurred while processing the request' => 'Beim Verarbeiten der Anfrage ist ein Fehler aufgetreten',
        'No data available' => 'Keine Daten verfügbar',
        'Job dispatched, processing data' => 'Job gestartet, Daten werden verarbeitet',
        'An error occurred' => 'Ein Fehler ist aufgetreten',
        'Invalid cell index' => 'Ungültiger Zellindex',
        'New Item Successfully Added' => 'Neuer Artikel erfolgreich hinzugefügt',
        'Item successfully updated' => 'Artikel erfolgreich aktualisiert',
        'No changes made' => 'Keine Änderungen vorgenommen',
        'No table found, returning empty data' => 'Keine Tabelle gefunden, gebe leere Daten zurück',
        'Export to inventory started' => 'Export zum Inventar gestartet',
        'The Excel file contains' => 'Die Excel-Datei enthält',
        'rows, but the maximum allowed is 50,000' => 'Zeilen, aber die maximal zulässige Anzahl ist 50.000',
        'File processed successfully' => 'Datei erfolgreich verarbeitet',
        'Some rows contain errors' => 'Einige Zeilen enthalten Fehler',
        'An error occurred while processing the Excel file' => 'Beim Verarbeiten der Excel-Datei ist ein Fehler aufgetreten',
        'No temporary table found' => 'Keine temporäre Tabelle gefunden',
        'Items Successfully Added' => 'Artikel erfolgreich hinzugefügt',
        'Validation Error! No database changes were made' => 'Validierungsfehler! Es wurden keine Datenbankänderungen vorgenommen',
        'An error occurred while exporting to inventory' => 'Beim Exportieren ins Inventar ist ein Fehler aufgetreten',
        'User preferences not found' => 'Benutzereinstellungen nicht gefunden',
        'Data fetched successfully' => 'Daten erfolgreich abgerufen',
        'An error occurred while fetching data' => 'Beim Abrufen der Daten ist ein Fehler aufgetreten',
        'Job ID is required' => 'Job ID is required',    ### new entry ###
        // UPLOAD DATA CONTROLLER


        // DATASET CONTROLLER
        'No items found in temporary table' => 'Keine Artikel in der temporären Tabelle gefunden',
        'Database operation failed' => 'Datenbankoperation fehlgeschlagen',
        'Dataset Preview' => 'Datensatzvorschau',
        '' => '',
        // DATASET CONTROLLER

        // HSN CODE VALIDATION
        'The HSN code must be a numeric value between 2 and 16 digits and cannot be all zeros' => 'Der HSN-Code muss eine numerische Zeichenfolge zwischen 2 und 16 Ziffern sein und darf nicht nur aus Nullen bestehen',
        'The HSN code must be a numeric value between 2 and 16 digits or empty and cannot be all zeros' => 'Der HSN-Code muss eine numerische Zeichenfolge zwischen 2 und 16 Ziffern oder leer sein und darf nicht nur aus Nullen bestehen',
        // HSN CODE VALIDATION


        'The minimum stock alert cannot be accepted without specifying a stock' => 'Die Mindestbestandswarnung kann nicht akzeptiert werden, wenn kein Bestand angegeben wird',
        'Invalid minimum stock alert value' => 'Ungültiger Wert für Mindestbestandswarnung',
        
        // JOBS
        'Starting data fetch' => 'Starte Datenabfrage',
        'User not found' => 'Benutzer nicht gefunden',
        'Checking temporary table' => 'Überprüfe temporäre Tabelle',
        'Applying pagination' => 'Anwenden der Paginierung',
        'Fetching data' => 'Abrufen von Daten',
        'Validating data' => 'Daten werden validiert',
        'Generating errors' => 'Fehler werden generiert',
        'Preparing response' => 'Antwort wird vorbereitet',
        'Starting Excel processing' => 'Starte Excel-Verarbeitung',
        'Parsing Excel file' => 'Excel-Datei wird analysiert',
        'Failed: The Excel file contains' => 'Fehler: Die Excel-Datei enthält',
        'Fetching user preferences' => 'Benutzereinstellungen werden abgerufen',
        'Collecting item names for duplicate checking' => 'Sammle Artikelnamen für Duplikatprüfung',
        'Processing data chunks' => 'Verarbeite Datenblöcke',
        'Excel processing completed successfully' => 'Excel-Verarbeitung erfolgreich abgeschlossen',
        'Failed: An error occurred while processing the Excel file' => 'Fehler: Beim Verarbeiten der Excel-Datei ist ein Fehler aufgetreten',
        'Starting export to inventory' => 'Starte Export ins Inventar',
        'Failed: No temporary table found' => 'Fehler: Keine temporäre Tabelle gefunden',
        'Collecting item names for validation' => 'Sammle Artikelnamen zur Validierung',
        'Validated chunk' => 'Validierter Block',
        'Inserting data into main table' => 'Füge Daten in Haupttabelle ein',
        'Failed: Database operation failed' => 'Fehler: Datenbankoperation fehlgeschlagen',
        // JOBS


    // CATCH ERRORS
    'unable_to_process' => 'Sorry, we are unable to process your request. Please try again after some time.',   ### new entry ###
    // CATCH ERRORS


    'Invalid mobile number' => 'Invalid mobile number',   ### new entry ###
    'The mobile number has already been registered' => 'The mobile number has already been registered',   ### new entry ###
    'Invalid request type' => 'Invalid request type',   ### new entry ###
];