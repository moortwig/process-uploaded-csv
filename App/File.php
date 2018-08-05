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
     * @param $handle
     * @param $content
     */
    public function write($handle, $content)
    {
        fwrite($handle, $content);
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

    /**
     * @param $pid
     * @param $fileName
     * @return bool
     */
    public function createFile($pid, $fileName)
    {
        $path = $this->getPath('lock');
        $handle = $this->open($path . $fileName, 'w');

        if (is_resource($handle)) {
            $this->write($handle, $pid . PHP_EOL);
            $this->close($handle);

            return true;
        } else {
            Logger::log(LOG_ERR, 'Failed to create file ' . $fileName);

            return false;
        }
    }

    public function fileExists($folder, $fileName)
    {
        $path = $this->getPath($folder);

        return file_exists($path . $fileName);
    }

    public function removeFile($folder, $fileName)
    {
        $path = $this->getPath($folder);

        return unlink($path . $fileName);
    }
}