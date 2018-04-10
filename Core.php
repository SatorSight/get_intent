<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'SUtils.php';
require 'Helper.php';

class Core{

    const TOKEN = 'LfU0GhXR6r6W7xWJzdU3sxVcUNrjL9ug';
    const ADVERTISER_INFO_URL = 'https://ui.getintent.com/api/v2/advertisers/<advertiser_id>/campaigns/all?token='.self::TOKEN;
    const CAMPAIGN_INFO_URL = 'https://ui.getintent.com/api/v2/advertisers/<advertiser_id>/campaigns/get/<campaign_id>?token='.self::TOKEN;
    const CAMPAIGN_EDIT_URL = 'https://ui.getintent.com/api/v2/advertisers/<advertiser_id>/campaigns/edit/<campaign_id>?token='.self::TOKEN;
    const IP_CHECKER_URL = 'http://ip.blinko.ru/api/';
    const MASKS_FOR_OP_ROUTE = self::IP_CHECKER_URL . 'get_op_masks/';
    const OP_FOR_MASK_ROUTE = self::IP_CHECKER_URL . 'get_mask_op/';

    private $advertiser_info;
    private $campaign_info;

    private $output;

    public function __construct(){
        $this->fillAdvertiserInfo();
        $this->fillCampaignInfo();

        $this->convertTargetingToIp();
        $this->convertTargetingToOp();
    }

    public function getOutput(){
        return $this->output;
    }

    public function getInfo(){
        if($this->advertiser_info)
            return $this->advertiser_info;
        if($this->campaign_info)
            return $this->campaign_info;
        return '';
    }

    public function convertTargetingToOp(){
        if (isset($_REQUEST['campaign_to_telco'])) {
            $advertiser_id = $_REQUEST['advertiser_id3'];
            $campaign_id = $_REQUEST['campaign_id2'];

            $data = file_get_contents(self::getCampaignInfoURL($advertiser_id, $campaign_id));
            $data = json_decode($data);

            $targeting = $data->targeting;

            if(empty($targeting->ip_ranges)){
                $this->dump('No ip ranges selected');
                if(!empty($targeting->carriers))
                    $this->dump('Already with carriers');
                return false;
            }

            $ip_ranges = $targeting->ip_ranges;

            $ops = [];

            foreach ($ip_ranges as $ip_mask){
                $op = self::getOpForMask($ip_mask);
                $op = strtolower($op);
                if(!in_array($op, $ops))
                    array_push($ops, $op);
            }

            $data->targeting->carriers = array_values($ops);
            unset($data->targeting->ip_ranges);

            $url = self::getCampaignEditURL($advertiser_id, $campaign_id);
            $data = json_encode($data);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data)));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response  = curl_exec($ch);
            curl_close($ch);

            $this->dump('Done');
        }
        return true;
    }

    public function convertTargetingToIp(){
        if(isset($_REQUEST['campaign_to_ip_range'])){
            $advertiser_id = $_REQUEST['advertiser_id3'];
            $campaign_id = $_REQUEST['campaign_id2'];

            $data = file_get_contents(self::getCampaignInfoURL($advertiser_id, $campaign_id));
            $data = json_decode($data);

            $targeting = $data->targeting;

            if(empty($targeting->carriers)){
                $this->dump('No telcos selected');
                if(!empty($targeting->ip_ranges))
                    $this->dump('Already with ip ranges');
                return false;
            }

            $carriers = $targeting->carriers;

            $ip_masks = [];
            foreach($carriers as $carrier)
                $ip_masks = array_merge($ip_masks, self::getIpMasksForOp($carrier));

            if(empty($ip_masks)){
                $this->dump('No ip masks found');
                return false;
            }

            $ip_masks = array_filter($ip_masks, function($m){
                if(strpos($m, '/') === false)
                    return false;
                if(substr_count($m, '.') < 3)
                    return false;

                return true;
            });

            $ip_masks = array_map(function($mask){
                return trim($mask);
            }, $ip_masks);

            $targeting->ip_ranges = array_values($ip_masks);

            $data->targeting = $targeting;
            unset($data->targeting->carriers);

            $url = self::getCampaignEditURL($advertiser_id, $campaign_id);
            $data = json_encode($data);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data)));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response  = curl_exec($ch);
            curl_close($ch);

            $this->dump('Done');
        }
        return true;
    }

    public function fillAdvertiserInfo(){
        if(isset($_REQUEST['advertiser'])){
            $advertiser_id = $_REQUEST['advertiser_id'];
            $data = file_get_contents(self::getAdvertiserInfoURL($advertiser_id));
            $this->advertiser_info = json_decode($data);
        }
    }

    public function fillCampaignInfo(){
        if(isset($_REQUEST['campaign'])){
            $advertiser_id = $_REQUEST['advertiser_id2'];
            $campaign_id = $_REQUEST['campaign_id'];
            $data = file_get_contents(self::getCampaignInfoURL($advertiser_id, $campaign_id));
            $this->campaign_info = json_decode($data);
        }
    }

    private static function getIpMasksForOp($op){
        $url = self::MASKS_FOR_OP_ROUTE . $op;
        $json = file_get_contents($url);
        $data = json_decode($json);

        return $data;
    }

    private static function getOpForMask($mask){
        $url = self::OP_FOR_MASK_ROUTE . SUtils::full_url_encode($mask);
        $json = file_get_contents($url);
        $data = json_decode($json);

        return $data;
    }

    private static function getAdvertiserInfoURL($advertiser_id) : string {
        $url = self::ADVERTISER_INFO_URL;
        $url = str_replace('<advertiser_id>', $advertiser_id, $url);
        return $url;
    }

    private static function getCampaignEditURL($advertiser_id, $campaign_id) : string {
        $url = self::CAMPAIGN_EDIT_URL;
        $url = str_replace('<advertiser_id>', $advertiser_id, $url);
        $url = str_replace('<campaign_id>', $campaign_id, $url);
        return $url;
    }

    private static function getCampaignInfoURL($advertiser_id, $campaign_id) : string {
        $url = self::CAMPAIGN_INFO_URL;
        $url = str_replace('<advertiser_id>', $advertiser_id, $url);
        $url = str_replace('<campaign_id>', $campaign_id, $url);
        return $url;
    }

    private function dump($string){
        $this->output .= SUtils::to_s($string, true);
    }
}


$core = new Core();

require 'view.php';