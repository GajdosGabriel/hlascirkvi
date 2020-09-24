<?php
    namespace App\Inspect;


    class Spam {


        protected $text = 'text z body sergej.mihal@mail.com dfasd fa sf';

        protected $invalidWords = [
            'Sergej.mihal@gmail.com',
            'body',
//            KeyWords::class
        ];



        public function detect()
        {

         $this->invalidKeywords();

         return false;
        }

        protected function invalidKeywords()
        {
            foreach($this->invalidWords as $invalidWord)
            {
                if( stripos($this->text, $invalidWord))
                {
                  dd($invalidWord);
                }
            }

        }

    }

