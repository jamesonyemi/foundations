<?php

namespace App\Helpers;

class PDFEngine
{
    protected $module;

    protected $model;

    protected $download;

    protected $creator;

    public function __construct($module, $model, $download = true)
    {
        $this->module = $module;
        $this->model = $model;
        $this->download = $download;
        $this->creator = $this->getPdfCreator();
    }

    public function pdf()
    {
        return $this->creator->pdf($this->model, $this->download);
    }

    public function getPdfCreator()
    {
        $className = 'Modules\\'.ucfirst($this->module).'\\Helpers\\'.ucfirst(get_option('pdf_engine')).'Generator';

        if (! class_exists($className)) {
            throw new \Exception('Module pdf generator implementation '.$className.' missing');
        }

        return new $className();
    }
}
