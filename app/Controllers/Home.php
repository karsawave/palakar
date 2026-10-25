<?php

namespace Controllers;

use Resources;

class Home extends Resources\Controller
{
    /** Points per file in the welcome page plot. */
    private const BINS = 48;

    /** Plot, legend and catalog all follow this order. */
    private const GROUPS = ['Core', 'Resources', 'Resources/Interfaces', 'Drivers/Abstraction', 'Drivers/Database', 'Drivers/Session', 'Drivers/Cache'];

    public function index()
    {
        return $this->output('layouts/main', [
            'title' => 'Palakar ' . \Gear::VERSION,
            'content' => $this->output('home', [
                'debug' => $this->configMain['debug'],
                'files' => $this->coreFiles(),
            ]),
        ]);
    }

    /**
     * Every PHP file of the core with its line count and the average
     * indentation along the file, so the welcome page draws the real code.
     * ponytail: reads the whole core (~6,400 lines) per request; cache it if
     * this page ever matters for speed.
     */
    private function coreFiles(): array
    {
        $files = [];

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(GEAR, \FilesystemIterator::SKIP_DOTS)) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $lines = file($file->getPathname(), FILE_IGNORE_NEW_LINES) ?: [''];
            $sum = $count = array_fill(0, self::BINS, 0);

            foreach ($lines as $i => $line) {
                $bin = intdiv($i * self::BINS, count($lines));
                $sum[$bin] += strlen($line) - strlen(ltrim($line));
                $count[$bin]++;
            }

            $path = str_replace('\\', '/', substr($file->getPathname(), strlen(GEAR)));
            $files[] = [
                'path' => $path,
                'group' => str_contains($path, '/') ? dirname($path) : 'Core',
                'lines' => count($lines),
                'profile' => array_map(fn($s, $c) => $c ? $s / $c : 0, $sum, $count),
            ];
        }

        $rank = fn($file) => [array_search($file['group'], self::GROUPS, true), $file['path']];
        usort($files, fn($a, $b) => $rank($a) <=> $rank($b));

        return $files;
    }
}
