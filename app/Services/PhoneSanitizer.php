<?php
namespace App\Services;


class PhoneSanitizer
{

    protected $phone;

    public function __construct($phone)
    {
        $this->phone = $phone;
    }

    public function getSanitized()
    {
        $tempPhone = trim($this->phone);
        $tempPhone = str_replace(['+421', '+', ' '], '', $tempPhone);

        if (is_numeric($tempPhone) && strlen($tempPhone)===9) {
            $tempPhone = '+421 ' . substr($tempPhone, 0, 3) . ' ' . substr($tempPhone, 3, 3) . ' ' . substr($tempPhone, 6, 3);
        } elseif (strlen($tempPhone) <= 5) {
            $tempPhone = null;
        } else {
            $tempPhone = trim($this->phone);
        }

        return $tempPhone;
    }
}