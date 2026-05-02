@extends('layouts.app')

@section('content')
    <div class="container py-4 font-sans-serif">

        @include('admin.partials.header_stats')

        <ul class="nav nav-pills mb-4 gap-2" id="adminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link active px-4 py-2 fw-bold shadow-sm d-flex align-items-center rounded-pill transition-all"
                    id="users-tab" data-bs-toggle="tab" data-bs-target="#users-content" type="button" role="tab">
                    <i class="fas fa-users me-2"></i>Users
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link px-4 py-2 fw-bold shadow-sm d-flex align-items-center rounded-pill transition-all"
                    id="sessions-tab" data-bs-toggle="tab" data-bs-target="#sessions-content" type="button" role="tab">
                    <i class="fas fa-calendar-alt me-2"></i>Sessions
                </button>
            </li>
        </ul>

        <div class="tab-content" id="adminTabsContent">
            <div class="tab-pane fade show active" id="users-content" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden transition-all" id="users-table-wrapper">
                    @include('admin.partials.users_table')
                </div>
            </div>

            <div class="tab-pane fade" id="sessions-content" role="tabpanel">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden transition-all" id="sessions-table-wrapper">
                    @include('admin.partials.sessions_table')
                </div>
            </div>
        </div>

    </div>

    @include('admin.partials.create_user_modal')

    <style>
        /* --- GLOBAL FONTS & UTILS --- */
        .font-sans-serif {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .transition-all {
            transition: all 0.2s ease-in-out;
        }

        /* --- TAB STYLING --- */
        .nav-pills .nav-link {
            background-color: #ffffff;
            color: #64748b;
            border: 1px solid #e2e8f0;
            font-size: 0.95rem;
            letter-spacing: 0.3px;
        }

        /* Active Tab: Brand Teal */
        .nav-pills .nav-link.active {
            background-color: var(--bs-secondary) !important;
            /* Teal */
            color: white !important;
            border-color: var(--bs-secondary);
            box-shadow: 0 4px 10px rgba(10, 125, 135, 0.25) !important;
        }

        .nav-pills .nav-link:hover:not(.active) {
            background-color: #f8fafc;
            border-color: #cbd5e1;
            color: var(--bs-primary);
            transform: translateY(-1px);
        }

        /* --- TABLE CONTAINER FIXES --- */
        /* Allow dropdowns (like the 3 dots action menu) to overflow the table visually */
        .table-responsive {
            overflow: visible !important;
        }

        #users-table-wrapper,
        #sessions-table-wrapper {
            overflow: visible !important;
        }

        /* Make sure tables inside the rounded card don't have weird outer borders */
        .card>.table-responsive>.table {
            margin-bottom: 0;
        }

        .table> :not(caption)>*>* {
            padding: 1rem 1.25rem;
            vertical-align: middle;
        }

        /* --- PAGINATION STYLING --- */
        .pagination {
            justify-content: center;
            margin-bottom: 0;
            gap: 6px;
            /* Spacing between pagination pills */
            padding: 1rem;
        }

        .page-item .page-link {
            border-radius: 8px;
            /* Soft squares */
            border: 1px solid #e2e8f0;
            color: #475569;
            padding: 8px 14px;
            font-size: 0.9rem;
            font-weight: 600;
            background-color: #ffffff;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
        }

        .page-item .page-link:hover {
            background-color: #f1f5f9;
            color: var(--bs-primary);
            border-color: #cbd5e1;
            transform: translateY(-1px);
            z-index: 2;
        }

        .page-item.active .page-link {
            background-color: var(--bs-secondary);
            /* Teal */
            border-color: var(--bs-secondary);
            color: #ffffff;
            box-shadow: 0 3px 6px rgba(10, 125, 135, 0.2);
        }

        .page-item.disabled .page-link {
            background-color: #f8fafc;
            border-color: #f1f5f9;
            color: #cbd5e1;
            box-shadow: none;
        }

        /* "Showing X results" text styling */
        div.d-none.d-sm-flex.align-items-center.justify-content-between {
            flex-direction: column;
            gap: 12px;
            padding: 15px 20px;
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            border-radius: 0 0 1rem 1rem;
            /* Match card rounding at the bottom */
        }

        div.d-none.d-sm-flex.align-items-center.justify-content-between>div:first-child {
            color: #64748b;
            font-size: 0.85rem;
            font-weight: 500;
        }
    </style>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            function fetch_data(url, wrapperId) {
                // Add a smooth fade out effect while loading
                $(wrapperId).css('opacity', '0.6');
                $.ajax({
                    url: url,
                    success: function(data) {
                        $(wrapperId).html(data);
                        $(wrapperId).css('opacity', '1');
                    },
                    error: function() {
                        alert('Could not load data. Please refresh.');
                        $(wrapperId).css('opacity', '1');
                    }
                });
            }

            $(document).on('click', '#users-table-wrapper .pagination a', function(event) {
                event.preventDefault();
                fetch_data($(this).attr('href'), '#users-table-wrapper');
            });

            $(document).on('click', '#sessions-table-wrapper .pagination a', function(event) {
                event.preventDefault();
                fetch_data($(this).attr('href'), '#sessions-table-wrapper');
            });
        });
    </script>
@endpush
