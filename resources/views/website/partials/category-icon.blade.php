@php
$categoryIcons = [
    'smartphones' => '📱', 'laptops' => '💻', 'headphones' => '🎧',
    'smartwatches' => '⌚', 'cameras' => '📷', 'accessories' => '🔌',
    'tablets' => '📲', 'gaming' => '🎮',
];
$icon = $categoryIcons[strtolower($category->slug ?? $category->name)] ?? '📦';
@endphp
<span class="gaget-cat-placeholder">{{ $icon }}</span>
