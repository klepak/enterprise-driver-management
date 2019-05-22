<?php
class ConsoleProgressBar
{
    private $currentStep = 0;
    private $totalSteps = 0;

    private $padding = 0;
    private $message = '';
    private $unit = '';

    private $firstWrite = true;

    public function max($steps)
    {
        $this->totalSteps = $steps;

        return $this;
    }

    public function message($message)
    {
        $this->message = $message;

        return $this;
    }

    public function unit($unit)
    {
        $this->unit = $unit;

        return $this;
    }

    public function padding(int $padding)
    {
        $this->padding = $padding;

        return $this;
    }

    public function update($step)
    {
        $this->currentStep = $step;

        if($this->firstWrite)
        {
            for($i=0;$i<$this->padding;$i++)
                $this->write("\n");

            $this->firstWrite = false;
        }

        $this->write();
    }

    public function completed()
    {
        $this->currentStep = $this->totalSteps;

        $paddingLines = "";
        for($i=0;$i<$this->padding;$i++)
            $paddingLines .= "\n";

        if($this->write() !== false)
            fwrite(STDERR, "\n$paddingLines");
    }

    private function write($string = null)
    {
        if(!defined("STDERR"))
            return false;

        if($this->totalSteps == 0)
            $perc = 0;
        else
            $perc = floor(($this->currentStep / $this->totalSteps) * 100);

        $progressBarSize = 100 - strlen($this->message);
        $displayPerc = $perc / 100 * $progressBarSize;
        $left = (100-$perc) / 100 * $progressBarSize;

        if($string == null)
            $string = sprintf("\033[0G\033[2K{$this->message} [%'={$displayPerc}s>%-{$left}s] - $perc%% - {$this->currentStep}{$this->unit}/{$this->totalSteps}{$this->unit}", "", "");

        return fwrite(STDERR, $string);
    }
}
