<?php

namespace App\Controller;

use App\Entity\PostCodeData;
use App\Repository\PostCodeDataRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class PostcodeController extends AbstractController 
{

   
    /**
     * @Route("/searchdata/{searchX}", name="searchdatax")
     */
    public function showPostCodeData(string $searchX, Request $request, ManagerRegistry $doctrine): response
    {
    
        //this normalizes errors like 'S e98JU', 'sE 98JU' or 'SE98J U'
        $searchX = strtoupper($searchX);                        //convert search string to Uppercase
        $searchX = preg_replace('/[^A-Za-z0-9]/', '', $searchX); // allow only aphalnumeric values
        $searchX = str_replace(' ', '', strtoupper($searchX));   //remove any space in the postcode to allow the below code add the right space
        $searchX = trim($searchX, ' ');                         //trim spaces on both edges


        // returns if postcode is not valid
        if (strlen($searchX) < 3 || strlen($searchX) > 7) {
            $response = new Response(json_encode(['Message'=>'Please enter a valid POSTCODE! (3 to 7 characters)']));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        

        //create the normal empty space in a postcode

        if(strlen($searchX) > 3 || strlen($searchX) == 6 && !substr($searchX, -3, 1) == null){
            
            $searchX = substr_replace($searchX, ' ', 3, 0);         //add space to a postcode written together (Eg: SE98JU to SE9 8JU)
            
        }      
        elseif(strlen($searchX) == 7 && !substr($searchX, -3, 1) == null){
            
            $searchX = substr_replace($searchX, ' ', 4, 0);         //add space to a postcode written together (Eg: SEV98JU to SEV9 8JU)
            
        }

        
        //find postcodes in the database
        $findPostCode = $doctrine->getRepository(PostCodeData::class)->findPostCodeData($searchX);  

        if(!$findPostCode)
        {
            $response = new Response(json_encode(['Message'=>'No data']));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }


        $jsonResult = json_encode(['Search result'=>$findPostCode], JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
        $jsonResult = preg_replace('/\\\"/',"", $jsonResult);

        $response = new Response($jsonResult);    //$findPostCode[5]['postcodejson']['postcode']
        $response->headers->set('Content-Type', 'application/json');
        return $response;

    }





    /**
     * @Route("/latlong/{lat}/{long}", name="latlongx")
     */
    public function showLatLongPostCode(string $lat, string $long, Request $request, ManagerRegistry $doctrine): response
    {
        //NOTE that I used string as hint bc if float is used and alphabets are entered, it would throw error
        //so the strings are converted to float later
             
        //latitude
        $lat = preg_replace('/[^0-9.-]/', '', $lat);               // allow only numeric values
        $lat = str_replace(' ', '', strtoupper($lat));          //remove any space in the latitude value   
        $lat = trim($lat, ' ');                                 //trim spaces on both edges

        //longitude
        $long = preg_replace('/[^0-9.-]/', '', $long);               // allow only numeric values
        $long = str_replace(' ', '', strtoupper($long));          //remove any space in the latitude value   
        $long = trim($long, ' '); 


        //convert strings to float
        $lat = floatval($lat);    
        $long = floatval($long);

        //check if lat & long are floats
        if(!is_float($lat) || !is_float($long) ){
            $response = new Response(json_encode(['Message'=>'Please enter valid values. EG: 53.12324']));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }


        //round up the lat & long for ease of search
        $lat = round($lat, 10);
        $long = round($long, 10);

        
        //check count of digits in lat & long
        if(strlen($lat) < 6 || strlen($long) < 6) {
            $response = new Response(json_encode(['Message'=>'Please increase values (maximum 10)']));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }


                
        $findPostCode = $doctrine->getRepository(PostCodeData::class)->findLatLongPostCode($lat, $long);  

        if(!$findPostCode)
        {
            $response = new Response(json_encode(['Message'=>'No data']));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        $jsonResult = json_encode(['Search result'=>$findPostCode], JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
        $jsonResult = preg_replace('/\\\"/',"", $jsonResult);

        $response = new Response($jsonResult);    //$findPostCode[5]['postcodejson']['postcode']
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }




}

