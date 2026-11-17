@include('partials.panel-header', [
    'home' => 'admin.dashboard',
    'badge' => __('header-admin'),
    'links' => [
        ['route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'label' => __('admin-header-dashboard')],
        ['route' => 'admin.users.index', 'active' => 'admin.users.*', 'label' => __('admin-header-users')],
        ['route' => 'admin.floor-plan', 'active' => 'admin.floor-plan', 'label' => __('admin-header-floor-plan')],
        ['route' => 'admin.opening-hours.index', 'active' => 'admin.opening-hours.*', 'label' => __('admin-header-opening-hours')],
        ['route' => 'admin.translations.index', 'active' => 'admin.translations.*', 'label' => __('admin-header-translations')],
        ['route' => 'admin.logs.index', 'active' => 'admin.logs.*', 'label' => __('admin-header-logs')],
    ],
])
