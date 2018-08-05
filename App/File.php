<?php

namespace App;

use ErrorException;

class File
{

    /**
     * @param $fullPath
     * @param $mode
     * @return bool|resource
     */
    public function open($fullPath, $mode)
    {
        return fopen($fullPath, $mode);
    }

    /**
     *
     * @param $handle
     */
    public function close($handle)
    {
        fclose($handle);
    }

    /**
     * @param $currentFolder
     * @param $newFolder
     * @param $fileName
     */
    public function moveFileTo($currentFolder, $newFolder, $fileName)
    {
        try {
            rename($this->getPath($currentFolder) . $fileName, $this->getPath($newFolder) . $fileName);
        } catch (ErrorException $e) {
            dd($e->getMessage());
        }

    }

    /**
     * @return array|bool
     */
    public function getFiles()
    {
        $path = $this->getPath('uploaded');

        return $this->scanFolder($path);
    }

    /**
     * @param $folder
     * @return string
     */
    public function getPath($folder)
    {
        $config = new Config();
        $path = $config->basePath . $config->{$folder . 'Folder'};

        if (!file_exists($path)) {
            mkdir($path);
        }

        return $path;
    }


    /**
     * Scan for files and filter results from anything besides .csv files.
     *
     * @param $path
     * @return array
     */
    public function scanFolder($path)
    {
        $files = scandir($path);

        return array_filter($files, function ($item) {
            $fileInfo = new \SplFileInfo($item);

            if ($fileInfo->getExtension() === 'csv') {
                return $item;
            }
        });
    }
}