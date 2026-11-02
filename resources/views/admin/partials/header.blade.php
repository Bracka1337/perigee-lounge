@include('partials.panel-header', [
    'home' => 'admin.dashboard',
    'badge' => __('header-admin'),
    'links' => [
        ['route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'label' => __('admin-header-dashboard')],
        ['route' => 'admin.users.index', 'active' => 'admin.users.*', 'label' => __('admin-header-users')],
        ['route' => 'admin.tables.index', 'active' => 'admin.tables.*', 'label' => __('admin-header-tables')],
        ['route' => 'admin.translations.index', 'active' => 'admin.translations.*', 'label' => __('admin-header-translations')],
        ['route' => 'admin.logs.index', 'active' => 'admin.logs.*', 'label' => __('admin-header-logs')],
    ],
])
