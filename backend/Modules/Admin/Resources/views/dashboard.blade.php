@extends('admin::layouts.admin')

@section('content')

    @push('styles')
        <style>
            .hover-card {
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }

            .hover-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
            }

            /* currency badge styling */
            .currency-badge {
                display: inline-block;
                font-weight: 700;
                padding: 4px 8px;
                border-radius: 12px;
                background: rgba(0, 0, 0, 0.05);
                border: 1px solid rgba(0, 0, 0, 0.06);
                margin-right: 6px;
                min-width: 28px;
                text-align: center;
            }

            /* small visual improvements for top revenue multi-line */
            #totalRevenue .fw-bold {
                font-size: 1.2rem;
            }
        </style>
    @endpush


    <section class="section dashboard">
        <div class="row">

            <!-- ===== Dashboard Summary ===== -->
            <div class="col-lg-12 mb-4">
                <div class="row text-center">
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <i class="bi bi-cart text-primary fs-2 mb-2"></i>
                                <h5 class="card-title mb-1">Total Shops</h5>
                                <h4 id="totalShops" class="fw-bold mb-0">0</h4>
                            </div>
                        </div>
                    </div>

                    {{-- <div class="col-md-4 mb-3">
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <i class="bi bi-currency-dollar text-success fs-2 mb-2"></i>
                                <h5 class="card-title mb-1">Total Revenue</h5>
                                <h4 id="totalRevenue" class="fw-bold mb-0">₹0</h4>
                            </div>
                        </div>
                    </div> --}}

                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <i class="bi bi-globe2 text-info fs-2 mb-2"></i>
                                <h5 class="card-title mb-1">Total Countries</h5>
                                <h4 id="totalCountries" class="fw-bold mb-0">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ===== End Summary ===== -->

            <!-- ===== Country Wise Cards ===== -->
            <div class="col-lg-12">
                <div class="card border-0 shadow">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="bi bi-flag text-primary me-2"></i> Country-wise Revenue
                        </h5>

                        <div class="row" id="countryRevenueContainer">
                            <div class="col-12 text-center text-muted py-3">
                                Loading country-wise data...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ===== End Country Wise Cards ===== -->

        </div>
    </section>
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function () {

            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            function loadDashboardStats() {
                $.ajax({
                    type: 'GET',
                    url: '{{ route("admin.dashboard.stats.data") }}',
                    beforeSend: function () {
                        $('#countryRevenueContainer').html(
                            `<div class="col-12 text-center text-muted py-3">Fetching data...</div>`
                        );

                        // show loading in summary fields
                        $('#totalShops').text('0');
                        $('#totalCountries').text('0');
                        $('#totalRevenue').html('<small class="text-muted">Loading...</small>');

                    },
                    success: function (res) {
                        console.log('Dashboard Data:', res);

                        const shopData = res.shop_counts_by_country || [];
                        const revenueData = res.billing_totals_by_country || [];

                        // build revenue map per country code
                        const revenueMap = {};
                        revenueData.forEach(item => {
                            revenueMap[item.code] = {
                                total: parseFloat(item.total || 0),
                                currency: item.currency || item.code_currency || '',
                                currency_symbol: item.currency_symbol || item.currencySymbol || item.currency_symbol || ''
                            };
                        });

                        // Build totals grouped by currency
                        const totalsByCurrency = {};
                        // Also populate country cards
                        let html = '';
                        let totalShops = 0;

                        shopData.forEach(country => {
                            const revObj = revenueMap[country.code] || { total: 0, currency: country.currency || '', currency_symbol: country.currency_symbol || '₹' };
                            const rev = parseFloat(revObj.total || 0);
                            const currency = revObj.currency || country.currency || country.currency_code || country.code || '';
                            const symbol = revObj.currency_symbol || country.currency_symbol || country.currency_symbol || '₹';

                            totalShops += parseInt(country.count || 0);

                            // accumulate per-currency totals
                            if (!totalsByCurrency[currency]) {
                                totalsByCurrency[currency] = { total: 0, symbol: symbol };
                            }
                            totalsByCurrency[currency].total += rev;

                            const shopListBaseUrl = `{{ route('admin.shop.list', ['module_type' => '']) }}`;
                            const countryModuleType = country.module_type; // your JS variable

                            const finalUrl = `${shopListBaseUrl}/${countryModuleType}`;
                            console.log(finalUrl);

                            html += `
                                <div class="col-xl-3 col-md-4 col-sm-6 mb-4">
                                    <div class="card border-0 shadow-sm h-100 hover-card">
                                        <div class="card-body text-center">
                                            ${country.flag}
                                            <h6 class="fw-bold mb-1">${country.name || 'Unknown'}</h6>
                                            <p class="mb-1 small text-muted">${country.code}</p>
                                            <div class="mt-2">
                                                <div><i class="bi bi-shop text-primary me-1"></i> Shops: <strong>${country.count}</strong></div>
                                                <div>Revenue: <strong>${symbol}${formatNumber(rev)}</strong></div>
                                            </div>
                                            <a class="btn btn-link" href="${finalUrl}" role="button">View Shop List</a>
                                        </div>
                                    </div>
                                </div>`;
                        });

                        if (!html) {
                            html = `<div class="col-12 text-center text-muted py-3">No country data found</div>`;
                        }

                        $('#countryRevenueContainer').html(html);

                        // Update top summary cards
                        $('#totalShops').text(totalShops);
                        $('#totalCountries').text(shopData.length);

                        // Display totals grouped by currency in top revenue card
                        // We'll generate small badges / rows for each currency so we don't mix them.
                        const currencyHtmlParts = [];
                        for (const curr in totalsByCurrency) {
                            if (!Object.prototype.hasOwnProperty.call(totalsByCurrency, curr)) continue;
                            const c = totalsByCurrency[curr];
                            currencyHtmlParts.push(
                                `<div class="d-flex align-items-baseline justify-content-center mb-1">
                                        <span class="currency-badge me-2">${c.symbol}</span>
                                        <span class="fw-bold">${formatNumber(c.total)}</span>
                                        <small class="text-muted ms-2">(${curr})</small>
                                    </div>`
                            );
                        }

                        if (currencyHtmlParts.length === 0) {
                            $('#totalRevenue').html('₹0');
                        } else {
                            $('#totalRevenue').html(currencyHtmlParts.join(''));
                        }

                    },
                    error: function (err) {
                        console.error('Error:', err);
                        $('#countryRevenueContainer').html(
                            `<div class="col-12 text-center text-danger py-3">Error loading data</div>`
                        );
                        $('#totalRevenue').html('<span class="text-danger">Error</span>');
                    }
                });
            }

            // utility: format number with thousand separators
            function formatNumber(val) {
                if (isNaN(val)) return '0';
                // show no decimal by default; change if you need cents
                return Math.round(val).toLocaleString();
            }

            // Animated counter
            function animateCounter(selector, value) {
                $({ Counter: 0 }).animate({ Counter: value }, {
                    duration: 1000,
                    easing: 'swing',
                    step: function (now) {
                        $(selector).text(
                            selector === '#totalRevenue'
                                ? '₹' + Math.ceil(now).toLocaleString()
                                : Math.ceil(now)
                        );
                    }
                });
            }

            loadDashboardStats();

        });
    </script>

@endsection