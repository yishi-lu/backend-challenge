<?php
    namespace App\Utility;

    require_once  "../vendor/simple_html_dom.php";

    /**
     * Created by Yishi Lu.
     * User: Yishi Lu
     * Date: 2020/01/25
     */
    class Parser{

        private $ch;

        public function __construct(){
            $this->ch = curl_init();
        }

        /**
         * parse etfs information from url
         *
         * @param url
         * @return array
         */
        public function parseEtfs($url){
            try {
            
                // Check if initialization had gone wrong*    
                if ($this->ch === false) {
                    throw new Exception('failed to initialize');
                }
            
                curl_setopt($this->ch, CURLOPT_URL, $url);
                curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
            
                $content = curl_exec($this->ch);
            
                // Check the return value of curl_exec()
                if ($content === false) {
                    throw new Exception(curl_error($this->ch), curl_errno($this->ch));
                }
            
                // Close curl handle
                curl_close($this->ch);
                
                $result = json_decode($content);

                // $etfs_item = $result->data->us->funds->etfs->overview->datas;

                return $result;

            } catch(Exception $e) {
                trigger_error(sprintf(
                    'Curl failed with error #%d: %s',
                    $e->getCode(), $e->getMessage()),
                    E_USER_ERROR);
            }
        }

        /**
         * parse etfs symbol detail information from url
         *
         * @param url
         * @return array
         */
        public function parseEtfDetail($url){

            $html = file_get_html($url);

            $fund_top_holdings_array = $this->findTopHolding($html, "fund-top-holdings");
            $index_top_holdings_array = $this->findTopHolding($html, "index-top-holdings");

            $fund_sector_weight_array = $this->findSectorWeight($html, "fund-sector-breakdown");
            $index_sector_weight_array = $this->findSectorWeight($html, "index-sector-breakdown");

            $country_weight_array = $this->findCountryWeight($html);

            $eft_info_array = $this->findInfo($html);

            
            $result = array();
            $result['fund_top_holdings'] = $fund_top_holdings_array;
            $result['index_top_holdings'] = $index_top_holdings_array;
            $result['fund_sector_weight'] = $fund_sector_weight_array;
            $result['index_sector_weight'] = $index_sector_weight_array;
            $result['country_weight'] = $country_weight_array;
            $result['eft_info'] = $eft_info_array;

            return $result;

        }
    
        /**
         * parse top holding information by given dom object and class name
         *
         * @param html, name
         * @return array
         */
        public function findTopHolding($html, $name){

            $top_holding_array = array();
            
            $top_holding = $html->find('div.'.$name, 0);

            if($top_holding == null) return null;

            $top_holding_items = $top_holding->find('tr');

            $head = $top_holding->find('th');

            foreach($top_holding_items as $item){

                $attributes = $item->find('td');

                if($attributes != null){
                    $entry = array();

                    for($i=0; $i<sizeof($head); $i++){

                        switch($head[$i]->innertext){

                            case 'Name':
                                $entry['name'] = $attributes[$i]->innertext;
                                break;
                            case 'Ticker':
                                $entry['ticker'] = $attributes[$i]->innertext;
                                break;
                            case 'Shares Held':
                                $entry['shares'] = $attributes[$i]->innertext;
                                break;
                            case 'Market Value':
                                $entry['market_value'] = $attributes[$i]->innertext;
                                break;
                            case 'Par Value':
                                $entry['par_value'] = $attributes[$i]->innertext;
                                break;
                            case 'Total Mkt Cap M':
                                $entry['total_mkt_cap_m'] = $attributes[$i]->innertext;
                                break;
                            case 'Weight':
                                $entry['weight'] = $attributes[$i]->innertext;
                                break;
                            case 'ISIN':
                                $entry['ISIN'] = $attributes[$i]->innertext;
                                break;
                            default:
                                break;  
                        }
                    }

                    array_push($top_holding_array, $entry);
                }

            }
            return $top_holding_array;
        }

        /**
         * parse sector weight information by given dom object and class name
         *
         * @param html, name
         * @return array
         */
        public function findSectorWeight($html, $name){

            $sector_weight_array = array();

            $sector_weight = $html->find('div.'.$name, 0);

            if($sector_weight == null) return null;

            $sector_weight_items = $sector_weight->find('td');
            
            for($i=0; $i<sizeof($sector_weight_items); $i=$i+2){

                $entry = array();
                $entry['label'] = $sector_weight_items[$i]->innertext;
                $entry['data'] = $sector_weight_items[$i+1]->innertext;

                array_push($sector_weight_array, $entry);
            }

            return $sector_weight_array;
        }

        /**
         * parse country weight information by given dom object
         *
         * @param html
         * @return array
         */
        public function findCountryWeight($html){

            $country_weight = $html->find("input[id=fund-geographical-breakdown]", 0);

            if($country_weight == null) return null;

            $values = $country_weight->value;

            $values = json_decode(str_replace("&#34;", '"', $values));

            return $values->attrArray;
        }

        /**
         * parse description and information of a etf symbol by given dom object
         *
         * @param html
         * @return array
         */
        public function findInfo($html){

            $eft_info_array = array();

            $key_feature = $html->find('div.fundcontent', 2);
            $eft_info_array['key_feature'] = $key_feature->find('div.content', 0)->innertext;

            $about = $html->find('div.fundcontent', 4);
            $eft_info_array['about'] = $about->find('div.content', 0) ? $about->find('div.content', 0)->innertext : null;

            //find Fund Information
            $info = $html->find('div.keyvalue ', 0);
            $info_detial1 = $info->find('tbody', 1)->find('td');
            $info_detial2 = $info->find('tbody', 3)->find('td');
            $info_array = array();
            for($i=0; $i<sizeof($info_detial1); $i=$i+2){

                if(strpos($info_detial1[$i]->innertext, 'Gross Expense Ratio') !== false) {
                    $info_detial1[$i]->innertext = 'Gross Expense Ratio';
                    $info_array['Gross Expense Ratio'] = $info_detial1[$i+1]->innertext;
                }
                else $info_array[$info_detial1[$i]->innertext] = $info_detial1[$i+1]->innertext;
            }
            for($i=0; $i<sizeof($info_detial2); $i=$i+2){

                $info_array[$info_detial2[$i]->innertext] = $info_detial2[$i+1]->innertext;
            }
            $eft_info_array['fund_info'] = $info_array;

            //find Listing Information
            $info = $html->find('div.keyvalue ', 1);
            $info_detial = $info->find('tbody', 1)->find('td');
            $info_array = array();
            for($i=0; $i<sizeof($info_detial); $i=$i+2){
                $info_array[$info_detial[$i]->innertext] = $info_detial[$i+1]->innertext;
            }

            $eft_info_array['listing_info'] = $info_array;

            return $eft_info_array;
        }
    }
    
?>