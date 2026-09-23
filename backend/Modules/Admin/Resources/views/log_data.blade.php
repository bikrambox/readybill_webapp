@extends('admin::layouts.admin')

@section('content')
    <div class="container-fluid p-4">

        <h1 class="mb-4">Issues Log</h1>

        {{-- Date Picker --}}
        <div class="mb-3">
            <label for="date" class="form-label">Select Date</label>
            <input type="date" id="date" name="date" value="{{ $selectedDate }}" class="form-control w-auto"
                onchange="window.location.href='{{ route('admin.log.data') }}?date='+this.value">
        </div>

        @if(empty($items))
            <p class="text-muted">
                No issues found for {{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}.
            </p>
        @else

            <div class="card">
                <div class="card-body p-0">

                    {{-- TOP HORIZONTAL SCROLL --}}
                    <div id="topScroll" class="overflow-auto"></div>

                    {{-- MAIN SCROLL AREA (Vertical + Bottom Horizontal) --}}
                    <div id="tableScroll" class="table-responsive" style="max-height:500px; overflow:auto;">

                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    @foreach($columns as $col)
                                        <th class="text-nowrap">{{ $col }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                    <tr>
                                        @foreach($columns as $col)
                                                        <td class="text-nowrap">
                                                            {{ is_scalar($item[$col] ?? null) || is_null($item[$col] ?? null)
                                            ? ($item[$col] ?? '')
                                            : json_encode($item[$col]) }}
                                                        </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>

        @endif
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {

            const topScroll = document.getElementById('topScroll');
            const tableScroll = document.getElementById('tableScroll');
            const table = tableScroll.querySelector('table');

            // Create inner width for top scroll
            const innerDiv = document.createElement('div');
            innerDiv.style.width = table.scrollWidth + 'px';
            innerDiv.style.height = '1px';
            topScroll.appendChild(innerDiv);

            // Sync horizontal scrolling
            topScroll.addEventListener('scroll', function () {
                tableScroll.scrollLeft = topScroll.scrollLeft;
            });

            tableScroll.addEventListener('scroll', function () {
                topScroll.scrollLeft = tableScroll.scrollLeft;
            });

            window.addEventListener('resize', function () {
                innerDiv.style.width = table.scrollWidth + 'px';
            });

        });
    </script>

@endsection