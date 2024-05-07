<?php

namespace Taketool\Sysinfo\Utility;

use Closure;

class SysinfoUtility
{
    /**
     * @param array $array
     * @param string $key
     * @return array
     */
    public static function sort(array $array, string $key): array
    {
        usort($array, self::build_sorter($key));
        return $array;
    }
    public static function sortReverse(array $array, string $key): array
    {
        usort($array, self::build_sorter_reverse($key));
        return $array;
    }

    /**
     * sort array by certain key, works together with self::sort()
     * @param string $key
     * @return Closure
     */
    public static function build_sorter(string $key): Closure
    {
        return function ($a, $b) use ($key) {
            return strnatcmp($a[$key], $b[$key]);
        };
    }
    public static function build_sorter_reverse(string $key): Closure
    {
        return function ($a, $b) use ($key) {
            return strnatcmp($b[$key], $a[$key]);
        };
    }

    public static function debug_hexdump($string, $lines=10) {
        if (true) //($_SESSION['debug'] & DEBUG_HEXDUMP)
        {
            $hexdump = '';
            echo '<style>'."\n"
                .' td { font-family: monospace; line-height: 1;}'."\n"
                .'</style>'."\n";
            // hexdump display
            $hexdump .= '<table border="0" cellpadding="0" cellspacing="2" bgcolor="Silver"><tr><td>'."\n";
            $hexdump .= '<table border="0" cellpadding="1" cellspacing="1" bgcolor="White"><tr bgcolor="Silver"><th>0</th><th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th>6</th><th>7</th><th>&nbsp;</th><th>8</th><th>9</th><th>A</th><th>B</th><th>C</th><th>D</th><th>E</th><th>F</th><th>&nbsp;</th><th>ascii</th></tr>'."\n";
            for ($i=0; $i<$lines; $i++){
                $hexdump .= '<tr>';
                $chrview = "";
                for ($j=0; $j<16; $j++) {
                    $chr = substr($string, $i*16+$j, 1);
                    $asc = ord($chr);
                    $hexdump .= "<td>".bin2hex($chr)."</td>";
                    if ($j==7) { $hexdump .= "<td>&nbsp;</td>"; }
                    if (($asc >31) and ($asc <128)) {
                        $chrview .= chr($asc);
                    } else {
                        $chrview .= ".";
                    }
                }
                $hexdump .= "<td>&nbsp;</td><td>".$chrview."</td>\n";
                $hexdump .= "</tr>";
            }
            $hexdump .= "</table>\n";
            $hexdump .= '</td></tr></table>'."\n";
            echo $hexdump;
        }
    }

}