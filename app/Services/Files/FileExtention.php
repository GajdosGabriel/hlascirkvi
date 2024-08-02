<?php

namespace App\Services\Files;




trait FileExtention
{

    function getExtention()
    {
        return $this->image->extension();
    }
}
