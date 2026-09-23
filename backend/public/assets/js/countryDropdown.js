// ------------------------------------------------------- COUNTRY CODE ------------------------------------------------------
// let apiUrl = "/api/countries-json"; // Laravel API route to get country list
let apiUrl = api_url;
let countryOptions = [];
let countryMap = {}; // Store countries with their short codes
let countryCurrenyMap = {};
let countrySeperatorMap = {};

// // X-API-KEY-SECRET
// var x_api_key_secret = "eyJpdiI6IklobHR5dk80RC96cktsOGN3elV4cHc9PSIsInZhbHVlIjoiai9PV2pGMmxlT083bUJmYVVXY3ZldjZSMnViUW1TV2tiSVpNN3U0SXR3YkFac2FweUlrZUNRNFp0QUs5bUJaWSIsIm1hYyI6IjE3OWVjNTYzNGI5NTQ3MDUyM2I5MWM5NWJjNGQ3M2RjOTUzMjg4MTFhNDcxYjYxZjg2MWRjZDBjYmYyNjYyNGQiLCJ0YWciOiIifQ==";
// // X-API-KEY-SECRET

// console.log('countryDropdown.js', api_url);

$.ajax({
    url: apiUrl + "countries-json",
    type: "GET",
    headers: {
        "X-API-Secret": '"' + x_api_key_secret + '"',
    },
    dataType: "json",
    success: function (countries) {
        let countryDropdown = $(".countryCode");

        console.log('countries', countries);

        // Sort countries alphabetically by name
        countries.sort((a, b) => a.name.localeCompare(b.name));

        countries.forEach(country => {
            let countryName = country.name;
            let countryCode = country.code; // ISO Alpha-2 code (e.g., IN, US)
            let dialCode = country.dial_code;
            let currency_symbol = country.currency_symbol;
            let decimal_separator = country.decimal_separator;
            let flagUrl = country.flag;

            if (dialCode) { // Only include countries with a dial code
                countryOptions.push({
                    id: countryCode,
                    text: `${countryName} (${dialCode})`,
                    name: countryName.toLowerCase(), // For faster matching
                    dialCode: dialCode,
                    flag: flagUrl,
                    shortText: `${dialCode}`,
                    'data-dial_code': dialCode,
                });

                countryMap[countryCode] = dialCode; // Map country short name to dial code
                countryCurrenyMap[countryCode] = currency_symbol;
                countrySeperatorMap[countryCode] = decimal_separator;
            }
        });

        // Initialize Select2 WITHOUT searchbox
        countryDropdown.select2({
            data: countryOptions,
            templateResult: formatCountryList, // List view: Flag + Full name + Code
            templateSelection: formatSelectedCountry, // Selected view: Flag + Code only
            minimumResultsForSearch: -1, // Disable search box completely
            width: 'auto', // Keeps the selected option compact
            dropdownParent: $("body"), // Ensures dropdown is not clipped inside small containers
            closeOnSelect: true, // Close dropdown after selection
        });

        // Type-to-jump state management
        let typedSequence = '';
        let typeTimeout;
        let currentHighlightedIndex = -1;

        // Enhanced keyboard navigation: Pure type-to-jump without search
        countryDropdown.on('keydown', function (e) {
            let $this = $(this);
            let currentValue = $this.val();
            let keyCode = e.which;

            // Skip if already has selection
            if (currentValue) {
                typedSequence = '';
                currentHighlightedIndex = -1;
                clearTimeout(typeTimeout);
                return;
            }

            // Handle alphabetic keys A-Z (65-90)
            if (keyCode >= 65 && keyCode <= 90) {
                let key = String.fromCharCode(keyCode).toLowerCase();
                e.preventDefault(); // Prevent any default behavior

                // Clear previous timeout and reset sequence after 800ms inactivity
                clearTimeout(typeTimeout);
                typeTimeout = setTimeout(() => {
                    typedSequence = '';
                    currentHighlightedIndex = -1;
                }, 800);

                // Add key to sequence
                typedSequence += key;
                console.log('Typing sequence:', typedSequence);

                // Find matches based on country name (not full text)
                let matches = countryOptions.filter(function (opt) {
                    // Match if name starts with sequence (most natural behavior)
                    return opt.name.startsWith(typedSequence);
                });

                // If no exact prefix matches, try contains match
                if (matches.length === 0) {
                    matches = countryOptions.filter(function (opt) {
                        return opt.name.includes(typedSequence);
                    });
                }

                // Sort matches: first by alphabetical order, then by relevance
                matches.sort(function (a, b) {
                    return a.name.localeCompare(b.name);
                });

                if (matches.length > 0) {
                    // Open dropdown to show the matching options
                    if (!$this.select2('isOpen')) {
                        $this.select2('open');
                    }

                    // Highlight the first match
                    let bestMatch = matches[0];
                    $this.val(null).trigger('change'); // Clear any previous selection
                    currentHighlightedIndex = countryOptions.findIndex(opt => opt.id === bestMatch.id);

                    // Trigger selection of the best match
                    $this.val(bestMatch.id).trigger('change.select2');

                    // Visual feedback
                    setTimeout(() => {
                        let $selection = $this.next('.select2-container').find('.select2-selection__rendered');
                        $selection.addClass('jump-highlight');
                        setTimeout(() => $selection.removeClass('jump-highlight'), 300);

                        console.log(`Jumped to: ${bestMatch.text} via "${typedSequence}" (${matches.length} matches)`);
                    }, 50);

                    // Auto-close after brief display or keep open for further navigation
                    setTimeout(() => {
                        if (matches.length === 1) { // Only auto-close if unique match
                            $this.select2('close');
                        }
                    }, 500);

                } else {
                    // No matches found - show all options with brief flash
                    if (!$this.select2('isOpen')) {
                        $this.select2('open');
                        setTimeout(() => $this.select2('close'), 1000);
                    }

                    // Visual feedback for no match
                    let $selection = $this.next('.select2-container').find('.select2-selection__rendered');
                    $selection.addClass('no-match-flash');
                    setTimeout(() => $selection.removeClass('no-match-flash'), 300);

                    console.log('No country matches for:', typedSequence);
                    typedSequence = key; // Keep last key for next attempt
                }

                return false; // Prevent further propagation

                // Handle Arrow Keys for navigation
            } else if (keyCode === 40 || keyCode === 38) { // Down / Up Arrow
                e.preventDefault();

                if (!$this.select2('isOpen')) {
                    $this.select2('open');
                    currentHighlightedIndex = 0;
                    return false;
                }

                // Navigate within open dropdown
                let totalOptions = countryOptions.length;
                if (keyCode === 40) { // Down
                    currentHighlightedIndex = (currentHighlightedIndex + 1) % totalOptions;
                } else if (keyCode === 38) { // Up
                    currentHighlightedIndex = (currentHighlightedIndex - 1 + totalOptions) % totalOptions;
                }

                // Highlight the current option in dropdown
                $('.select2-results__option').removeClass('jump-highlight');
                setTimeout(() => {
                    let $option = $('.select2-results__option').eq(currentHighlightedIndex);
                    if ($option.length) {
                        $option.addClass('jump-highlight');
                        $option[0].scrollIntoView({ block: 'nearest' });
                    }
                }, 10);

                return false;

                // Handle Enter key
            } else if (keyCode === 13) { // Enter
                e.preventDefault();

                if (currentHighlightedIndex >= 0 && countryOptions[currentHighlightedIndex]) {
                    let selectedOption = countryOptions[currentHighlightedIndex];
                    $this.val(selectedOption.id).trigger('change.select2');
                    $this.select2('close');
                    currentHighlightedIndex = -1;
                } else if ($this.select2('isOpen')) {
                    // Let Select2 handle default Enter behavior when open
                    return true;
                }

                return false;

                // Handle Tab key
            } else if (keyCode === 9) { // Tab
                // If we have a current highlight, select it before tabbing away
                if (currentHighlightedIndex >= 0 && countryOptions[currentHighlightedIndex] && !currentValue) {
                    let selectedOption = countryOptions[currentHighlightedIndex];
                    $this.val(selectedOption.id).trigger('change.select2');
                    $this.select2('close');
                }
                // Allow tab to proceed to next element
                return true;

                // Handle Escape key
            } else if (keyCode === 27) { // Escape
                typedSequence = '';
                currentHighlightedIndex = -1;
                $this.select2('close');
                return false;

                // Spacebar - open dropdown if closed
            } else if (keyCode === 32 && !currentValue) {
                e.preventDefault();
                $this.select2('open');
                return false;
            }

            // Allow other keys (numbers, symbols) to pass through normally
            // This prevents interference with other form controls
        });

        // Reset state on blur
        countryDropdown.on('blur', function () {
            setTimeout(() => {
                typedSequence = '';
                currentHighlightedIndex = -1;
                clearTimeout(typeTimeout);
            }, 150);
        });

        // Reset on selection
        countryDropdown.on('select2:select', function () {
            typedSequence = '';
            currentHighlightedIndex = -1;
            clearTimeout(typeTimeout);
        });

        // Clean up highlights when dropdown closes
        countryDropdown.on('select2:close', function () {
            $('.select2-results__option').removeClass('jump-highlight');
            currentHighlightedIndex = -1;
        });

        // OPTIONAL: Apply data attributes to the actual <option> tags
        countryOptions.forEach(opt => {
            let $option = countryDropdown.find(`option[value="${opt.id}"]`);
            if ($option.length) {
                $option.attr('data-dial_code', opt['data-dial_code']);
            }
        });

    },
    error: function () {
        console.error("Failed to load country data.");
        // alert("Failed to load country data.");
    }
});

// Function to format the dropdown list (flag + full country name + dial code)
function formatCountryList(country) {
    if (!country.id) return country.text;

    let flag = country.flag;
    return $(`<span class="select2-option d-flex align-items-center">${flag} ${country.text}</span>`);
}

// Function to format the selected option (flag + country code only)
function formatSelectedCountry(country) {
    if (!country.id) return country.text;

    let flag = country.flag;
    return $(`<div class="d-flex align-items-center">${flag} ${country.shortText}</div>`);
}

// Update input field placeholder when country code is selected
$(".countryCode").on("change", function () {
    let selectedCode = $(this).val();
    $("#mobile").attr("placeholder", "Enter your mobile number");
});

function getCountryCode() {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: apiUrl + 'country-code',
            type: 'GET',
            headers: {
                "X-API-Secret": '"' + x_api_key_secret + '"',
            },
            beforeSend: function () {
                $('.overlay').show();
            },
            success: function (response) {
                resolve(response); // Resolve the Promise with the data
                $('.overlay').hide();
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
                $('.overlay').hide();
                reject(error); // Reject the Promise with the error
            }
        });
    });
}

// ------------------------------------------------------- COUNTRY CODE ------------------------------------------------------
