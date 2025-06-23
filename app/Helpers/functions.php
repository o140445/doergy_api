<?php

// 获取随机颜色
function get_random_color(): string
{
    $colors = [
        '#67c23a', // green
        '#f56c6c', // red
        '#909399', // gray
        '#e6a23c', // orange
        '#409eff', // blue
        '#909399', // dark gray
        '#f0c674', // yellow
        '#8e44ad', // purple
        '#1abc9c', // turquoise
        '#3498db', // light blue
    ];
    return $colors[array_rand($colors)];
}
