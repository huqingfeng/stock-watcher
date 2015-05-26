<?php

namespace Acme\DemoBundle\Resources\lib;

class StockWatcher
{
    public function getStartDate()
    {
        $startDate = '2014-01-01';
        return $startDate;
    }

    public function getEndDate()
    {
        $endDate = Date('Y-m-d');
        return $endDate;
    }

    public function getStockSymbols()
    {
        return array(
                'AAPL',
                'WUBA',
                'QIHU',
                'YHOO',
                'MU',
                'MA',
                'FOXA',
                'SFUN',
                'GOOG',
                'AMZN',
                'FB',
                'BABA',
                'SPY',
                'QQQ',
                'MOMO'
            );
    }

    public function getYahooFinanceData($stockSymbol, $startDate = null, $endDate = null)
    {
        $stockMarketAPI = new StockMarketAPI();
        $stockMarketAPI->symbol = $stockSymbol;

        if($startDate != null && $endDate != null) {
            $stockMarketAPI->history = array(
                'start' => $startDate,
                'end'   => $endDate,
                'interval' => 'd'
            );
        }

        $stockData = $stockMarketAPI->getData();

        return $stockData;
    }

    public function sendNotificationEmail($stockSymbol, $currentPrice, $lastDayPrice, $decreaseRatio, $timeInterval)
    {
        sendNotificationEmail($stockSymbol, $currentPrice, $lastDayPrice, abs($decreaseRatio), $timeInterval);
    }

    public function getDecreaseRatio($currentPrice, $lastPrice)
    {
        $decreaseRatio = round(($currentPrice - $lastPrice) / $lastPrice * 100, 2);
        return $decreaseRatio;
    }

    public function getTimeInterval()
    {
        return array(
            'day' => date('N', strtotime('- 1 days')) == 7 ? date('Y-m-d', strtotime('- 3 days')) : (date('N', strtotime('- 1 days')) == 6 ? date('Y-m-d', strtotime('- 2 days')) : date('Y-m-d', strtotime('- 1 days'))),
            'week' => date('N', strtotime('- 7 days')) == 7 ? date('Y-m-d', strtotime('- 9 days')) : (date('N', strtotime('- 7 days')) == 6 ? date('Y-m-d', strtotime('- 8 days')) : date('Y-m-d', strtotime('- 7 days'))),
            'month' => date('N', strtotime('- 30 days')) == 7 ? date('Y-m-d', strtotime('- 32 days')) : (date('N', strtotime('- 30 days')) == 6 ? date('Y-m-d', strtotime('- 31 days')) : date('Y-m-d', strtotime('- 30 days'))),
            'threeMonth' => date('N', strtotime('- 90 days')) == 7 ? date('Y-m-d', strtotime('- 92 days')) : (date('N', strtotime('- 90 days')) == 6 ? date('Y-m-d', strtotime('- 91 days')) : date('Y-m-d', strtotime('- 90 days'))),
            'sixMonth' => date('N', strtotime('- 180 days')) == 7 ? date('Y-m-d', strtotime('- 182 days')) : (date('N', strtotime('- 180 days')) == 6 ? date('Y-m-d', strtotime('- 181 days')) : date('Y-m-d', strtotime('- 180 days'))),
            'year' => date('N', strtotime('- 360 days')) == 7 ? date('Y-m-d', strtotime('- 362 days')) : (date('N', strtotime('- 360 days')) == 6 ? date('Y-m-d', strtotime('- 361 days')) : date('Y-m-d', strtotime('- 360 days'))),
        );
    }

    public function getFinalizedRecords()
    {
        $stockSymbols = $this->getStockSymbols();

        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();


        $finalizedRecords = array();
        foreach($stockSymbols as $stockSymbol) {
            $stockCurrentRecord = $this->getYahooFinanceData($stockSymbol);
            $riskShed = $this->getRiskshed();
            $currentPrice = $stockCurrentRecord['price'];

            
            $stockHistoricalRecords = $this->getYahooFinanceData($stockSymbol, $startDate, $endDate);

            $finalizedRecords[$stockSymbol] = array();

            $finalizedRecords[$stockSymbol]['symbol'] = $stockSymbol;
            $finalizedRecords[$stockSymbol]['current_price'] = $currentPrice;

            foreach ($this->getTimeInterval() as $timeInterval => $date) {
                
                if(isset($stockHistoricalRecords[$stockSymbol])) {
                    $lastPrice = 0;
                    
                    if(isset($stockHistoricalRecords[$stockSymbol][$date])) {
                        $lastPrice = $stockHistoricalRecords[$stockSymbol][$date]['close'];
                    } else {
                        $dateBeforeOneDay = date('Y-m-d', strtotime('- 1 days', strtotime($date)));
                        $dateBeforeTwoDay = date('Y-m-d', strtotime('- 2 days', strtotime($date)));
                        $dateBeforeThreeDay = date('Y-m-d', strtotime('- 3 days', strtotime($date)));
                        $dateBeforeFourDay = date('Y-m-d', strtotime('- 4 days', strtotime($date)));
                        $dateBeforeFiveDay = date('Y-m-d', strtotime('- 5 days', strtotime($date)));
                        
                        if(isset($stockHistoricalRecords[$stockSymbol][$dateBeforeOneDay])) {
                            $lastPrice = $stockHistoricalRecords[$stockSymbol][$dateBeforeOneDay]['close'];
                        } elseif (isset($stockHistoricalRecords[$stockSymbol][$dateBeforeTwoDay])) {
                            $lastPrice = $stockHistoricalRecords[$stockSymbol][$dateBeforeTwoDay]['close'];
                        } elseif (isset($stockHistoricalRecords[$stockSymbol][$dateBeforeThreeDay])) {
                            $lastPrice = $stockHistoricalRecords[$stockSymbol][$dateBeforeThreeDay]['close'];
                        } elseif (isset($stockHistoricalRecords[$stockSymbol][$dateBeforeFourDay])) {
                            $lastPrice = $stockHistoricalRecords[$stockSymbol][$dateBeforeFourDay]['close'];
                        } elseif (isset($stockHistoricalRecords[$stockSymbol][$dateBeforeFiveDay])) {
                            $lastPrice = $stockHistoricalRecords[$stockSymbol][$dateBeforeFiveDay]['close'];
                        }
                    }
                    
                    if(!empty($lastPrice)) {
                        $decreaseRatio = $this->getDecreaseRatio($currentPrice, $lastPrice);

                        $finalizedRecords[$stockSymbol][$timeInterval] = array(
                            'last_price' => round($lastPrice, 2),
                            'decrease_ratio' => $decreaseRatio,
                            'risk' => isset($riskShed[$timeInterval]) && $decreaseRatio <= $riskShed[$timeInterval] ? true : false
                        );
                    }
                }
            }
            
            
        }

        return $finalizedRecords;
    }

    protected function getRiskshed()
    {
        return array(
            'day' => -3,
            'week' => -5,
            'month' => -10,
            'threeMonth' => -20,
            'sixMonth' => -30,
            'year' => -40,
        );
    }
	
}
