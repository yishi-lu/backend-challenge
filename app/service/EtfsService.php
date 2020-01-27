<?php
    namespace App\Service;
    use App\Utility\Database;
    use App\Utility\Parser;

    require_once  "../utility/db.php";
    require_once  "../utility/parser.php";

    /**
     * Created by Yishi Lu.
     * User: Yishi Lu
     * Date: 2020/01/25
     */
    class EtfsService{
    
        private $conn;
        private $parser;

        public function __construct(){

            $database = new Database();
            $this->conn = $database->getConnection();

            $this->parser = new Parser();
        }

        /**
         * fetch all etf symbols 
         *
         * @param null
         * @return array
         */
        function getAllEtfs(){

            try{

                $output = array();

                $stmt = $this->conn->prepare("SELECT * FROM etf WHERE 1=1");
                $stmt->execute();

                $result = $stmt->get_result();


                while($row = $result->fetch_assoc()) {

                    $row['fundUri'] = "http://".WEB_HOST.":9090/fetchEtfDetail.php?ticker=".$row['fundTicker'];

                    array_push($output, $row);
                }

                $stmt->close();

                return $output;

            } catch (Exception $e) {
                echo 'Caught exception: ',  $e->getMessage(), "\n";
            }
        }

        /**
         * fetch etf symbol with given ticker
         *
         * @param ticker
         * @return array
         */
        function getEtfByTicker($ticker){

            try{

                $today = date("Y-m-d");  

                $output = array();

                $stmt = $this->conn->prepare("SELECT * FROM etf WHERE fundTicker=?");
                $stmt->bind_param("s", $ticker);
                $stmt->execute();

                $result = $stmt->get_result();

                $fund_id = null;

                if($result->num_rows == 0) return array('error'=>'Invalid Ticker');

                if($row = $result->fetch_assoc()) {
                    $fund_id = $row['id'];
                }
                $stmt->close();

                //fund top holding
                $stmt = $this->conn->prepare("SELECT * FROM top_holding WHERE etf_id=? AND type='fund' AND created_at=? ORDER BY weight DESC");
                $stmt->bind_param("ss", $fund_id, $today);
                $stmt->execute();

                $result = $stmt->get_result();

                $fund_top_holding = array();

                while($row = $result->fetch_assoc()){
                    array_push($fund_top_holding, $row);
                }

                $output['fund_top_holding'] = $fund_top_holding;

                //index top holding
                $stmt = $this->conn->prepare("SELECT * FROM top_holding WHERE etf_id=? AND type='index' AND created_at=? ORDER BY weight DESC");
                $stmt->bind_param("ss", $fund_id, $today);
                $stmt->execute();

                $result = $stmt->get_result();

                $index_top_holding = array();

                while($row = $result->fetch_assoc()){
                    array_push($index_top_holding, $row);
                }

                $output['index_top_holding'] = $index_top_holding;


                //fund sector weight
                $stmt = $this->conn->prepare("SELECT * FROM sector_weight WHERE etf_id=? AND type='fund' AND created_at=? ORDER BY data DESC");
                $stmt->bind_param("ss", $fund_id, $today);
                $stmt->execute();

                $result = $stmt->get_result();

                $fund_sector_weight = array();

                while($row = $result->fetch_assoc()){
                    array_push($fund_sector_weight, $row);
                }

                $output['fund_sector_weight'] = $fund_sector_weight;



                //index sector weight
                $stmt = $this->conn->prepare("SELECT * FROM sector_weight WHERE etf_id=? AND type='index' AND created_at=? ORDER BY data DESC");
                $stmt->bind_param("ss", $fund_id, $today);
                $stmt->execute();

                $result = $stmt->get_result();

                $index_sector_weight = array();

                while($row = $result->fetch_assoc()){
                    array_push($index_sector_weight, $row);
                }
                
                $output['index_sector_weight'] = $index_sector_weight;


                //country weight
                $stmt = $this->conn->prepare("SELECT * FROM country_weight WHERE etf_id=? AND created_at=? ORDER BY weight DESC");
                $stmt->bind_param("ss", $fund_id, $today);
                $stmt->execute();

                $result = $stmt->get_result();

                $country_weight = array();

                while($row = $result->fetch_assoc()){
                    array_push($country_weight, $row);
                }
                
                $output['country_weight'] = $country_weight;

                //etf information
                $stmt = $this->conn->prepare("SELECT * FROM etf_info WHERE etf_id=? AND created_at=? ");
                $stmt->bind_param("ss", $fund_id, $today);
                $stmt->execute();

                $result = $stmt->get_result();

                $etf_info = array();

                while($row = $result->fetch_assoc()){
                    array_push($etf_info, $row);
                }
                
                $output['etf_info'] = $etf_info;



                return $output;

            } catch (Exception $e) {
                echo 'Caught exception: ',  $e->getMessage(), "\n";
            }
        }

        /**
         * update etf symbols from given url
         *
         * @param url
         * @return null;
         */
        function updateEtfs($url){

            $result = $this->parser->parseEtfs($url);

            $etf_items = $result->data->us->funds->etfs->overview->datas;

            foreach($etf_items as $item){

                try {
                    $current_id = null;

                    $domicile = $item->domicile;
                    $fundName = $item->fundName;
                    $fundTicker = $item->fundTicker;
                    $fundUri = $item->fundUri;
                    $ter = $item->ter;
                    $nav = $item->nav;
                    $aum = $item->aum;
                    $asOfDate = $item->asOfDate[1];
                    $fundFilter = $item->fundFilter;

                    $stmt = $this->conn->prepare("Select * from Etf Where fundTicker=?");
                    $stmt->bind_param("s", $fundTicker);

                    $stmt->execute();
                    $result = $stmt->get_result();

                    if($row = $result->fetch_assoc()) $current_id = $row['id'];

                    $stmt->close();

                    if($result->num_rows == 0) {
                        $stmt = $this->conn->prepare("INSERT INTO Etf (domicile, fundName, fundTicker, fundUri, ter, nav, aum, asOfDate, fundFilter) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        
                        $stmt->bind_param("sssssssss", $domicile, $fundName, $fundTicker, $fundUri, $ter, $nav, $aum, $asOfDate, $fundFilter);
                        
                        $stmt->execute();
                        $current_id = $stmt->insert_id;
                        $stmt->close();
                    }
                    else {
                        $stmt = $this->conn->prepare("UPDATE Etf SET domicile=?, fundUri=?, ter=?, nav=?, aum=?, asOfDate=?, fundFilter=? where fundTicker=?");
                        $stmt->bind_param("ssssssss", $domicile, $fundUri, $ter, $nav, $aum, $asOfDate, $fundFilter, $fundTicker);

                        // echo $fundTicker.'*';
                        $stmt->execute();
                        $stmt->close();
                    }

                    $this->updateEtfsAttributes($current_id, $fundUri);

                } catch (\Exception $e) {
                    echo $e->getMessage();
                    var_dump($e->getMessage());
                }

            }

        }

        /**
         * update detail information of etfs by given etf id and url
         *
         * @param id, url
         * @return null
         */
        function updateEtfsAttributes($id, $url){

            try {
                $target_url = ETF_DOMAIN.$url;
                $result = $this->parser->parseEtfDetail($target_url);

                $today = date("Y-m-d");  


                if(is_array($result['fund_top_holdings'])){
                    foreach($result['fund_top_holdings'] as $item){

                        $type = 'fund';

                        $stmt = $this->conn->prepare("INSERT INTO top_holding (name, shares, ticker, market_value, par_value, total_mkt_cap_m, ISIN, weight, type, etf_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("sssssssssss", $item['name'], $item['shares'], $item['ticker'], $item['market_value'], $item['par_value'], $item['total_mkt_cap_m'], $item['ISIN'], $item['weight'], $type, $id, $today);

                        $stmt->execute();
                        $item_id = $stmt->insert_id;
                        $stmt->close();

                    }
                }
                
                if(is_array($result['index_top_holdings'])){
                    foreach($result['index_top_holdings'] as $item){

                        $type = 'index';

                        $stmt = $this->conn->prepare("INSERT INTO top_holding (name, shares, ticker, market_value, par_value, total_mkt_cap_m, ISIN, weight, type, etf_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("sssssssssss", $item['name'], $item['shares'], $item['ticker'], $item['market_value'], $item['par_value'], $item['total_mkt_cap_m'], $item['ISIN'], $item['weight'], $type, $id, $today);


                        $stmt->execute();
                        $item_id = $stmt->insert_id;
                        $stmt->close();

                    }
                }

                if(is_array($result['fund_sector_weight'])){
                    foreach($result['fund_sector_weight'] as $item){

                        $type = 'fund';

                        $stmt = $this->conn->prepare("INSERT INTO sector_weight (label, data, type, etf_id, created_at) VALUES (?, ?, ?, ?, ?)");
                        $stmt->bind_param("sssss", $item['label'], $item['data'], $type, $id, $today);

                        $stmt->execute();
                        $item_id = $stmt->insert_id;
                        $stmt->close();

                    }
                }

                if(is_array($result['index_sector_weight'])){
                    foreach($result['index_sector_weight'] as $item){

                        $type = 'index';

                        $stmt = $this->conn->prepare("INSERT INTO sector_weight (label, data, type, etf_id, created_at) VALUES (?, ?, ?, ?, ?)");
                        $stmt->bind_param("sssss", $item['label'], $item['data'], $type, $id, $today);

                        $stmt->execute();
                        $item_id = $stmt->insert_id;
                        $stmt->close();

                    }
                }

                if(is_array($result['country_weight'])){
                    foreach($result['country_weight'] as $item){

                        $stmt = $this->conn->prepare("INSERT INTO country_weight (name, weight, etf_id, created_at) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("ssss", $item->name->value, $item->weight->value, $id, $today);

                        $stmt->execute();
                        $item_id = $stmt->insert_id;
                        $stmt->close();

                    }
                }

                $stmt = $this->conn->prepare("INSERT INTO etf_info (key_feature, about, primary_benchmark, secondary_benchmark, inception, options, gross_expense_ratio, base_currency, investment_manager, 
                management_team, sub_advisor, distributor, distribution_frequency, trustee, marketing_agent, gold_custodian, sponsor, exchange, listing_date, trading_currency, CUSIP, ISIN, etf_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $stmt->bind_param("ssssssssssssssssssssssss", $key_feature, $about, $primary_benchmark, $secondary_benchmark, $inception, $options, $gross_expense_ratio, $base_currency, 
                $investment_manager, $management_team, $sub_advisor, $distributor, $distribution_frequency, $trustee, $marketing_agent, $gold_custodian, $sponsor, $exchange, $listing_date, $trading_currency, $CUSIP, $ISIN, $id, $today);

                $key_feature = $result['eft_info']['key_feature'] ?? null;
                $about = $result['eft_info']['about'] ?? null;
                
                $primary_benchmark = $result['eft_info']['fund_info']['Benchmark'] ?? null; 
                $primary_benchmark = $result['eft_info']['fund_info']['Primary Benchmark'] ?? $primary_benchmark;
                $secondary_benchmark = $result['eft_info']['fund_info']['Secondary Benchmark'] ?? null;
                $inception = $result['eft_info']['fund_info']['Inception Date'] ?? null;
                $options = $result['eft_info']['fund_info']['Options Available'] ?? null;
                $gross_expense_ratio = $result['eft_info']['fund_info']['Gross Expense Ratio'] ?? null;
                $base_currency = $result['eft_info']['fund_info']['Base Currency'] ?? null;
                $investment_manager = $result['eft_info']['fund_info']['Investment Manager'] ?? null;
                $management_team = $result['eft_info']['fund_info']['Management Team'] ?? null;
                $sub_advisor = $result['eft_info']['fund_info']['Sub-advisor'] ?? null;
                $distributor = $result['eft_info']['fund_info']['Distributor'] ?? null;
                $distribution_frequency = $result['eft_info']['fund_info']['Distribution Frequency'] ?? null;
                $trustee = $result['eft_info']['fund_info']['Trustee'] ?? null; 
                $marketing_agent = $result['eft_info']['fund_info']['Marketing Agent'] ?? null;
                $gold_custodian = $result['eft_info']['fund_info']['Gold Custodian'] ?? null;
                $sponsor = $result['eft_info']['fund_info']['Sponsor'] ?? null;

                $exchange = $result['eft_info']['listing_info']['Exchange'] ?? null;
                $listing_date = $result['eft_info']['listing_info']['Listing Date'] ?? null;
                $trading_currency = $result['eft_info']['listing_info']['Trading Currency'] ?? null;
                $CUSIP = $result['eft_info']['listing_info']['CUSIP'] ?? null;
                $ISIN = $result['eft_info']['listing_info']['ISIN'] ?? null;


                $stmt->execute();
                $item_id = $stmt->insert_id;
                $stmt->close();

            } catch (Exception $e) {
                echo 'Caught exception: ',  $e->getMessage(), "\n";
            }

        }

    }
?>