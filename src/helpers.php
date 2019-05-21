<?php

function consoleProgressBar($done, $total, $info = "", $unit = '') {
    if(!defined("STDERR"))
        return;

    if($total == 0)
        $perc = 0;
    else
        $perc = floor(($done / $total) * 100);

    $left = 100 - $perc;
    $write = sprintf("\033[0G\033[2K{$info} [%'={$perc}s>%-{$left}s] - $perc%% - $done$unit/$total$unit", "", "");

    fwrite(STDERR, $write);
}
