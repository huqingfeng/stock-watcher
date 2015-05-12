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
            'day' => Date('Y-m-d', strtotime('- 1 days')),
            'week' => Date('Y-m-d', strtotime('- 7 days')),
            'month' => Date('Y-m-d', strtotime('- 30 Days')),
            'threeMonth' => Date('Y-m-d', strtotime('- 90 Days')),
            'sixMonth' => Date('Y-m-d', strtotime('- 180 Days')),
            'year' => Date('Y-m-d', strtotime('- 360 Days')),
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


            foreach($stockHistoricalRecords as $stockHistoricalRecord) {
                foreach($stockHistoricalRecord as $stockHistoricalRecordPerDay) {

                    foreach($this->getTimeInterval() as $timeInterval => $date) {

                        if(isset($stockHistoricalRecordPerDay['date']) && $stockHistoricalRecordPerDay['date'] == $date) {
                            $lastPrice = $stockHistoricalRecordPerDay['close'];

                            $decreaseRatio = $this->getDecreaseRatio($currentPrice, $lastPrice);

                            $finalizedRecords[$stockSymbol][$timeInterval] = array(
                                'last_price'        => round($lastPrice, 2),
                                'decrease_ratio'    => $decreaseRatio,
                                'risk'              => isset($riskShed[$timeInterval]) && $decreaseRatio <= $riskShed[$timeInterval] ? true : false
                            );
                        }
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
