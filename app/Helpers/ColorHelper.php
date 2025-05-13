<?php

namespace App\Helpers;

class ColorHelper
{
    /**
     * Retourne une couleur de texte (noir/blanc) contrastante avec la couleur de fond.
     *
     * @param string $hexColor Code hexadécimal (#RRGGBB)
     * @return string Code hexadécimal de la couleur du texte (#000000 ou #FFFFFF)
     */
    public static function getTextColor(?string $hexColor): string
    {
        if (!$hexColor) {
            return '#000000'; // Couleur de secours
        }
        
        $hexColor = ltrim($hexColor, '#');
        
        if (strlen($hexColor) !== 6) {
            return '#000000'; // Fallback en cas de format incorrect
        }
        
        $r = hexdec(substr($hexColor, 0, 2));
        $g = hexdec(substr($hexColor, 2, 2));
        $b = hexdec(substr($hexColor, 4, 2));
        
        $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

        return ($yiq >= 128) ? '#000000' : '#FFFFFF';
    }
}
