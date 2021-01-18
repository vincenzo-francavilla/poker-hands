<?php

namespace App\Entity;

use App\Repository\MatchDataRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MatchDataRepository::class)
 */
class MatchData
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="array")
     */
    private $playerOneCards = [];

    /**
     * @ORM\Column(type="array")
     */
    private $playerTwoCards = [];

    /**
     * @ORM\Column(type="integer")
     */
    private $winnerPlayerId;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return array
     */
    public function getPlayerOneCards(): array
    {
        return $this->playerOneCards;
    }

    /**
     * @param array $playerOneCards
     */
    public function setPlayerOneCards(array $playerOneCards): void
    {
        $this->playerOneCards = $playerOneCards;
    }

    /**
     * @return array
     */
    public function getPlayerTwoCards(): array
    {
        return $this->playerTwoCards;
    }

    /**
     * @param array $playerTwoCards
     */
    public function setPlayerTwoCards(array $playerTwoCards): void
    {
        $this->playerTwoCards = $playerTwoCards;
    }

    /**
     * @return mixed
     */
    public function getWinnerPlayerId()
    {
        return $this->winnerPlayerId;
    }

    /**
     * @param mixed $winnerPlayerId
     */
    public function setWinnerPlayerId($winnerPlayerId): void
    {
        $this->winnerPlayerId = $winnerPlayerId;
    }
}
