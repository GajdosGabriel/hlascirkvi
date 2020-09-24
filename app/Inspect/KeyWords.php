<?php
    namespace App\Inspect;


    use Illuminate\Support\Str;

    class KeyWords {

        protected $keywords = [
            'Sergej.mihal@gmail.com',
            'body',
        ];

        public function detect($body)
        {
            foreach($this->keywords as $word)
            {
             $aaa[] = Str::slug($word);

            }
//            return $this->keywords;

            return $aaa;
        }

    }

