<?php

namespace app;

class ConfigDefault implements Config
{
    /**
     * Describe how hungry or full the pet is, on a scale. 5 is terminal (game over). 
     */
    public static function hungryStatusLabel()
    {
        return [
            -3 => 'vomiting',
            -2 => 'queezy',
            -1 => 'bloated',
            0 => 'full',
            1 => 'peckish',
            2 => 'hungry',
            3 => 'starvng',
            4 => 'collapsed',
            5 => 'dead (game over)',
        ];
    }
    
    /**
     * Describe how happy or lonely the pet is, on a scale. 5 is terminal (game over). 
     */
    public static function happyStatusLabel()
    {
        return [
            -3 => 'run away',
            -2 => 'irritable',
            -1 => 'spoiled',
            0 => 'happy',
            1 => 'hoping',
            2 => 'lonely',
            3 => 'dejected',
            4 => 'morose',
            5 => 'found another home (game over)',
        ];
    }
    
    /**
     * Hours since last feed 
     */
    public static function feedScale()
    {
        return [
            5 => 120,
            4 => 96,
            3 => 36,
            2 => 24,
            1 => 8,
            0 => 0
        ];
    }
    
    /**
     * Hours since last stroke 
     */
    public static function strokeScale()
    {
        return [
            5 => 360,
            4 => 120,
            3 => 48,
            2 => 27,
            1 => 21,
            0 => 0
        ];
    }
    
    /**
     * Feeding X times within Y hours gives Z state
     */
    public static function overfeed()
    {
        return [
            [
                'hours' => 6,
                'limit' => 4,
                'state' => -3
            ],
            [
                'hours' => 12,
                'limit' => 3,
                'state' => -2
            ],    
            [
                'hours' => 18,
                'limit' => 2,
                'state' => -1
            ]
        ];
    }
    
    /**
     * Stroking X times within Y hours gives Z state
     */
    public static function overstroke()
    {
        return [
            [
                'hours' => 6,
                'limit' => 4,
                'state' => -3
            ],
            [
                'hours' => 18,
                'limit' => 3,
                'state' => -2
            ],
            [
                'hours' => 21,
                'limit' => 2,
                'state' => -1
            ]
        ];
    }
}
?>