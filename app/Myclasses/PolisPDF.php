<?php

namespace App\Myclasses;
use Illuminate\Http\Request;

class PolisPDF
{


    //public function __construct($phone, $text)
    //{
    //    $this->phone = $phone;
    //    $this->text = $text;
    //}

    public function genPolicyPdf($number, $address, $policyholder, $date_of_birth, $validity, $plan, $comes_into_force, $valid_until, $policy, $phone, $email){
        $pdf_path = $_SERVER['DOCUMENT_ROOT'] . "/policies/{$number}.pdf";
        $url = '';
        if ($plan) {
            $url = 'https://my.inapp.insure/certgen?policy='.$policy.'&number='.$number.'&address='.$address.'&policyholder='.$policyholder.'&phone='.$phone.'&email='.$email.'&date_of_birth='.$date_of_birth.'&validity='.$validity.'&cost='.$plan->price.'&$plan=' . $plan->id .'&coverage=' . $plan->coverage . '&period=' . $plan->period . '&comes_into_force='.$comes_into_force.'&valid_until='.$valid_until;
        } else {
            $url = 'https://my.inapp.insure/certgen?policy='.$policy.'&number='.$number.'&address='.$address.'&policyholder='.$policyholder.'&phone='.$phone.'&email='.$email.'&date_of_birth='.$date_of_birth.'&validity='.$validity.'&cost='.$plan->price.'&$plan=' . 0 .'&coverage=' . 1000000  . '&comes_into_force='.$comes_into_force.'&valid_until='.$valid_until;
        }
        if (!file_exists($pdf_path)) {
            $command = 'google-chrome --no-sandbox --headless --disable-gpu --print-to-pdf="' . $pdf_path . '" "'.$url.'"';
            exec($command);
        }
        
        //return [$pdf_path, $url];
        $content = file_get_contents($pdf_path);

        header("Content-type:application/pdf");
        header("Content-Disposition:inline;filename=Страховой полис.pdf");
        //readfile($pdf_path);
        unlink($pdf_path);
        
        echo $content;
        //return $content;
    }
}
