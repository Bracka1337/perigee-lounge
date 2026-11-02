@include('partials.panel-header', [
    'home' => 'staff.dashboard',
    'badge' => __('staff-header-badge'),
    'links' => [
        ['route' => 'staff.dashboard', 'active' => 'staff.dashboard', 'label' => __('staff-header-dashboard-link')],
        ['route' => 'staff.menu-categories.index', 'active' => ['staff.menu-categories.*', 'staff.menu-items.*'], 'label' => __('staff-header-menu-link')],
        ['route' => 'staff.reservations.index', 'active' => 'staff.reservations.*', 'label' => __('staff-header-reservations-link')],
        ['route' => 'staff.orders.index', 'active' => 'staff.orders.*', 'label' => __('staff-header-orders-link')],
        ['route' => 'staff.promo-codes.index', 'active' => 'staff.promo-codes.*', 'label' => __('staff-header-promo-codes-link')],
    ],
])
