<?php
/**
 * Render Rating Component
 * Reusable helper for displaying star ratings
 */
function renderRating($rating, $count = null) {
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5 ? true : false;
    $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
    
    $html = '<div class="flex items-center gap-1.5">';
    
    // Full stars
    for ($i = 0; $i < $fullStars; $i++) {
        $html .= '<i class="fas fa-star text-amber-400 text-sm"></i>';
    }
    
    // Half star
    if ($halfStar) {
        $html .= '<i class="fas fa-star-half-alt text-amber-400 text-sm"></i>';
    }
    
    // Empty stars
    for ($i = 0; $i < $emptyStars; $i++) {
        $html .= '<i class="far fa-star text-slate-200 text-sm"></i>';
    }
    
    if ($count !== null) {
        $html .= '<span class="ml-2 text-xs font-black text-slate-400 uppercase tracking-tighter">' . number_format($rating, 1) . ' (' . $count . ')</span>';
    }
    
    $html .= '</div>';
    return $html;
}
?>