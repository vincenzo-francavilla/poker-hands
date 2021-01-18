<?php
/**
 * Committed Company: Gatewaynet Limited
 * Develop by: Gatewaynet LTD
 * Project Manager: Vincenzo Francavilla
 * Contact: admin@gatewaynet.ltd
 * Date: 7/20/20
 * Time: 8:48 AM
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


use App\Entity\MatchData;
use App\Entity\Players;
use App\Entity\Tournament;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use PhpParser\ErrorHandler\Throwing;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DataService
{
    const ALLOWED_CARD = [
        '2C','2D','2H','2S',
        '3C','3D','3H','3S',
        '4C','4D','4H','4S',
        '5C','5D','5H','5S',
        '6C','6D','6H','6S',
        '7C','7D','7H','7S',
        '8C','8D','8H','8S',
        '9C','9D','9H','9S',
        'TC','TD','TH','TS',
        'JC','JD','JH','JS',
        'QC','QD','QH','QS',
        'KC','KD','KH','KS',
        'AC','AD','AH','AS',
        ];

    /**
     * @var EntityManager $em
     */
    protected $em;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * @var PokerRulesService $pokerRules
     */
    private $pokerRules;

    public function __construct(ContainerInterface $container,LoggerInterface $logger,PokerRulesService $pokerRulesService)
    {
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getManager();
        $this->pokerRules = $pokerRulesService;
    }

    /**
     * @param $file
     * @throws Exception
     */
    public function verifyData($file)
    {
        $linesArray = [];
        try{
            $openFile = fopen($file, "r") or die('ERROR CANT OPEN FILE');
            while(!feof($openFile))
            {
                // split data into array by file's line
                $linesArray[] = fgets($openFile);
            }
            fclose($openFile);
        }catch (\Exception $exception){
            echo $exception->getMessage();
        }
        $collectResults = [];
        $totalWinPlayerOne = 0;
        $totalWinPlayerTwo = 0;
        $findPlayers = $this->em->getRepository('App:Players')->findBy([]);
        foreach ($linesArray as $key=>$line){
            $cardMatch = explode(' ',trim($line));
            if(count($cardMatch) == 10){
                foreach ($cardMatch as $cardCheck){
                    if(!in_array(trim($cardCheck),self::ALLOWED_CARD)){
                        return 'File contains not valid card format. check card: '.$cardCheck;
                    }
                    $cardMatch[] = $cardCheck;
                }
                $pushCardData = $this->pushCardData($cardMatch,$findPlayers);
                if($pushCardData->handsWinner['winner'] == 'playerOne'){
                    $totalWinPlayerOne++;
                }elseif($pushCardData->handsWinner['winner'] == 'playerTwo'){
                    $totalWinPlayerTwo++;
                }
                $collectResults[] = $pushCardData;
            }
        }
        return ['stats' => ['playerOne' => $totalWinPlayerOne,'playerTwo' => $totalWinPlayerTwo],'data' => $collectResults];
    }

    /**
     * @param $matchCards
     * @param $findPlayers
     * @return \stdClass
     */
    private function pushCardData($matchCards,$findPlayers)
    {
        $playerOne = $this->mergeArrayByKeys($matchCards,[0,1,2,3,4]);
        $playerTwo = $this->mergeArrayByKeys($matchCards,[5,6,7,8,9]);
        $results = new \stdClass();
        $collectResult = new \stdClass();
        $collectResult->flush = $this->checkFlush($playerOne,$playerTwo);
        $collectResult->fourOfKind = $this->checkFourOfKind($playerOne,$playerTwo);
        $collectResult->royalFlush = $this->checkRoyalFlush($playerOne,$playerTwo);
        $collectResult->straight = $this->checkStraight($playerOne,$playerTwo);
        $collectResult->straightFlush = $this->checkStraightFlush($playerOne,$playerTwo);
        $collectResult->threeOfKind = $this->checkThreeOfKind($playerOne,$playerTwo);
        $collectResult->twoPair = $this->checkTwoPair($playerOne,$playerTwo);
        $collectResult->onePair = $this->checkOnePair($playerOne,$playerTwo);
        $collectResult->higherCard = $this->checkHigherCards($playerOne,$playerTwo);
        $results->handsWinner = false;
        foreach ($collectResult as $result){
            if($result['winner']){
                $results->handsWinner = $result;
                $results->cards = ['playerOne' => $playerOne,'playerTwo' => $playerTwo];
                if($result['winner'] == 'playerOne'){
                    $winner = $findPlayers[0]->getId();
                }elseif ($result['winner'] == 'playerTwo'){
                    $winner = $findPlayers[1]->getId();
                }else{
                    $winner = 0;
                }
                $matchData = $this->createMatchData($playerOne,$playerTwo,$winner);
                $this->createTournament($findPlayers[0],$findPlayers[1],$matchData);
                return $results;
            }
        }
    }

    private function checkHigherCards($playerOne,$playerTwo)
    {
        $winner = false;
        $playerOneMatch = $this->pokerRules->higherCards($playerOne);
        $playerTwoMatch = $this->pokerRules->higherCards($playerTwo);
        if(max($playerOneMatch) > max($playerTwoMatch)){
            $winner = 'playerOne';
        }elseif (max($playerOneMatch) < max($playerTwoMatch)){
            $winner = 'playerTwo';
        }elseif (array_sum($playerOneMatch) > array_sum($playerTwoMatch)){
            $winner = 'playerOne';
        }elseif (array_sum($playerOneMatch) < array_sum($playerTwoMatch)){
            $winner = 'playerTwo';
        }else{
            $winner = 'split';
        }
        return ['message' => 'Winner Higher Card','cards' => ['playerOne' => $playerOne,'playerTwo' => $playerTwo],'winner' => $winner];

    }

    private function checkOnePair($playerOne,$playerTwo)
    {
        $winner = false;
        $playerOneMatch = $this->pokerRules->onePair($playerOne);
        $playerTwoMatch = $this->pokerRules->onePair($playerTwo);
        if($playerOneMatch && $playerTwoMatch){
            $playerOneCouples = array_keys(array_intersect($playerOneMatch['refCard'], [2]));
            $playerTwoCouples = array_keys(array_intersect($playerTwoMatch['refCard'], [2]));
            $playerOneSingleCard = array_keys(array_intersect($playerOneMatch['refCard'], [1]));
            $playerTwoSingleCard = array_keys(array_intersect($playerTwoMatch['refCard'], [1]));
            if(array_sum($playerOneCouples) > array_sum($playerTwoCouples)){
                $winner = 'playerOne';
            }elseif (array_sum($playerOneCouples) < array_sum($playerTwoCouples)){
                $winner = 'playerTwo';
            }elseif (array_sum($playerOneCouples) == array_sum($playerTwoCouples)){
                if($playerOneSingleCard > $playerTwoSingleCard){
                    $winner = 'playerOne';
                }elseif ($playerOneSingleCard < $playerTwoSingleCard){
                    $winner = 'playerTwo';
                }else{
                    $winner = 'split';
                }
            }
        }elseif ($playerOneMatch){
            $winner = 'playerOne';
        }elseif ($playerTwoMatch){
            $winner = 'playerTwo';
        }
        return ['message' => 'Winner One Pair','cards' => ['playerOne' => $playerOne,'playerTwo' => $playerTwo],'winner' => $winner];
    }
    private function checkTwoPair($playerOne,$playerTwo)
    {
        $winner = false;
        $playerOneMatch = $this->pokerRules->twoOfPair($playerOne);
        $playerTwoMatch = $this->pokerRules->twoOfPair($playerTwo);

        if($playerOneMatch && $playerTwoMatch){
            $playerOneCouples = array_keys(array_intersect($playerOneMatch['refCard'], [2]));
            $playerTwoCouples = array_keys(array_intersect($playerTwoMatch['refCard'], [2]));
            $playerOneSingleCard = array_keys(array_intersect($playerOneMatch['refCard'], [1]));
            $playerTwoSingleCard = array_keys(array_intersect($playerTwoMatch['refCard'], [1]));
            if(array_sum($playerOneCouples) > array_sum($playerTwoCouples)){
                $winner = 'playerOne';
            }elseif (array_sum($playerOneCouples) < array_sum($playerTwoCouples)){
                $winner = 'playerTwo';
            }elseif (array_sum($playerOneCouples) == array_sum($playerTwoCouples)){
                if($playerOneSingleCard > $playerTwoSingleCard){
                    $winner = 'playerOne';
                }elseif ($playerOneSingleCard < $playerTwoSingleCard){
                    $winner = 'playerTwo';
                }else{
                    $winner = 'split';
                }
            }
        }elseif ($playerOneMatch){
            $winner = 'playerOne';
        }elseif ($playerTwoMatch){
            $winner = 'playerTwo';
        }
        return ['message' => 'Winner Two Pair','cards' => ['playerOne' => $playerOne,'playerTwo' => $playerTwo],'winner' => $winner];
    }

    private function checkThreeOfKind($playerOne,$playerTwo)
    {
        $winner = false;
        $playerOneMatch = $this->pokerRules->threeOfKind($playerOne);
        $playerTwoMatch = $this->pokerRules->threeOfKind($playerTwo);
        if($playerOneMatch && $playerTwoMatch){
            if($playerOneMatch['refCard'] > $playerTwoMatch['refCard']){
                $winner = 'playerOne';
            }elseif ($playerOneMatch['refCard'] < $playerTwoMatch['refCard']){
                $winner = 'playerTwo';
            }else{
                $winner = 'split';
            }
        }elseif($playerOneMatch){
            $winner = 'playerOne';
        }else if($playerTwoMatch){
            $winner = 'playerTwo';
        }
        return ['message' => 'Winner Three Of A Kind','cards' => ['playerOne' => $playerOne,'playerTwo' => $playerTwo],'winner' => $winner];
    }
    private function checkStraight($playerOne,$playerTwo)
    {
        $winner = false;
        $playerOneMatch = $this->pokerRules->straightCards($playerOne);
        $playerTwoMatch = $this->pokerRules->straightCards($playerTwo);
        if($playerOneMatch && $playerTwoMatch){
            $winner = false;
            // in case of double true return check the higher value
        }
        if($playerOneMatch){
            $winner = 'playerOne';
        }elseif($playerTwoMatch && !$winner){
            $winner = 'playerTwo';
        }
        if(!$winner){
            return false;
        }
        return ['message' => 'Winner Straight','cards' => ['playerOne' => $playerOne,'playerTwo' => $playerTwo],'winner' => $winner];
    }

    private function checkFlush($playerOne,$playerTwo)
    {
        $winner = false;
        $playerOneMatch = $this->pokerRules->colorFlush($playerOne);
        $playerTwoMatch = $this->pokerRules->colorFlush($playerTwo);
        // case both has flush cards, than check higher cards
        if($playerOneMatch['cardSeed'] && $playerTwoMatch['cardSeed']){
            $playerOneSum = array_sum($playerOneMatch['cardValues']);
            $playerTwoSum = array_sum($playerOneMatch['cardValues']);
            if($playerOneSum > $playerTwoSum){
                $winner = 'playerOne';
            }elseif ($playerOneSum < $playerTwoSum){
                $winner = 'playerTwo';
            }else{
                $winner = 'split';
            }
            return ['message' => 'Split Flush','cards' => ['playerOne' => $playerOne,'playerTwo' => $playerTwo],'winner' => $winner];
        }elseif ($playerOneMatch['cardSeed']){
            $winner = 'playerOne';
        }elseif ($playerTwoMatch['cardSeed']){
            $winner = 'playerTwo';
        }
        if(!$winner){
            return false;
        }
        return ['message' => 'Winner Flush','cards' => ['playerOne' => $playerOne,'playerTwo' => $playerTwo],'winner' => $winner];
    }

    /**
     * @param $playerOne
     * @param $playerTwo
     * @return array|bool
     */
    private function checkStraightFlush($playerOne,$playerTwo)
    {

        $playerOneMatch = $this->pokerRules->straightFlush($playerOne);
        $playerTwoMatch = $this->pokerRules->straightFlush($playerTwo);
        $winner = false;
        if($playerOneMatch && $playerTwoMatch){
            return ['message' => 'Both Players Straight Flush ','cards' => ['playerOne' => $playerOne,'playerTwo' => $playerTwo],'winner' => $winner];
        }elseif($playerOneMatch){
            $winner = 'playerOne';
        }elseif($playerTwoMatch && !$winner){
            $winner = 'playerTwo';
        }
        if(!$winner){
            return false;
        }
        return ['message' => 'Winner Straight Flush ','cards' => ['playerOne' => $playerOne,'playerTwo' => $playerTwo],'winner' => $winner];
    }

    /**
     * @param $playerOne
     * @param $playerTwo
     * @return array|bool
     */
    private function checkRoyalFlush($playerOne,$playerTwo)
    {
        $playerOneMatch = $this->pokerRules->royalFlush($playerOne);
        $playerTwoMatch = $this->pokerRules->royalFlush($playerTwo);
        $winner = false;
        if($playerOneMatch && $playerTwoMatch){
            return ['message' => 'Both Players Royal Flush','cards' => ['playerOne' => $playerOne,'playerTwo' => $playerTwo],'winner' => $winner];
        }
        if($playerOneMatch){
            $winner = 'playerOne';
        }elseif($playerTwoMatch && !$winner){
            $winner = 'playerTwo';
        }
        if(!$winner){
            return false;
        }
        return ['message' => 'Winner Royal Flush','cards' => ['playerOne' => $playerOne,'playerTwo' => $playerTwo],'winner' => $winner];
    }

    private function checkFourOfKind($playerOne,$playerTwo)
    {
        $playerOneMatch = $this->pokerRules->fourOfKind($playerOne);
        $playerTwoMatch = $this->pokerRules->fourOfKind($playerTwo);
        $winner = false;
        if($playerOneMatch && $playerTwoMatch['base'] == $playerOneMatch['base']){
            if($playerOneMatch['kicker'] > $playerTwoMatch['kicker']){
                $winner = 'playerOne';
            }elseif ($playerOneMatch['kicker'] < $playerTwoMatch['kicker']){
                $winner = 'playerTwo';
            }else{
                $winner = 'split';
            }
        }elseif($playerOneMatch && $playerOneMatch['base'] > $playerTwoMatch['base']){
            $winner = 'playerOne';
        }elseif($playerTwoMatch && $playerTwoMatch['base'] > $playerOneMatch['base']){
            $winner = 'playerTwo';
        }
        if(!$winner){
            return false;
        }
        return ['message' => 'Winner FOUR OF A KIND','cards' => ['playerOne' => $playerOne,'playerTwo' => $playerTwo],'winner' => $winner];
    }

    private function createMatchData($playerOneCards,$playerTwoCards,$winnerId)
    {
        $newMatchData = new MatchData();
        $newMatchData->setPlayerOneCards($playerOneCards);
        $newMatchData->setPlayerTwoCards($playerTwoCards);
        $newMatchData->setWinnerPlayerId($winnerId);

        return $this->saveData($newMatchData);
    }

    /**
     * @param Players $playerOne
     * @param Players $playerTwo
     * @param MatchData $matchData
     */
    private function createTournament(Players $playerOne,Players $playerTwo,MatchData $matchData)
    {
        $newTournament = new Tournament();
        $newTournament->setPlayerOneId($playerOne);
        $newTournament->setPlayerTwoId($playerTwo);
        $newTournament->setMatchDataId($matchData);
        $this->saveData($newTournament);
    }

    /**
     * @param $entity
     * @return mixed
     */
    private function saveData($entity)
    {
        try{
            $this->em->persist($entity);
            $this->em->flush();
            return $entity;
        }catch (\Exception $exception){
            var_dump($exception->getMessage());
        }
    }

    /**
     * This will implode array by specific keys
     * @param array $sourceArray
     * @param array $arrayKeys
     * @param bool $string
     * @return array|string
     */
    private function mergeArrayByKeys($sourceArray,$arrayKeys, $string = false)
    {
        $resultData = [];
        foreach ($sourceArray as $key=>$value){
            if(in_array($key,$arrayKeys)){
                $resultData[] = $value;
            }
        }
        if($string){
            return implode(' ',$resultData);
        }
        return $resultData;
    }
}