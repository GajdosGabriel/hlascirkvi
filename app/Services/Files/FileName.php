<?php

namespace App\Services\Files;



trait FileName
{


    public function getName()
    {
        return substr($this->model->slug, 0, 255) . '-' . rand(1000, 90000);
    }

   public function getOrginalName()
    {
        return substr($this->image->getClientOriginalName(), 0, 255);
    }

    private function generateUniqueName()
    {
        return $this->getName() . '-' . rand(1000, 90000);
    }
}
