{{-- Floor Plan Zones Overlay Component --}}
<svg class="absolute inset-0 w-full h-full pointer-events-none" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid slice">
    <!-- Терраса (B1-B7) - левая сторона -->
    <g class="zone-terrace">
        <rect x="0" y="45" width="22.5" height="55" fill="rgb(168, 85, 247)" opacity="0.20" />
    </g>

    <!-- Зал 1 (T1-T7) - верхняя часть, центр-левая -->
    <g class="zone-hall1">
        <rect x="22.8" y="0" width="40" height="45" fill="rgb(59, 130, 246)" opacity="0.20" />
    </g>

    <!-- Зал Плейстейшн (P1-P2) - верхняя правая -->
    <g class="zone-playstation">
        <rect x="77.5" y="35" width="35" height="27" fill="rgb(236, 72, 153)" opacity="0.20" />
    </g>

    <!-- Зал 2 (Z1-Z8) - центр и нижняя часть -->
    <g class="zone-hall2">
        <rect x="22.8" y="45" width="55" height="55" fill="rgb(34, 197, 94)" opacity="0.20" />
    </g>

    <!-- VIP (VIP) - нижняя правая -->
    <g class="zone-vip">
        <rect x="77.9" y="62.3" width="25" height="35" fill="rgb(251, 146, 60)" opacity="0.20" />
    </g>

    <g class="zone-borders" stroke-width="0.3">
        <!-- Граница между Террасой и другими -->
        <line x1="22.5" y1="45" x2="22.5" y2="100" stroke="rgb(168, 85, 247)" opacity="0.40" />
        <line x1="22.5" y1="45" x2="0" y2="45" stroke="rgb(168, 85, 247)" opacity="0.40" />
        
        <!-- Граница между Залом 1 и центром -->
        <line x1="22.8" y1="45" x2="22.8" y2="0" stroke="rgb(59, 130, 246)" opacity="0.40" />
        <line x1="22.8" y1="45" x2="62.8" y2="45" stroke="rgb(59, 130, 246)" opacity="0.40" />
        <line x1="62.8" y1="45" x2="62.8" y2="0" stroke="rgb(59, 130, 246)" opacity="0.40" />
        
        <!-- Граница между Залом Плейстейшн и остальным -->
        <line x1="77.5" y1="35" x2="77.5" y2="62" stroke="rgb(236, 72, 153)" opacity="0.40" />
        <line x1="77.5" y1="62" x2="100" y2="62" stroke="rgb(236, 72, 153)" opacity="0.40" />
        <line x1="77.5" y1="35" x2="100" y2="35" stroke="rgb(236, 72, 153)" opacity="0.40" />
        
        <!-- Граница между Залом 2 и VIP -->
        <line x1="22.8" y1="45" x2="22.8" y2="100" stroke="rgb(34, 197, 94)" opacity="0.40" />
        <line x1="22.8" y1="45.3" x2="77.6" y2="45.3" stroke="rgb(34, 197, 94)" opacity="0.40" />
        <line x1="77.5" y1="100" x2="77.5" y2="45" stroke="rgb(34, 197, 94)" opacity="0.40" />

        <!-- Граница VIP -->
        <line x1="77.9" y1="100" x2="77.9" y2="62.3" stroke="rgb(251, 146, 60)" opacity="0.40" />
        <line x1="77.9" y1="62.3" x2="100" y2="62.3" stroke="rgb(251, 146, 60)" opacity="0.40" />
    </g>
</svg>

<style>
    .zone-terrace,
    .zone-hall1,
    .zone-playstation,
    .zone-hall2,
    .zone-vip {
        transition: opacity 0.3s ease;
    }
</style>
