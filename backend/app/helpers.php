<?php

if (!function_exists('category_name')) {
    /**
     * 获取分类的中文名称
     */
    function category_name(string $category): string
    {
        return match($category) {
            'general' => '综合讨论',
            'tech' => '技术交流',
            'study' => '学习心得',
            'question' => '问题求助',
            default => $category,
        };
    }
}

if (!function_exists('pet_type_name')) {
    /**
     * 获取宠物类型的中文名称
     */
    function pet_type_name(string $type): string
    {
        return match($type) {
            'dog' => '🐕 狗狗',
            'cat' => '🐱 猫咪',
            'other' => '🐾 其他',
            default => $type,
        };
    }
}

if (!function_exists('pet_status_name')) {
    /**
     * 获取宠物状态的中文名称
     */
    function pet_status_name(string $status): string
    {
        return match($status) {
            'lost' => '走失中',
            'found' => '已找到',
            'closed' => '已关闭',
            default => $status,
        };
    }
}

if (!function_exists('pet_status_badge_class')) {
    /**
     * 获取宠物状态的Badge样式类
     */
    function pet_status_badge_class(string $status): string
    {
        return match($status) {
            'lost' => 'bg-red-50 text-red-700',
            'found' => 'bg-green-50 text-green-700',
            'closed' => 'bg-gray-50 text-gray-700',
            default => 'bg-neutral-100 text-neutral-600',
        };
    }
}

if (!function_exists('pet_marker_color')) {
    /**
     * 获取地图标记颜色
     */
    function pet_marker_color(string $status, string $petType): string
    {
        if ($status === 'found') return '#10b981';
        if ($status === 'closed') return '#6b7280';
        return $petType === 'dog' ? '#ef4444' : ($petType === 'cat' ? '#f59e0b' : '#8b5cf6');
    }
}
