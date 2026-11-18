<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\ServeCommand as BaseServeCommand;
use Illuminate\Support\Carbon;
use Throwable;

class ServeCommand extends BaseServeCommand
{
    /**
     * Get the full server command.
     * Fixes: "error: -S cannot be combined with script file server.php" on Wasmer / phpix
     *
     * @return array
     */
    protected function serverCommand()
    {
        return [
            (new \Symfony\Component\Process\PhpExecutableFinder)->find(false),
            '-S',
            $this->host().':'.$this->port(),
            '-t',
            base_path('public'),
        ];
    }

    /**
     * Get the port for the command.
     * Fixes: "Unsupported operand types: string + int" on PHP 8 / Wasmer
     *
     * @return int
     */
    protected function port()
    {
        $port = $this->input->getOption('port');

        if (is_null($port)) {
            [, $port] = $this->getHostAndPort();
        }

        $port = $port ?: 8000;

        // Force extraction of digits and cast to integer to prevent operand error
        $numericPort = (int) preg_replace('/[^0-9]/', '', (string) $port) ?: 8000;

        return $numericPort + (int) $this->portOffset;
    }

    /**
     * Get the date from the given PHP server output.
     * Fixes: "Undefined array key 1" when server line date parsing fails
     *
     * @param  string  $line
     * @return \Illuminate\Support\Carbon
     */
    protected function getDateFromLine($line)
    {
        try {
            return parent::getDateFromLine($line);
        } catch (Throwable $e) {
            return Carbon::now();
        }
    }
}
