<?php

namespace App\Command;

use App\Entity\PostCodeData;
use App\Repository\PostCodeDataRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\HttpFoundation\Session\Session;


class DownloadPostcodeCommand extends Command 
{
 
    //php bin/console app:uk-post-codes            //call this application on the console using this line
    //php bin/console list

    protected static $defaultName = 'app:uk-post-codes';               //this shows when help is run on the console //(php bin/console list)
    protected static $defaultDescription = 'Download UK postcodes & migrate to database';    //this is shown when help is run on the console  (php bin/console list)
    public $doctrine;

    function __construct(ManagerRegistry $doctrine)
    {
        $this -> doctrine = $doctrine;
        parent::__construct();
        
    }
                                              
    protected function configure(): void                              //configure() is called just after __contructor
    {
        $this -> setHelp('This command allows you to download UK postcodes');    
        
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        //####################################
        //request user to enter a post code
        //####################################

        $helper = $this->getHelper('question');
        $questionConfirm = new ConfirmationQuestion('To see & save details of a POSTCODE, enter "y" (yes)  or "n" (no)', false);

        if (!$helper->ask($input, $output, $questionConfirm)) {
            
            $output->writeln('Want to try again ?');
            return 0;

        }else{

            $question = new Question('Please enter a postcode: ');
            $question->setTrimmable(true);

            // basic postcode validation to remove space and limit entry to between 3 to 7 characters
            $cleanPostcode = $helper->ask($input, $output, $question);
            $cleanPostcode = preg_replace('/[^A-Za-z0-9]/', '', $cleanPostcode); // allow only aphalnumeric values
            $cleanPostcode = str_replace(' ', '', strtoupper($cleanPostcode));
            
            // returns if postcode is not valid
            if (strlen($cleanPostcode) < 3 || strlen($cleanPostcode) > 7) {
                $output->writeln('Error: Please enter a valid POSTCODE! (3 to 7 characters)');
                return 0;
            }
                

            //get post details from url json
            //$output->writeln('Lenght is: '.strlen($capsPostcode).' '.$capsPostcode);

            $url = 'https://mapit.mysociety.org/postcode/'.$cleanPostcode;    
            
            $json = file_get_contents($url);                                  
            
            //used only for checks after exceeding 50 calls
            //$json = '{"wgs84_lat": 51.52038508142051, "coordsyst": "G", "shortcuts": {"WMC": 65613, "ward": 8217, "council": 2486}, "wgs84_lon": -0.4687752451592552, "postcode": "UB7 8AZ", "easting": 506336, "areas": {"900000": {"parent_area": null, "generation_high": 19, "all_names": {}, "id": 900000, "codes": {}, "name": "House of Commons", "country": "", "type_name": "UK Parliament", "generation_low": 1, "country_name": "-", "type": "WMP"}, "900002": {"parent_area": 900006, "generation_high": 45, "all_names": {}, "id": 900002, "codes": {}, "name": "London Assembly", "country": "E", "type_name": "London Assembly area (shared)", "generation_low": 1, "country_name": "England", "type": "LAE"}, "163685": {"parent_area": null, "generation_high": 45, "all_names": {}, "id": 163685, "codes": {"gss": "E30000266"}, "name": "Slough and Heathrow", "country": "E", "type_name": "Travel to Work Areas", "generation_low": 38, "country_name": "England", "type": "TTW"}, "900006": {"parent_area": null, "generation_high": 45, "all_names": {}, "id": 900006, "codes": {}, "name": "London Assembly", "country": "E", "type_name": "London Assembly area", "generation_low": 1, "country_name": "England", "type": "LAS"}, "2247": {"parent_area": null, "generation_high": 45, "all_names": {}, "id": 2247, "codes": {"gss": "E61000001", "unit_id": "41441"}, "name": "Greater London Authority", "country": "E", "type_name": "Greater London Authority", "generation_low": 1, "country_name": "England", "type": "GLA"}, "11819": {"parent_area": 2247, "generation_high": 45, "all_names": {}, "id": 11819, "codes": {"gss": "E32000006", "unit_id": "41442"}, "name": "Ealing and Hillingdon", "country": "E", "type_name": "London Assembly constituency", "generation_low": 1, "country_name": "England", "type": "LAC"}, "65613": {"parent_area": null, "generation_high": 45, "all_names": {}, "id": 65613, "codes": {"gss": "E14001007", "unit_id": "24936"}, "name": "Uxbridge and South Ruislip", "country": "E", "type_name": "UK Parliament constituency", "generation_low": 13, "country_name": "England", "type": "WMC"}, "164978": {"parent_area": null, "generation_high": 45, "all_names": {}, "id": 164978, "codes": {"gss": "E43000207"}, "name": "Hillingdon, unparished area", "country": "E", "type_name": "Non-civil parished area", "generation_low": 44, "country_name": "England", "type": "NCP"}, "68691": {"parent_area": null, "generation_high": 45, "all_names": {}, "id": 68691, "codes": {"ons": "E01002550"}, "name": "Hillingdon 022E", "country": "E", "type_name": "Lower Layer Super Output Area (Full)", "generation_low": 13, "country_name": "England", "type": "OLF"}, "2486": {"parent_area": null, "generation_high": 45, "all_names": {}, "id": 2486, "codes": {"ons": "00AS", "gss": "E09000017", "local-authority-eng": "HIL", "unit_id": "11539", "local-authority-canonical": "HIL"}, "name": "Hillingdon Borough Council", "country": "E", "type_name": "London borough", "generation_low": 1, "country_name": "England", "type": "LBO"}, "8217": {"parent_area": 2486, "generation_high": 45, "all_names": {}, "id": 8217, "codes": {"ons": "00ASHE", "gss": "E05000345", "unit_id": "11563"}, "name": "Yiewsley", "country": "E", "type_name": "London borough ward", "generation_low": 1, "country_name": "England", "type": "LBW"}, "34394": {"parent_area": null, "generation_high": 45, "all_names": {}, "id": 34394, "codes": {"ons": "E02000515"}, "name": "Hillingdon 022", "country": "E", "type_name": "Middle Layer Super Output Area (Full)", "generation_low": 13, "country_name": "England", "type": "OMF"}, "165087": {"parent_area": null, "generation_high": 45, "all_names": {}, "id": 165087, "codes": {"gss": "E38000256"}, "name": "NHS North West London CCG", "country": "E", "type_name": "Clinical Commissioning Group", "generation_low": 45, "country_name": "England", "type": "CCG"}, "164863": {"parent_area": null, "generation_high": 45, "all_names": {}, "id": 164863, "codes": {"gss": "E12000007", "unit_id": "41428"}, "name": "London", "country": "E", "type_name": "English Region", "generation_low": 44, "country_name": "England", "type": "ER"}}, "northing": 181274}';


            $json_data = json_decode($json, true);          //contains the json data (array type) to be added to database
            //$output->writeln(gettype($json));

            $output->writeln([
                '                           ',
                '===========================',
                '    Your Postcode details  ',
                '===========================',
                '                           ',
            ]);
            $output->writeln(json_encode($json_data, JSON_PRETTY_PRINT));


            //Move the json data to database
            $output->writeln([
                '                           ',
                '===========================',
                '    Move data to database  ',
                '===========================',
                '                           ',
            ]);


            if(!$json_data){
                return 0;
            }
            
            //save postcode data to database            
            $postcodeDataX = new PostCodeData();
            $postcodeDataX -> setPostcodejson($json_data);   //$this->jsonPostCodeData
        
            //$doctrine = new ManagerRegistry();
            $entityManager = $this -> doctrine->getManager();
            $entityManager->persist($postcodeDataX);
            $entityManager->flush();
            
             
            $output->writeln('Your postcode data has been successfully saved');

            return Command::SUCCESS;


        }

        

    }

}











