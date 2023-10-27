@php($logout_url = View::getSection('logout_url') ?? config('adminlte.logout_url', 'logout'))
@php($profile_url = View::getSection('profile_url') ?? config('adminlte.profile_url', 'logout'))

@if (config('adminlte.usermenu_profile_url', false))
    @php($profile_url = Auth::user()->adminlte_profile_url())
@endif

@if (config('adminlte.use_route_url', false))
    @php($profile_url = $profile_url ? route($profile_url) : '')
    @php($logout_url = $logout_url ? route($logout_url) : '')
@else
    @php($profile_url = $profile_url ? url($profile_url) : '')
    @php($logout_url = $logout_url ? url($logout_url) : '')
@endif
<!-- Navbar -->

<!-- Icons -->
<!-- Notifications -->
<?php 
use Carbon\Carbon;  
use App\Models\PurchaseInvoice;
use App\Models\SalesInvoice;
    $startDate = Carbon::today();
    $endDate = Carbon::today()->addDays(7);

    $purchaseinvoice = PurchaseInvoice::select('*')
    ->whereBetween('purchase_invoice_due_date' ,[$startDate, $endDate])
    ->simplePaginate(3);

    $salesinvoice = SalesInvoice::select('*')
    ->whereBetween('sales_invoice_due_date' ,[$startDate, $endDate])
    ->simplePaginate(3);


    $countPurchaseInv = count($purchaseinvoice);
    $countinvoiceInv = count($salesinvoice);

    $Count =  $countPurchaseInv + $countinvoiceInv;
    // var_dump($Count);
?>
        

<li class="nav-item dropdown user-menu">
                    <a href='#addtstock' data-toggle='modal' name="Find" class="btn btn-info btn-sm" title="Add Data">
                        <i class="fas fa-bell text-black"></i>
                        <span class="badge rounded-pill badge-notification bg-danger">{{ $Count }}</span>
                    </a>
   
</li>


<li class="nav-item dropdown user-menu">

    {{-- User menu toggler --}}
    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
        @if (config('adminlte.usermenu_image'))
            <img src="{{ Auth::user()->adminlte_image() }}" class="user-image img-circle elevation-2"
                alt="{{ Auth::user()->name }}">
        @endif
        <span @if (config('adminlte.usermenu_image')) class="d-none d-md-inline" @endif>
            {{ Auth::user()->name }}
            <i class='fas fa-user-alt'></i>
        </span>
    </a>

    {{-- User menu dropdown --}}
    <ul class="dropdown-menu dropdown-menu-right" style="width:60px !important">

        {{-- User menu header --}}
        @if (!View::hasSection('usermenu_header') && config('adminlte.usermenu_header'))
            <li
                class="user-header {{ config('adminlte.usermenu_header_class', 'bg-primary') }}
                @if (!config('adminlte.usermenu_image')) h-auto @endif">
                @if (config('adminlte.usermenu_image'))
                    <img src="{{ Auth::user()->adminlte_image() }}" class="img-circle elevation-2"
                        alt="{{ Auth::user()->name }}">
                @endif
                <p class="@if (!config('adminlte.usermenu_image')) mt-0 @endif">
                    {{ Auth::user()->name }}
                    @if (config('adminlte.usermenu_desc'))
                        <small>{{ Auth::user()->adminlte_desc() }}</small>
                    @endif
                </p>
            </li>
        @else
            @yield('usermenu_header')
        @endif

        {{-- Configured user menu links --}}
        @each('adminlte::partials.navbar.dropdown-item', $adminlte->menu('navbar-user'), 'item')

        {{-- User menu body --}}
        @hasSection('usermenu_body')
            <li class="user-body">
                @yield('usermenu_body')
            </li>
        @endif

        {{-- User menu footer --}}
        <li class="user-footer">
            @if ($profile_url)
                <div>
                    <a href="{{ $profile_url }}" class="btn btn-sm btn-flat">
                        <i class="fa fa-fw fa-user"></i>
                        {{ __('adminlte::menu.profile') }}
                    </a>
                </div>
            @endif
            <div>
                <a class="btn btn-sm btn-flat" href="{{ url('/system-user/change-password', Auth::id()) }}">
                    {{-- <i class='fas fa-sync-alt'></i> --}}
                    Change Password
                </a>
            </div>
            <div>
                <a class="btn btn-sm btn-flat" href="#"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    {{-- <i class="fa fa-fw fa-power-off"></i> --}}
                    {{ __('adminlte::adminlte.log_out') }}
                </a>
            </div>
            <form id="logout-form" action="{{ $logout_url }}" method="POST" style="display: none;">
                @if (config('adminlte.logout_method'))
                    {{ method_field(config('adminlte.logout_method')) }}
                @endif
                {{ csrf_field() }}
            </form>
        </li>

    </ul>

</li>
