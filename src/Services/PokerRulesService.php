<?php
/**
 * Committed Company: Gatewaynet Limited
 * Develop by: Gatewaynet LTD
 * Project Manager: Vincenzo Francavilla
 * Contact: admin@gatewaynet.ltd
 * Date: 7/20/20
 * Time: 10:27 AM
 * --------------------------- NOTICE -------------------------------
 * All information contained herein is, and remains
 * the property of Gatewaynet Limited and its suppliers,
 * if any.  The intellectual and technical concepts contained
 * herein are proprietary to Clanroot Industries Limited
 * and its suppliers and may be covered by U.S. and Foreign Patents,
 * patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Gatewaynet Limited.
 */

namespace App\Services;


class PokerRulesService
{
    /**
     * @param $cards
     * @return array
     */
    public function colorFlush($cards)
    {
        $seedCheck = false;
        $cardValues = [];
        $seedValue = false;
        foreach ($cards as $card)
        {
            $seed = $this->cardSeed($card);
            $cardValues[] = $this->cardValue($card);
            if(!$seedCheck){
                $seedCheck = $seed;
            }
            if($seedCheck != $seed){
                $seedValue = false;
                break;
            }else{
                $seedValue = true;
            }
        }
        asort($cardValues);
        return ['cardSeed' => $seedValue, 'cardValues' => $cardValues];
    }

    public function higherCards($cards)
    {
        $convertCardToValue = [];
        foreach ($cards as $card){
            $convertCardToValue[] = $this->cardValue($card);
        }
        return $convertCardToValue;
    }

    public function onePair($cards)
    {
        $convertCardToValue = [];
        foreach ($cards as $card){
            $convertCardToValue[] = $this->cardValue($card);
        }
        $arrayCount = array_count_values($convertCardToValue);
        if(count($arrayCount) == 4){
            return ['win' => true, 'refCard' => $arrayCount];
        }
        return false;
    }

    public function twoOfPair($cards)
    {
        $convertCardToValue = [];
        foreach ($cards as $card){
            $convertCardToValue[] = $this->cardValue($card);
        }
        $arrayCount = array_count_values($convertCardToValue);
        if(count($arrayCount) == 3){
            return ['win' => true, 'refCard' => $arrayCount];
        }
        return false;
    }
    public function threeOfKind($cards)
    {
        $convertCardToValue = [];
        foreach ($cards as $card){
            $convertCardToValue[] = $this->cardValue($card);
        }
        $arrayCount = array_count_values($convertCardToValue);
        foreach ($arrayCount as $key=>$value)
        {
            if($value == 3){
                return ['win' => true, 'refCard' => $key];
            }
        }
        return false;
    }
    public function straightCards($cards)
    {
        $combinations = [
            ['2','3','4','5','6'],
            ['3','4','5','6','7'],
            ['4','5','6','7','8'],
            ['5','6','7','8','9']
        ];
        $byCombination = [];
        $verified = [];
        foreach ($combinations as $key=>$combination)
        {
            $matchedCards = 0;
            foreach ($cards as $card)
            {
                $cardValue = $this->cardValue($card);
                if(!in_array($cardValue,$combination)){
                    break;
                }else{
                    if(!in_array($cardValue,$verified)){
                        $verified[] = $cardValue;
                        $matchedCards++;
                    }
                }
            }
            $byCombination[] = $matchedCards;
            if($matchedCards === 5){
                return true;
            }
        }

        return false;
    }
    /**
     * @param $cards
     * @return bool
     */
    public function royalFlush($cards)
    {
        $combinations = [
            ['TC','JC','QC','KC','AC'],
            ['TH','JH','QH','KH','AH'],
            ['TD','JD','QD','KD','AD'],
            ['TS','JS','QS','KS','AS']
        ];
        $matchedCards = 0;
        foreach ($combinations as $combination)
        {
            foreach ($cards as $card)
            {
                if(!in_array($card,$combination)){
                    break;
                }else{
                    $matchedCards++;
                }
            }
        }
        if($matchedCards == 5){
            return true;
        }
        return false;
    }

    public function straightFlush($cards)
    {
        $combinations = [
            ['2C','3C','4C','5C','6C','7C','8C','9C','TC','JC','QC','KC'],
            ['2H','3H','4H','5H','6H','7H','8H','9H','TH','JH','QH','KH'],
            ['2D','3D','4D','5D','6D','7D','8D','9D','TD','JD','QD','KD'],
            ['2S','3S','4S','5S','6S','7S','8S','9S','TS','JS','QS','KS']
        ];
        $matchedCards = 0;
        foreach ($combinations as $combination)
        {
            foreach ($cards as $card)
            {
                if(!in_array($card,$combination)){
                    break;
                }else{
                    $matchedCards++;
                }
            }
        }
        if($matchedCards == 5){
            return true;
        }
        return false;
    }

    public function fourOfKind($cards)
    {
        $combinations = [
            ['2C','2D','2H','2S'],
            ['3C','3D','3H','3S'],
            ['4C','4D','4H','4S'],
            ['5C','5D','5H','5S'],
            ['6C','6D','6H','6S'],
            ['7C','7D','7H','7S'],
            ['8C','8D','8H','8S'],
            ['9C','9D','9H','9S'],
            ['TC','TD','TH','TS'],
            ['JC','JD','JH','JS'],
            ['QC','QD','QH','QS'],
            ['KC','KD','KH','KS'],
            ['AC','AD','AH','AS'],
        ];
        $matchedCards = 0;
        $kicker = 0;
        foreach ($combinations as $combination)
        {
            foreach ($cards as $card)
            {
                $cardVal = $this->cardValue($card);
                if(!in_array($card,$combination)) {
                    if ($cardVal > $kicker){
                        $kicker = $cardVal;
                    }
                    break;
                }else{
                    $baseCard = $this->cardValue($card);
                    $matchedCards++;
                }
            }
        }
        if($matchedCards === 4){
            return ['base' => $baseCard,'kicker' => $kicker];
        }
        return false;
    }

    /**
     * @param $card
     * @return int
     */
    public function cardValue($card)
    {
        $value = substr($card,0,1);
        if($value == 'T'){
            $value = 10;
        }elseif($value == 'J'){
            $value = 11;
        }elseif ($value == 'Q'){
            $value = 12;
        }elseif ($value == 'K'){
            $value = 13;
        }elseif ($value == 'A'){
            $value = 14;
        }
        return intval($value);
    }

    public function cardSeed($card)
    {
        return substr($card,1,1);
    }
}