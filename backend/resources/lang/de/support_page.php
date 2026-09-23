<?php

// {!!  __('support_page.faq1.para.11') !!}

return [

    // Übersetzungen ins Deutsche für die Desktop‑Version
    'title' => 'Support',
    'subtitle' => 'Häufig gestellte Fragen',

    'ask_your_questions' => 'Stellen Sie Ihre Fragen',
    'contact_n_sale_support' => 'Kontaktieren Sie Vertrieb & Support: +91 88227 74191 / +91 98640 81806',

    'faq1' => [
        'quest' => 'Wie kann ich Artikel zum Inventar hinzufügen?',
        'para' => [
            '1' => 'Wenn Sie ReadyBill zum ersten Mal verwenden, sind Ihre Store-Daten leer. Das Erste, was Sie tun müssen, ist, Ihre Produktdaten hinzuzufügen, bevor Sie es verwenden können.',
            '2' => 'Um Ihre Produkte in das Inventar einzufügen, benötigen Sie bestimmte Informationen über jedes Produkt. Standardmäßig sind in ReadyBill nicht alle Informationsfelder aktiviert. Sie können im Menü “<b>Voreinstellungen</b>” links Daten ein- oder ausschalten, die Sie nicht anzeigen oder verwalten möchten. Zum Beispiel ist die <b>Lagermenge</b> standardmäßig deaktiviert. Wenn Sie Ihre Bestände verwalten möchten, müssen Sie sie auf der Seite Einstellungen aktivieren. Ebenso, wenn Sie die UVP in Ihrem Beleg oder Ihrer Rechnung anzeigen möchten, müssen Sie zuerst die UVP aktivieren und dann die UVP in der Rechnung aktivieren. Erst dann kann die UVP im Beleg oder in der Rechnung angezeigt werden.',
            '3' => 'Zum Beispiel:',
            '4' => '<b>Artikelname:</b> Der Artikelname ist der Name des Produkts, das Sie eingeben. (Angenommen) Sie möchten “AASHIRVAAD SALZ 1 KG” hinzufügen. Geben Sie diesen Produktnamen “AASHIRVAAD SALZ 1 KG” in das Feld Artikelname ein.',
            '5' => 'Hinweis: Wenn Sie mehrere Produkte mit demselben Namen haben, deren Gewicht jedoch unterschiedlich ist, sollten Sie sie entsprechend benennen. Zum Beispiel sind “AASHIRVAAD SALZ 1 KG” und “AASHIRVAAD SALZ 500 G” unterschiedlich. Wenn Sie sie mit demselben Namen speichern, können Sie nicht unterscheiden, welches Sie verkaufen.',
            '6' => '<b>Lagermenge:</b> Wenn Sie die Lagermenge in Ihrem Geschäft verwalten möchten, sollten Sie die aktuelle verfügbare Menge in Ihrem Geschäft eingeben. Die Lagermenge kann Ihnen auf viele Arten helfen. Damit können Sie jederzeit die Menge des verfügbaren Lagerbestands für jedes Produkt angeben.',
            '7' => '<b>Mindestbestand-Warnung:</b> Nur wenn Sie Bestände pflegen, können Sie dieses Feld nutzen, um sich zu warnen, wenn ein bestimmter Artikel unter Ihre Mindestbestandsmenge fällt. Wenn Sie beispielsweise ein schnellverkauftes Produkt “Amulya Powder 500” haben und immer mindestens 10 Packungen auf Lager haben möchten, können Sie für dieses Produkt eine <b>Mindestbestand-Warnung</b> auf 10 einstellen. Sobald der Bestand dieses Produkts unter 10 fällt, wird der Artikel in der Inventarliste rot hervorgehoben. DIESES FELD IST NICHT ERFORDERLICH.',
            '8' => '<b>Einheit:</b> Jedes Produkt hat eine Einheit. Einheiten sind zum Beispiel – Beutel, Schachtel, Flasche, Stück, Dose, kg, Gramm usw. Zum Beispiel ist die Einheit von Maggi Tomatenketchup eine Flasche, während die Nachfüllpackung von Good Knight eine <b>Stück</b> oder <b>Packung</b> sein kann. Die Liste aller Einheiten finden Sie auf der Seite “Inventar hinzufügen”. Einheit ist ein Pflichtfeld und immer erforderlich.',
            '9' => '<b>Preis:</b> Der Preis ist der Verkaufspreis, d. h. der Preis, zu dem Sie das Produkt verkaufen. DIES IST EIN PFLICHTFELD und für alle Zwecke erforderlich. Dieser Preis wird auf dem Beleg oder der Rechnung gedruckt.',
            '10' => '<b>Steuer:</b> Jedes Produkt hat einen Steuersatz. Beispielsweise hat die indische Regierung die Goods and Services Tax (GST) obligatorisch gemacht. Es kann zusätzliche Steuern neben der GST geben, wie z. B. CESS. Wenn das Produkt eine CESS-Steuer hat, müssen Sie auch den CESS-Betrag eingeben. DIESES FELD IST NICHT VERPFLICHTEND. Auch wenn es keine Steuer gibt, müssen Sie “0” eingeben.',

            '11' => '<b>HSN: </b><b>HSN (Harmonized System of Nomenclature) and SAC (Service Accounting Code)</b> are standardized codes used for classifying goods and services under GST, where HSN is applicable for products and SAC is used for services. This field will be available only if the “Use HSN/SAC Codes” option is enabled in the Settings menu. It helps in accurate tax calculation, GST compliance, and proper invoice generation. This field is optional and can be used based on your business requirements.',  ### new entry v1 ###
            '12' => '<b>Barcode: </b>A <b>barcode</b> is a unique code assigned to each product, which can be scanned using a barcode scanner for faster billing and inventory management. This field will be available only if the “Enable Bar Code Scanning” option is enabled in the Settings menu. Using barcodes improves billing speed, reduces manual errors, and makes product identification easier. This field is optional and can be enabled or disabled as per your needs.',    ### new entry v1 ###


            '13' => '<span class="text-success fw-bold">*TIPP:</span> Anstatt Daten einzeln hinzuzufügen, können Sie auch Massendaten in Batches hochladen. Es gibt eine Schaltfläche “Daten hochladen” auf der Seite “Inventar hinzufügen”. Sie können sie verwenden, um Massendaten hochzuladen. Weitere Informationen dazu, “Wie man Inventardaten im CSV- oder XLS-Format hochlädt”, finden Sie <a href="how-to-upload" target="_blank">hier</a>',
            '14' => '<b>VAT:</b> Each product has a tax percentage. For example, <b>VAT (Value Added Tax)</b> may be applicable at rates such as 7% or 19%, depending on the product. You should enter the appropriate tax percentage for each item. THIS FIELD IS NOT MANDATORY. Even if there is no tax applicable, you will have to enter ‘0’.'  ### new entry v1 ###

        ]

    ],
    'faq2' => [
        'quest' => 'Wie führe ich einen Schnellverkauf durch?',
        'para' => [
            '1' => 'Nachdem Sie Ihre Produkte hinzugefügt haben, sind Sie bereit zu verkaufen. Im Schnellverkauf',
            '2' => '<b>Für die Mobile App –</b> tippen Sie einfach auf den MIC-Button und sagen Sie den Produktnamen. Zum Beispiel “Amul-Butter 1 Stück” oder “Amul-Butter 1 Packung”. Eine Liste wird geöffnet. Wählen Sie das richtige Produkt aus. Sie sehen das Produkt und die Menge automatisch in den Feldern ausgefüllt. Klicken Sie nun auf <b>Hinzufügen</b>.',
            '3' => 'Jetzt sehen Sie, dass das Produkt unten zur Abrechnung hinzugefügt wurde. Fügen Sie weitere Produkte auf die gleiche Weise hinzu. Sie können auch die Tastatur verwenden und Ihren Produktnamen und die Menge eingeben.',
            '4' => 'Unten können Sie auch die Mengen und den Preis für das Produkt, das Sie bereits hinzugefügt haben, bearbeiten oder ändern.',
            '5' => 'Wenn Sie fertig sind, können Sie entweder auf die Schaltfläche Speichern oder auf das <b>Druck</b>-Symbol oben tippen. Beide Aktionen speichern die Transaktion.',
            '6' => '<b>Für die Desktop-App –</b> Sie müssen den Produktnamen und die Menge eingeben und dann auf <b>Hinzufügen</b> klicken. Derzeit verfügt die Desktop-App nicht über die Sprachfunktion. Diese wird bald implementiert.',
        ]

    ],

    'faq3' => [
        'quest' => 'Wie mache ich eine Rückerstattung?',
        'para' => [
            '1' => 'Die <b>Rückerstattung</b> funktioniert genauso wie der <b>Schnellverkauf</b>. Der einzige Unterschied ist, dass beim Hinzufügen eines Produkts für eine Rückerstattung der Preis negativ ist. Auch hier können Sie sowohl <b>SPEICHERN</b> als auch <b>DRUCKEN</b>.',
        ]

    ],

    'faq4' => [
        'quest' => 'Wie kann ich Mitarbeiter oder Personal hinzufügen?',
        'para' => [
            '1' => 'Klicken Sie auf das Menü <b>Mitarbeiter</b> auf der linken Seite. Geben Sie die erforderlichen Details Ihres Mitarbeiters ein.',
            '2' => '<span class="text-danger">Hinweis:</span> Mitarbeiter können ihre Mobilnummer und ihr Passwort nicht selbst erstellen. Nachdem Sie den Mitarbeiter hinzugefügt haben, müssen Sie ihm/ ihr die Details mitteilen. Nun kann sich der Mitarbeiter mit den Daten anmelden. Wenn der Mitarbeiter seine Mobilnummer oder Adresse ändern möchte – kann dies nur der Shopbesitzer (Admin) tun.',
        ]

    ],

    'faq5' => [
        'quest' => 'Wie können meine Mitarbeiter verkaufen und Rechnungen erstellen?',
        'para' => [
            '1' => 'Der Mitarbeiter muss sich mit seinen Zugangsdaten anmelden. Nach dem Einloggen kann der Mitarbeiter <b>Schnellverkauf</b> und <b>Rückerstattung</b> durchführen. Mitarbeiter haben nicht alle Rechte, die der Shopbesitzer hat. Alle Verkäufe des Mitarbeiters werden in Transaktionen gespeichert. Wenn Sie die <b>Transaktionen</b> ansehen, können Sie jede Rechnung sehen, wer den Verkauf getätigt hat und zu welcher Uhrzeit.',
        ]

    ],

    'faq6' => [
        'quest' => 'Wie kann ich die Transaktionen sehen?',
        'para' => [
            '1' => 'Das Menü „<b>Transaktion</b>“ befindet sich auf der linken Seite. Sie sehen eine detaillierte Liste aller Transaktionen – wer den Verkauf getätigt hat und zu welcher Uhrzeit. Sie können auch auf eine Transaktion klicken und weitere Details anzeigen. Hier können Sie auch einen Beleg oder eine Rechnung drucken.',
        ]

    ],

    'faq7' => [
        'quest' => 'Wie nutzt man die Voreinstellungen?',
        'para' => [
            '1' => 'Das Menü „<b>Voreinstellungen</b>“ befindet sich links. Wenn Sie darauf klicken, wird die Seite Voreinstellungen geöffnet. Hier können Sie Ihre Optionen aktivieren oder deaktivieren.',
            '2' => '<b>Pflegen Sie die UVP?</b> Wenn Sie diese Option aktivieren, werden Sie auf der Seite „<b>Inventar hinzufügen</b>“ oder in Ihrer XLS-/CSV-Datei (bei Masseneingabe) aufgefordert, die <b>UVP</b> des Produkts einzugeben.',
            '3' => '<b>Möchten Sie die UVP in der Rechnung anzeigen?</b> Wenn Sie diese Option aktivieren, wird die <b>UVP</b> im Beleg oder in der Rechnung angezeigt. Hinweis: Ohne die UVP-Option zu aktivieren können Sie die Anzeige der UVP in der Rechnung nicht aktivieren.',
            '4' => '<b>Möchten Sie den Bestand verwalten?</b> Wenn Sie diese Option aktivieren, müssen Sie beim Hinzufügen von Inventarartikeln zwingend die aktuelle Lagerbestandsmenge eingeben. Hinweis: Standardmäßig wird „<b>Lagermenge</b>“ auf der Seite „<b>Inventar hinzufügen</b>“ angezeigt, ist aber nicht erforderlich. Wenn Sie diese Option hier aktivieren, wird die Lagermenge zwingend erforderlich.',
            '5' => '<b>Benötigen Sie einen HSN/SAC-Code?</b> HSN steht für „<b>Harmonized System of Nomenclature</b>“. Dieser Code wird zur Klassifizierung von Produkten verwendet. Jedes Produkt hat seine eigene eindeutige HSN-Nummer. Die <b>Liste der HSN-Codes &amp; GST</b> finden Sie hier <a href="https://cleartax.in/s/gst-hsn-lookup" target="_blank">https://cleartax.in/s/gst-hsn-lookup</a>. Sie können HSN hier aktivieren oder deaktivieren. Wenn Sie den HSN/SAC-Code deaktivieren, müssen Sie ihn nicht in Ihre Inventardaten eingeben.',
            '6' => '<b>Möchten Sie den HSN/SAC-Code in der Rechnung anzeigen?</b> Wenn Sie diese Option aktiviert haben, müssen Sie auch „Benötigen Sie einen HSN/SAC-Code?“ aktivieren. Wenn Sie diese Option aktivieren, wird der HSN-Code im Beleg oder in der Rechnung gedruckt.',
        ]

    ],

    'faq8' => [
        'quest' => 'Ich habe Bestand hinzugefügt, aber er funktioniert nicht richtig.',
        'para' => [
            '1' => 'Damit der Bestand wie gewünscht funktioniert, müssen Sie zuerst die Lageroption im Menü „Voreinstellungen“ aktivieren.',
        ]

    ],

    'faq9' => [
        'quest' => 'Ich habe auf das MIC getippt und nichts erscheint.',
        'para' => [
            '1' => 'Bitte vergewissern Sie sich, dass Sie Inventar hinzugefügt haben. Klicken Sie auf „Inventar ansehen“ und überprüfen Sie dies.',
        ]

    ],


    'faq10' => [
        'quest' => 'Im Datensatz sehe ich viele Zeilen in Rot.',
        'para' => [
            '1' => 'Mögliche Gründe sind: Es gibt Duplikate oder eine Abweichung in den akzeptierten Werten. Alle Warnungen und Gründe werden oberhalb des Datensatzes angegeben, um Ihnen bei der Behebung zu helfen. Manchmal sind Artikel, die bereits in Ihrem Inventar vorhanden sind, auch im Datensatz vorhanden. Der Datensatz zeigt sie dann in Rot an, weil sie jetzt Duplikate sind.',
        ]

    ],

    'faq11' => [
        'quest' => 'Wie kann ich den Drucker verbinden und verwenden?',
        'para' => [
            '1' => 'Die ReadyBill-Desktop-Webanwendung (https://www.readybill.app/) unterstützt jeden Drucker Ihrer Wahl. Zusätzlich können Sie die kleinen thermischen Bluetooth-Drucker verwenden.',
            '2' => 'Die ReadyBill-Mobile-App unterstützt sowohl 80-mm- als auch 50-mm-Bluetooth-Thermodrucker. Einige 80-mm-Thermodrucker unterstützen jedoch kein iPhone. Sobald Apple Unterstützung bereitstellt oder das Problem für die 80-mm-Thermodrucker behebt, werden sie mit der App funktionieren.',
            '3' => 'Um einen Thermodrucker mit Ihrem Android-Gerät zu verwenden, koppeln Sie einfach Ihr Gerät mit Ihrem Mobiltelefon. Nach dem Koppeln werden die Drucker in Ihrer Liste der Bluetooth-Geräte angezeigt und auch in der App erscheinen. Tippen Sie im Menü „Drucker“ Ihrer App, und der gewünschte Drucker erscheint.',
            '4' => 'Um einen Thermodrucker mit Ihrem iOS-Gerät zu verwenden, koppeln Sie einfach Ihr Gerät mit Ihrem Mobiltelefon. Nach dem Koppeln werden die Drucker in Ihrer Liste der Bluetooth-Geräte angezeigt und auch in der App erscheinen. Tippen Sie im Menü „Drucker“ Ihrer App, und der gewünschte Drucker erscheint.',
        ]

    ],


    'Thank you for your query' => 'Thank you for your query',   ### new entry ###
    'We will review it and get back to you' => 'We will review it and get back to you',   ### new entry ###

];