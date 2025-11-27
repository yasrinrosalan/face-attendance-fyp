@extends('layouts.app')

@section('content')
    @include('admin.partials.header_stats')

    <ul class="nav nav-pills mb-4 gap-2" id="adminTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active px-4 py-2 fw-bold shadow-sm d-flex align-items-center" id="users-tab"
                data-bs-toggle="tab" data-bs-target="#users-content" type="button" role="tab">
                <i class="fas fa-users me-2"></i>Users
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link px-4 py-2 fw-bold shadow-sm d-flex align-items-center" id="sessions-tab"
                data-bs-toggle="tab" data-bs-target="#sessions-content" type="button" role="tab">
                <i class="fas fa-calendar-alt me-2"></i>Sessions
            </button>
        </li>
    </ul>

    <div class="tab-content" id="adminTabsContent">

        <div class="tab-pane fade show active" id="users-content" role="tabpanel">
            <div class="card border-0 shadow-sm" id="users-table-wrapper">
                @include('admin.partials.users_table')
            </div>
        </div>

        <div class="tab-pane fade" id="sessions-content" role="tabpanel">
            <div class="card border-0 shadow-sm" id="sessions-table-wrapper">
                @include('admin.partials.sessions_table')
            </div>
        </div>
    </div>

    @include('admin.partials.create_user_modal')

    <style>
        /* --- TAB STYLING --- */
        .nav-pills .nav-link {
            background-color: #fff;
            color: #64748b;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        /* Allow dropdowns to overflow the table */
        .table-responsive {
            overflow: visible !important;
        }

        /* Ensure the card doesn't clip it either */
        #users-table-wrapper,
        #sessions-table-wrapper {
            overflow: visible !important;
        }

        /* Active Tab: Use Brand Teal for a nice contrast */
        .nav-pills .nav-link.active {
            background-color: var(--bs-secondary) !important;
            /* Teal */
            color: white !important;
            border-color: var(--bs-secondary);
            box-shadow: 0 4px 6px -1px rgba(10, 125, 135, 0.2) !important;
        }

        .nav-pills .nav-link:hover:not(.active) {
            background-color: #f8f9fa;
            border-color: #cbd5e1;
            color: var(--bs-primary);
        }

        /* --- PAGINATION STYLING --- */

        /* Center the buttons */
        .pagination {
            justify-content: center;
            margin-bottom: 0;
            gap: 5px;
            /* Adds space between page numbers */
        }

        /* Default Page Link Style */
        .page-item .page-link {
            border-radius: 6px;
            /* Rounded corners */
            border: 1px solid #e2e8f0;
            color: #475569;
            /* Dark gray text */
            padding: 6px 12px;
            font-size: 0.9rem;
            font-weight: 500;
            background-color: #ffffff;
            transition: all 0.2s;
        }

        /* Hover State */
        .page-item .page-link:hover {
            background-color: #f1f5f9;
            color: var(--bs-primary);
            border-color: #cbd5e1;
            z-index: 2;
        }

        /* Active Page (Current) - Matches Tab Color */
        .page-item.active .page-link {
            background-color: var(--bs-secondary);
            /* Teal */
            border-color: var(--bs-secondary);
            color: #ffffff;
            box-shadow: 0 2px 4px rgba(10, 125, 135, 0.2);
        }

        /* Disabled State (Previous/Next when unavailable) */
        .page-item.disabled .page-link {
            background-color: #f8fafc;
            border-color: #f1f5f9;
            color: #cbd5e1;
        }

        /* Style the "Showing X results" text */
        div.d-none.d-sm-flex.align-items-center.justify-content-between {
            flex-direction: column;
            gap: 10px;
            padding-top: 10px;
        }

        div.d-none.d-sm-flex.align-items-center.justify-content-between>div:first-child {
            /* The text part */
            color: #94a3b8;
            /* Muted gray */
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
    </style>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            function fetch_data(url, wrapperId) {
                $(wrapperId).css('opacity', '0.5');
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
